<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\Fund;
use App\Models\Transaction;
use App\Services\JournalPostingService;
use Illuminate\View\View;

class PublicTransparencyController extends Controller
{
    public function __construct(private readonly JournalPostingService $journalService) {}

    public function home(): View
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $balanceSheet = $this->journalService->getBalanceSheet($today);

        $monthlyIncome = (float) Transaction::where('status', 'posted')
            ->where('type', 'income')
            ->whereBetween('trx_date', [$monthStart, $today])
            ->sum('amount');

        $monthlyExpense = (float) Transaction::where('status', 'posted')
            ->where('type', 'expense')
            ->whereBetween('trx_date', [$monthStart, $today])
            ->sum('amount');

        $expenseBreakdown = Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.status', 'posted')
            ->where('transactions.type', 'expense')
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('public.home', [
            'totalBalance' => $balanceSheet['assets'],
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'expenseBreakdown' => $expenseBreakdown,
            'lastUpdated' => Transaction::where('status', 'posted')->latest('updated_at')->value('updated_at'),
        ]);
    }

    public function report(): View
    {
        $asOf = request('as_of', now()->toDateString());
        $balanceSheet = $this->journalService->getBalanceSheet($asOf);

        $recentTransactions = Transaction::with(['fund', 'category'])
            ->where('status', 'posted')
            ->latest('trx_date')
            ->latest('id')
            ->limit(15)
            ->get();

        return view('public.report', compact('balanceSheet', 'recentTransactions', 'asOf'));
    }

    public function donate(): View
    {
        $funds = Fund::where('is_active', true)->get();
        $bankAccounts = CashAccount::whereIn('type', ['bank', 'ewallet'])->get();

        return view('public.donate', compact('funds', 'bankAccounts'));
    }
}
