<?php

namespace App\Services;

use App\Models\AccountingPeriod;
use App\Models\CashAccount;
use App\Models\Category;
use App\Models\Fund;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class JournalPostingService
{
    /**
     * Record a simple treasurer transaction and generate balanced double-entry journal lines.
     */
    public function recordTransaction(array $data, int $userId): Transaction
    {
        $amount = round((float) ($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal transaksi harus lebih dari nol.');
        }

        $trxDate = $data['trx_date'] ?? now()->toDateString();
        $periodCode = substr($trxDate, 0, 7);

        $period = AccountingPeriod::firstOrCreate(
            ['period' => $periodCode],
            ['status' => 'open']
        );

        if ($period->status === 'closed') {
            throw new InvalidArgumentException("Periode {$periodCode} sudah ditutup dan tidak dapat menerima transaksi baru.");
        }

        $fund = Fund::findOrFail($data['fund_id']);
        $cashAccount = CashAccount::with('account')->findOrFail($data['cash_account_id']);
        $type = $data['type'];

        [$debitAccountId, $creditAccountId] = $this->resolveJournalAccounts($type, $fund, $cashAccount, $data);

        return DB::transaction(function () use ($data, $userId, $amount, $trxDate, $fund, $period, $type, $debitAccountId, $creditAccountId) {
            $trxNumber = $this->generateTrxNumber($trxDate);
            $prevHash = Transaction::orderByDesc('id')->value('row_hash') ?? str_repeat('0', 64);
            $rowHash = hash('sha256', implode('|', [
                $prevHash,
                $trxNumber,
                $trxDate,
                $type,
                number_format($amount, 2, '.', ''),
            ]));

            $transaction = Transaction::create([
                'trx_number' => $trxNumber,
                'trx_date' => $trxDate,
                'type' => $type,
                'fund_id' => $fund->id,
                'category_id' => $data['category_id'] ?? null,
                'cash_account_id' => $data['cash_account_id'],
                'to_cash_account_id' => $data['to_cash_account_id'] ?? null,
                'amount' => $amount,
                'description' => $data['description'],
                'counterparty' => $data['counterparty'] ?? null,
                'status' => 'posted',
                'created_by' => $userId,
                'row_hash' => $rowHash,
            ]);

            $entry = JournalEntry::create([
                'transaction_id' => $transaction->id,
                'entry_date' => $trxDate,
                'fund_id' => $fund->id,
                'accounting_period_id' => $period->id,
                'memo' => $data['description'],
            ]);

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $debitAccountId,
                'debit' => $amount,
                'credit' => 0,
            ]);

            JournalLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $creditAccountId,
                'debit' => 0,
                'credit' => $amount,
            ]);

            return $transaction->load('journalEntry.lines.account');
        });
    }

    /**
     * Void a posted transaction without deleting historical records.
     */
    public function voidTransaction(Transaction $transaction, string $reason): Transaction
    {
        if ($transaction->status === 'void') {
            throw new InvalidArgumentException('Transaksi sudah berstatus void.');
        }

        $periodCode = $transaction->trx_date->format('Y-m');
        $period = AccountingPeriod::where('period', $periodCode)->first();
        if ($period && $period->status === 'closed') {
            throw new InvalidArgumentException("Periode {$periodCode} sudah ditutup. Transaksi tidak dapat dibatalkan.");
        }

        $transaction->update([
            'status' => 'void',
            'void_reason' => $reason,
        ]);

        return $transaction->fresh();
    }

    /**
     * Compute the non-profit Balance Sheet (Aset = Liabilitas + Aset Neto).
     */
    public function getBalanceSheet(string $asOfDate): array
    {
        $rows = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('transactions', 'transactions.id', '=', 'journal_entries.transaction_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('transactions.status', 'posted')
            ->whereDate('journal_entries.entry_date', '<=', $asOfDate)
            ->selectRaw('accounts.type, accounts.normal_balance, SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->groupBy('accounts.type', 'accounts.normal_balance')
            ->get();

        $totals = ['asset' => 0.0, 'liability' => 0.0, 'net_asset' => 0.0, 'revenue' => 0.0, 'expense' => 0.0];

        foreach ($rows as $row) {
            $balance = $row->normal_balance === 'debit'
                ? (float) $row->total_debit - (float) $row->total_credit
                : (float) $row->total_credit - (float) $row->total_debit;
            $totals[$row->type] += $balance;
        }

        $surplus = $totals['revenue'] - $totals['expense'];
        $netAssetTotal = $totals['net_asset'] + $surplus;

        return [
            'as_of' => $asOfDate,
            'assets' => round($totals['asset'], 2),
            'liabilities' => round($totals['liability'], 2),
            'net_assets' => round($netAssetTotal, 2),
            'current_surplus' => round($surplus, 2),
            'is_balanced' => round($totals['asset'], 2) === round($totals['liability'] + $netAssetTotal, 2),
        ];
    }

    private function resolveJournalAccounts(string $type, Fund $fund, CashAccount $cashAccount, array $data): array
    {
        if ($type === 'transfer') {
            if (empty($data['to_cash_account_id']) || (int) $data['to_cash_account_id'] === $cashAccount->id) {
                throw new InvalidArgumentException('Rekening kas tujuan transfer harus berbeda dari kas asal.');
            }
            $toCash = CashAccount::findOrFail($data['to_cash_account_id']);

            return [$toCash->account_id, $cashAccount->account_id];
        }

        $category = Category::with('account')->findOrFail($data['category_id']);

        if ($fund->isRestricted() && $type === 'expense' && str_starts_with($category->account->code, '51')) {
            throw new InvalidArgumentException('Dana ini berasal dari dana terikat dan hanya dapat disalurkan sesuai peruntukannya. Pilih dana lain?');
        }

        return $type === 'income'
            ? [$cashAccount->account_id, $category->account_id]
            : [$category->account_id, $cashAccount->account_id];
    }

    private function generateTrxNumber(string $trxDate): string
    {
        $prefix = 'TRX-'.str_replace('-', '', substr($trxDate, 0, 7)).'-';
        $count = Transaction::where('trx_number', 'like', $prefix.'%')->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
