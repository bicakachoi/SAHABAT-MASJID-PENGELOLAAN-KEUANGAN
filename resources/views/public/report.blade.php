@extends('layouts.app')

@section('title', 'Laporan Publik — Masjid Pintar')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">
    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-end; gap:16px;">
        <div>
            <h1 style="font-size:2rem;">Laporan Keuangan Publik</h1>
            <p style="color:var(--text-muted); margin:4px 0 0 0;">Posisi keuangan nirlaba per {{ \Carbon\Carbon::parse($asOf)->format('d M Y') }}.</p>
        </div>
        <span class="badge {{ $balanceSheet['is_balanced'] ? 'badge-success' : 'badge-warning' }}">
            {{ $balanceSheet['is_balanced'] ? '✓ Neraca Seimbang' : 'Perlu Pemeriksaan' }}
        </span>
    </div>

    <section class="grid grid-3">
        <div class="card">
            <div class="label">Total Aset (Kas & Bank)</div>
            <div class="tabular" style="font-size:1.5rem; font-weight:600; margin-top:6px;">Rp {{ number_format($balanceSheet['assets'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Liabilitas (Titipan Zakat/Qurban)</div>
            <div class="tabular" style="font-size:1.5rem; font-weight:600; margin-top:6px;">Rp {{ number_format($balanceSheet['liabilities'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Aset Neto (Surplus Berjalan)</div>
            <div class="tabular" style="font-size:1.5rem; font-weight:600; margin-top:6px;">Rp {{ number_format($balanceSheet['net_assets'], 0, ',', '.') }}</div>
        </div>
    </section>

    <section class="card">
        <h2 style="font-size:1.35rem; margin-bottom:16px;">Transaksi Terakhir yang Disetujui</h2>
        @if($recentTransactions->isEmpty())
            <p style="color:var(--text-muted); margin:0;">Belum ada transaksi bulan ini.</p>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Dana</th>
                            <th>Kategori</th>
                            <th class="text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $trx)
                            <tr>
                                <td class="tabular">{{ $trx->trx_date->format('d/m/Y') }}</td>
                                <td>{{ $trx->description }}</td>
                                <td><span class="badge badge-neutral">{{ $trx->fund->name }}</span></td>
                                <td>{{ $trx->category->name ?? 'Transfer Kas' }}</td>
                                <td class="text-right tabular" style="font-weight:600; color:{{ $trx->type === 'income' ? 'var(--secondary)' : ($trx->type === 'expense' ? 'var(--error)' : 'var(--text)') }};">
                                    {{ $trx->type === 'income' ? '+' : ($trx->type === 'expense' ? '-' : '⇄') }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
