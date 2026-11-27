<?php

namespace App\Http\Controllers;

use App\Models\CashAccount;
use App\Models\Category;
use App\Models\Fund;
use App\Models\Transaction;
use App\Services\JournalPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class TransactionController extends Controller
{
    public function __construct(private readonly JournalPostingService $journalService) {}

    public function index(Request $request): View
    {
        $query = Transaction::with(['fund', 'category', 'cashAccount', 'toCashAccount'])
            ->latest('trx_date')
            ->latest('id');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('fund_id')) {
            $query->where('fund_id', $request->integer('fund_id'));
        }

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', $q)
                    ->orWhere('trx_number', 'like', $q);
            });
        }

        $transactions = $query->paginate(15)->withQueryString();
        $funds = Fund::where('is_active', true)->get();

        return view('internal.transactions.index', compact('transactions', 'funds'));
    }

    public function create(): View
    {
        $funds = Fund::where('is_active', true)->get();
        $categories = Category::orderBy('type')->orderBy('name')->get();
        $cashAccounts = CashAccount::all();

        return view('internal.transactions.create', compact('funds', 'categories', 'cashAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense,transfer'],
            'trx_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'fund_id' => ['required', 'exists:funds,id'],
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'to_cash_account_id' => ['nullable', 'exists:cash_accounts,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:255'],
            'counterparty' => ['nullable', 'string', 'max:120'],
        ]);

        if ($validated['type'] !== 'transfer' && empty($validated['category_id'])) {
            return back()->withErrors(['category_id' => 'Pilih kategori transaksi terlebih dahulu.'])->withInput();
        }

        try {
            $this->journalService->recordTransaction($validated, (int) $request->user()->id);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['guard' => $e->getMessage()])->withInput();
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Catatan tersimpan. Jazakumullah khairan.');
    }

    public function void(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->journalService->voidTransaction($transaction, $validated['void_reason']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['void' => $e->getMessage()]);
        }

        return back()->with('success', 'Transaksi dibatalkan (void) dengan alasan tercatat.');
    }
}
