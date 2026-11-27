<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Category;
use App\Models\Fund;
use Illuminate\Database\Seeder;

class MasjidMasterSeeder extends Seeder
{
    public function run(): void
    {
        $funds = [
            ['code' => 'UMUM', 'name' => 'Dana Umum / Operasional', 'restriction' => 'unrestricted'],
            ['code' => 'ZAKAT', 'name' => 'Dana Zakat', 'restriction' => 'restricted'],
            ['code' => 'WAKAF', 'name' => 'Dana Wakaf', 'restriction' => 'restricted'],
            ['code' => 'PEMBANGUNAN', 'name' => 'Dana Pembangunan Masjid', 'restriction' => 'restricted'],
            ['code' => 'QURBAN', 'name' => 'Dana Qurban', 'restriction' => 'restricted'],
        ];

        $fundMap = [];
        foreach ($funds as $fund) {
            $record = Fund::updateOrCreate(['code' => $fund['code']], $fund);
            $fundMap[$fund['code']] = $record->id;
        }

        $accounts = [
            ['code' => '1101', 'name' => 'Kas Tunai Masjid', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1102', 'name' => 'Bank Syariah (BSI)', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1103', 'name' => 'E-Wallet / QRIS Masjid', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1201', 'name' => 'Inventaris & Peralatan Masjid', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '2101', 'name' => 'Titipan Dana Zakat', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2102', 'name' => 'Titipan Dana Qurban', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '3101', 'name' => 'Aset Neto Tidak Terikat', 'type' => 'net_asset', 'normal_balance' => 'credit'],
            ['code' => '3201', 'name' => 'Aset Neto Terikat Wakaf & Pembangunan', 'type' => 'net_asset', 'normal_balance' => 'credit'],
            ['code' => '4101', 'name' => 'Pendapatan Infak Jumat', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4102', 'name' => 'Pendapatan Infak & Sedekah Umum', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4201', 'name' => 'Pendapatan Dana Terikat Wakaf & Pembangunan', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '5101', 'name' => 'Beban Gaji Marbot & Imam', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5102', 'name' => 'Beban Listrik, Air & Kebersihan', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5103', 'name' => 'Beban Pemeliharaan & Kajian', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5201', 'name' => 'Beban Penyaluran Zakat & Sosial', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5202', 'name' => 'Beban Pembangunan & Wakaf', 'type' => 'expense', 'normal_balance' => 'debit'],
        ];

        $accMap = [];
        foreach ($accounts as $account) {
            $record = Account::updateOrCreate(['code' => $account['code']], $account);
            $accMap[$account['code']] = $record->id;
        }

        $cashAccounts = [
            ['name' => 'Kas Tunai Sekretariat', 'type' => 'cash', 'account_id' => $accMap['1101'], 'opening_balance' => 0],
            ['name' => 'Bank BSI Operasional', 'type' => 'bank', 'account_id' => $accMap['1102'], 'opening_balance' => 0],
            ['name' => 'QRIS Masjid Pintar', 'type' => 'ewallet', 'account_id' => $accMap['1103'], 'opening_balance' => 0],
        ];

        foreach ($cashAccounts as $cash) {
            CashAccount::updateOrCreate(['name' => $cash['name']], $cash);
        }

        $categories = [
            ['name' => 'Infak Jumat', 'type' => 'income', 'account_id' => $accMap['4101'], 'default_fund_id' => $fundMap['UMUM'], 'icon' => 'coins'],
            ['name' => 'Infak & Sedekah Harian', 'type' => 'income', 'account_id' => $accMap['4102'], 'default_fund_id' => $fundMap['UMUM'], 'icon' => 'heart'],
            ['name' => 'Penerimaan Zakat', 'type' => 'income', 'account_id' => $accMap['2101'], 'default_fund_id' => $fundMap['ZAKAT'], 'icon' => 'hand-helping'],
            ['name' => 'Wakaf & Pembangunan', 'type' => 'income', 'account_id' => $accMap['4201'], 'default_fund_id' => $fundMap['WAKAF'], 'icon' => 'building'],
            ['name' => 'Gaji Marbot & Imam', 'type' => 'expense', 'account_id' => $accMap['5101'], 'default_fund_id' => $fundMap['UMUM'], 'icon' => 'users'],
            ['name' => 'Listrik, Air & Kebersihan', 'type' => 'expense', 'account_id' => $accMap['5102'], 'default_fund_id' => $fundMap['UMUM'], 'icon' => 'zap'],
            ['name' => 'Pemeliharaan & Kajian', 'type' => 'expense', 'account_id' => $accMap['5103'], 'default_fund_id' => $fundMap['UMUM'], 'icon' => 'book-open'],
            ['name' => 'Penyaluran Zakat (Mustahik)', 'type' => 'expense', 'account_id' => $accMap['5201'], 'default_fund_id' => $fundMap['ZAKAT'], 'icon' => 'gift'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name'], 'type' => $category['type']], $category);
        }
    }
}
