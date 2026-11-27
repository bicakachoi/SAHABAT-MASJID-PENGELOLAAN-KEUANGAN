<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\Transaction;
use App\Services\JournalPostingService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly JournalPostingService $journalService) {}

    public function index(): View
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

        $cashRatioMonths = $monthlyExpense > 0 ? min(12, $balanceSheet['assets'] / $monthlyExpense) : 6;
        $healthScore = (int) round(min(100, ($cashRatioMonths / 6) * 100));

        $funds = Fund::where('is_active', true)->get()->map(function (Fund $fund) {
            $in = (float) Transaction::where('fund_id', $fund->id)->where('status', 'posted')->where('type', 'income')->sum('amount');
            $out = (float) Transaction::where('fund_id', $fund->id)->where('status', 'posted')->where('type', 'expense')->sum('amount');
            $fund->balance = $in - $out;

            return $fund;
        });

        $recentTransactions = Transaction::with(['fund', 'category', 'cashAccount'])
            ->latest('trx_date')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('internal.dashboard', [
            'balanceSheet' => $balanceSheet,
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpense' => $monthlyExpense,
            'healthScore' => $healthScore,
            'funds' => $funds,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
