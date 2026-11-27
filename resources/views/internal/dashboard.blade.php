@extends('layouts.app')

@section('title', 'Dashboard Pengurus — Masjid Pintar')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">
    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h1 style="font-size:2rem;">Assalamu'alaikum, {{ auth()->user()->name }}</h1>
            <p style="color:var(--text-muted); margin:4px 0 0 0;">{{ now()->translatedFormat('l, d F Y') }} • Ringkasan keuangan masjid</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">+ Catat Transaksi</a>
    </div>

    <section class="grid grid-4">
        <div class="card">
            <div class="label">Total Saldo</div>
            <div class="tabular" style="font-size:1.45rem; font-weight:600; margin-top:6px;">Rp {{ number_format($balanceSheet['assets'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Masuk Bulan Ini</div>
            <div class="tabular" style="font-size:1.45rem; font-weight:600; color:var(--secondary); margin-top:6px;">+ Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Keluar Bulan Ini</div>
            <div class="tabular" style="font-size:1.45rem; font-weight:600; color:var(--error); margin-top:6px;">- Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Skor Kesehatan</div>
            <div class="tabular" style="font-size:1.45rem; font-weight:600; color:var(--success); margin-top:6px;">{{ $healthScore }} / 100</div>
        </div>
    </section>

    <section class="grid grid-2">
        <div class="card">
            <h2 style="font-size:1.3rem; margin-bottom:14px;">Komposisi Saldo per Dana</h2>
            <div style="display:flex; flex-direction:column; gap:10px;">
                @foreach($funds as $fund)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; border-radius:var(--radius-sm); background:var(--surface-variant);">
                        <div>
                            <div style="font-weight:500;">{{ $fund->name }}</div>
                            <div style="font-size:0.8rem; color:var(--text-muted);">{{ $fund->isRestricted() ? 'Dana Terikat' : 'Tidak Terikat' }}</div>
                        </div>
                        <strong class="tabular">Rp {{ number_format($fund->balance, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h2 style="font-size:1.3rem;">Catatan Terbaru</h2>
                <a href="{{ route('transactions.index') }}" style="font-size:0.9rem;">Lihat semua →</a>
            </div>
            @if($recentTransactions->isEmpty())
                <p style="color:var(--text-muted); margin:0;">Belum ada transaksi bulan ini. Mulai dengan mencatat infak pertama.</p>
            @else
                <div style="display:flex; flex-direction:column; gap:10px;">
                    @foreach($recentTransactions as $trx)
                        <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:10px; border-bottom:1px solid var(--outline);">
                            <div>
                                <div style="font-weight:500;">{{ $trx->description }}</div>
                                <div style="font-size:0.8rem; color:var(--text-muted);">{{ $trx->trx_date->format('d M Y') }} • {{ $trx->fund->name }}</div>
                            </div>
                            <strong class="tabular" style="color:{{ $trx->type === 'income' ? 'var(--secondary)' : 'var(--error)' }};">
                                {{ $trx->type === 'income' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </strong>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
