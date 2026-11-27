<?php

namespace Tests\Feature;

use App\Models\AccountingPeriod;
use App\Models\CashAccount;
use App\Models\Category;
use App\Models\Fund;
use App\Models\User;
use App\Services\JournalPostingService;
use Database\Seeders\MasjidMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class JournalPostingTest extends TestCase
{
    use RefreshDatabase;

    private JournalPostingService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasjidMasterSeeder::class);
        $this->service = app(JournalPostingService::class);
        $this->user = User::factory()->create();
    }

    public function test_income_and_expense_generate_balanced_double_entry_and_balance_sheet(): void
    {
        $umum = Fund::where('code', 'UMUM')->firstOrFail();
        $kas = CashAccount::where('type', 'cash')->firstOrFail();
        $infakJumat = Category::where('name', 'Infak Jumat')->firstOrFail();
        $listrik = Category::where('name', 'Listrik, Air & Kebersihan')->firstOrFail();

        $trxIn = $this->service->recordTransaction([
            'trx_date' => '2026-11-27',
            'type' => 'income',
            'fund_id' => $umum->id,
            'category_id' => $infakJumat->id,
            'cash_account_id' => $kas->id,
            'amount' => 2000000,
            'description' => 'Infak Jumat Pekan 4',
        ], $this->user->id);

        $this->assertNotNull($trxIn->row_hash);
        $this->assertCount(2, $trxIn->journalEntry->lines);

        $this->service->recordTransaction([
            'trx_date' => '2026-11-27',
            'type' => 'expense',
            'fund_id' => $umum->id,
            'category_id' => $listrik->id,
            'cash_account_id' => $kas->id,
            'amount' => 850000,
            'description' => 'Bayar Listrik PLN',
        ], $this->user->id);

        $bs = $this->service->getBalanceSheet('2026-11-30');
        $this->assertTrue($bs['is_balanced']);
        $this->assertEquals(1150000.0, $bs['assets']);
        $this->assertEquals(1150000.0, $bs['net_assets']);
    }

    public function test_restricted_fund_guard_blocks_operational_expense(): void
    {
        $zakat = Fund::where('code', 'ZAKAT')->firstOrFail();
        $kas = CashAccount::where('type', 'cash')->firstOrFail();
        $listrik = Category::where('name', 'Listrik, Air & Kebersihan')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);

        $this->service->recordTransaction([
            'trx_date' => '2026-11-27',
            'type' => 'expense',
            'fund_id' => $zakat->id,
            'category_id' => $listrik->id,
            'cash_account_id' => $kas->id,
            'amount' => 500000,
            'description' => 'Bayar listrik pakai dana zakat',
        ], $this->user->id);
    }

    public function test_closed_accounting_period_rejects_new_transactions(): void
    {
        AccountingPeriod::create([
            'period' => '2026-10',
            'status' => 'closed',
            'closed_by' => $this->user->id,
            'closed_at' => now(),
        ]);

        $umum = Fund::where('code', 'UMUM')->firstOrFail();
        $kas = CashAccount::where('type', 'cash')->firstOrFail();
        $infakJumat = Category::where('name', 'Infak Jumat')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);

        $this->service->recordTransaction([
            'trx_date' => '2026-10-15',
            'type' => 'income',
            'fund_id' => $umum->id,
            'category_id' => $infakJumat->id,
            'cash_account_id' => $kas->id,
            'amount' => 1000000,
            'description' => 'Transaksi di periode tertutup',
        ], $this->user->id);
    }
}
