<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\Category;
use App\Models\Fund;
use App\Models\User;
use Database\Seeders\MasjidMasterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasjidPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasjidMasterSeeder::class);
    }

    public function test_public_pages_are_accessible_without_login(): void
    {
        $this->get('/')->assertOk()->assertSee('Masjid Pintar');
        $this->get('/laporan-publik')->assertOk()->assertSee('Laporan Keuangan Publik');
        $this->get('/donasi')->assertOk()->assertSee('Berdonasi');
        $this->get('/login')->assertOk()->assertSee('Masuk Pengurus');
    }

    public function test_internal_pages_redirect_guests_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/transaksi')->assertRedirect('/login');
        $this->get('/transaksi/catat')->assertRedirect('/login');
        $this->get('/neraca')->assertRedirect('/login');
    }

    public function test_authenticated_treasurer_can_access_pages_and_record_transaction(): void
    {
        $user = User::factory()->create();
        $umum = Fund::where('code', 'UMUM')->firstOrFail();
        $kas = CashAccount::where('type', 'cash')->firstOrFail();
        $infak = Category::where('name', 'Infak Jumat')->firstOrFail();

        $this->actingAs($user);

        $this->get('/dashboard')->assertOk()->assertSee($user->name);
        $this->get('/transaksi')->assertOk();
        $this->get('/transaksi/catat')->assertOk();
        $this->get('/neraca')->assertOk()->assertSee('Seimbang');

        $response = $this->post('/transaksi', [
            'type' => 'income',
            'trx_date' => '2026-11-27',
            'amount' => 1500000,
            'fund_id' => $umum->id,
            'category_id' => $infak->id,
            'cash_account_id' => $kas->id,
            'description' => 'Infak Jumat Tes UI',
        ]);

        $response->assertRedirect('/transaksi');
        $this->assertDatabaseHas('transactions', ['description' => 'Infak Jumat Tes UI', 'status' => 'posted']);
    }
}
