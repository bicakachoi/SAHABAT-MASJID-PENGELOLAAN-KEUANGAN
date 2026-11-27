@extends('layouts.app')

@section('title', 'Catat Transaksi — Masjid Pintar')

@section('content')
<div class="container-narrow" style="padding:0;">
    <div style="margin-bottom:20px;">
        <a href="{{ route('transactions.index') }}" style="font-size:0.9rem;">← Kembali ke Daftar Transaksi</a>
        <h1 style="font-size:2rem; margin-top:8px;">Catat Transaksi Cepat</h1>
        <p style="color:var(--text-muted); margin:4px 0 0 0;">Isi nominal dan keterangan sederhana; jurnal akuntansi dibuat otomatis di balik layar.</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('transactions.store') }}" id="trx-form">
            @csrf
            <input type="hidden" name="type" id="trx-type" value="{{ old('type', 'income') }}">

            <div class="segment-group" role="tablist" aria-label="Jenis Transaksi">
                <button type="button" class="segment-btn" data-type="income">Uang Masuk</button>
                <button type="button" class="segment-btn" data-type="expense">Uang Keluar</button>
                <button type="button" class="segment-btn" data-type="transfer">Pindah Kas</button>
            </div>

            <div class="field">
                <label class="label" for="amount">Nominal (Rp)</label>
                <input id="amount" type="number" name="amount" min="1" step="1" value="{{ old('amount') }}" class="input input-nominal" placeholder="0" required autofocus>
            </div>

            <div class="grid grid-2">
                <div class="field">
                    <label class="label" for="trx_date">Tanggal</label>
                    <input id="trx_date" type="date" name="trx_date" value="{{ old('trx_date', now()->toDateString()) }}" class="input" required>
                </div>

                <div class="field">
                    <label class="label" for="fund_id">Jenis Dana</label>
                    <select id="fund_id" name="fund_id" class="select" required>
                        @foreach($funds as $fund)
                            <option value="{{ $fund->id }}" @selected((string) old('fund_id') === (string) $fund->id)>
                                {{ $fund->name }} {{ $fund->isRestricted() ? '(Terikat)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field" id="category-field">
                <label class="label" for="category_id">Kategori</label>
                <select id="category_id" name="category_id" class="select">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" data-cat-type="{{ $cat->type }}" @selected((string) old('category_id') === (string) $cat->id)>
                            {{ $cat->name }} ({{ $cat->type === 'income' ? 'Masuk' : 'Keluar' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-2">
                <div class="field">
                    <label class="label" for="cash_account_id" id="cash-label">Masuk ke Kas / Bank</label>
                    <select id="cash_account_id" name="cash_account_id" class="select" required>
                        @foreach($cashAccounts as $cash)
                            <option value="{{ $cash->id }}" @selected((string) old('cash_account_id') === (string) $cash->id)>{{ $cash->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field" id="to-cash-field" style="display:none;">
                    <label class="label" for="to_cash_account_id">Ke Rekening Kas Tujuan</label>
                    <select id="to_cash_account_id" name="to_cash_account_id" class="select">
                        <option value="">-- Pilih Tujuan --</option>
                        @foreach($cashAccounts as $cash)
                            <option value="{{ $cash->id }}" @selected((string) old('to_cash_account_id') === (string) $cash->id)>{{ $cash->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="field">
                <label class="label" for="description">Keterangan Singkat</label>
                <input id="description" type="text" name="description" value="{{ old('description') }}" class="input" placeholder="Contoh: Infak Jumat Pekan 4 / Bayar Listrik PLN" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Catatan</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const typeInput = document.getElementById('trx-type');
        const buttons = document.querySelectorAll('.segment-btn');
        const catField = document.getElementById('category-field');
        const toCashField = document.getElementById('to-cash-field');
        const cashLabel = document.getElementById('cash-label');
        const catSelect = document.getElementById('category_id');

        function applyType(type) {
            typeInput.value = type;
            buttons.forEach(b => b.classList.toggle('active', b.dataset.type === type));
            catField.style.display = type === 'transfer' ? 'none' : 'flex';
            toCashField.style.display = type === 'transfer' ? 'flex' : 'none';
            cashLabel.textContent = type === 'income' ? 'Masuk ke Kas / Bank' : (type === 'expense' ? 'Keluar dari Kas / Bank' : 'Dari Kas / Bank');

            Array.from(catSelect.options).forEach(opt => {
                if (!opt.value) return;
                const match = opt.dataset.catType === type;
                opt.hidden = !match;
                if (!match && opt.selected) opt.selected = false;
            });
        }

        buttons.forEach(b => b.addEventListener('click', () => applyType(b.dataset.type)));
        applyType(typeInput.value || 'income');
    })();
</script>
@endpush
@endsection
