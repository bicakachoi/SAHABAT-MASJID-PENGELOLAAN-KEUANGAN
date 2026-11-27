<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\JournalPostingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BalanceSheetController extends Controller
{
    public function __construct(private readonly JournalPostingService $journalService) {}

    public function index(Request $request): View
    {
        $asOf = $request->input('as_of', now()->toDateString());
        $balanceSheet = $this->journalService->getBalanceSheet($asOf);

        $accountRows = JournalLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->join('transactions', 'transactions.id', '=', 'journal_entries.transaction_id')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->where('transactions.status', 'posted')
            ->whereDate('journal_entries.entry_date', '<=', $asOf)
            ->whereIn('accounts.type', ['asset', 'liability', 'net_asset'])
            ->selectRaw('accounts.id, accounts.code, accounts.name, accounts.type, accounts.normal_balance, SUM(journal_lines.debit) as total_debit, SUM(journal_lines.credit) as total_credit')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.type', 'accounts.normal_balance')
            ->orderBy('accounts.code')
            ->get()
            ->map(function ($row) {
                $row->balance = $row->normal_balance === 'debit'
                    ? (float) $row->total_debit - (float) $row->total_credit
                    : (float) $row->total_credit - (float) $row->total_debit;

                return $row;
            });

        $recentJournals = JournalEntry::with(['lines.account', 'transaction'])
            ->whereHas('transaction', fn ($q) => $q->where('status', 'posted'))
            ->whereDate('entry_date', '<=', $asOf)
            ->latest('entry_date')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('internal.balance-sheet', compact('asOf', 'balanceSheet', 'accountRows', 'recentJournals'));
    }
}
