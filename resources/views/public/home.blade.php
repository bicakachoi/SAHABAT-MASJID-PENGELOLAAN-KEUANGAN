@extends('layouts.app')

@section('title', 'Beranda Transparansi — Masjid Pintar')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">
    <section class="hero-card">
        <div class="geo-bg" aria-hidden="true"></div>
        <div class="hero-content">
            <span class="badge badge-success">Transparansi Keuangan Publik</span>
            <p class="label" style="margin-top:16px;">Total Saldo Kas & Bank Masjid</p>
            <div class="hero-amount tabular">Rp {{ number_format($totalBalance, 0, ',', '.') }}</div>
            <p style="color:var(--text-muted); margin:0 0 24px 0; max-width:60ch;">
                @if($lastUpdated)
                    Pembaruan terakhir {{ $lastUpdated->format('d M Y, H:i') }} WIB. Seluruh catatan dijaga dengan segregasi dana terikat dan jejak audit.
                @else
                    Belum ada transaksi tercatat. Pengurus dapat mulai mencatat transaksi melalui panel internal.
                @endif
            </p>
            <div style="display:flex; flex-wrap:wrap; gap:12px;">
                <a href="{{ route('public.donate') }}" class="btn btn-primary">Berdonasi Sekarang</a>
                <a href="{{ route('public.report') }}" class="btn btn-secondary">Lihat Laporan & Neraca</a>
            </div>
        </div>
    </section>

    <section class="grid grid-2">
        <div class="card">
            <div class="label">Uang Masuk Bulan Ini</div>
            <div class="tabular" style="font-size:1.75rem; font-weight:600; color:var(--secondary); margin-top:6px;">
                + Rp {{ number_format($monthlyIncome, 0, ',', '.') }}
            </div>
            <p style="margin:8px 0 0 0; font-size:0.9rem; color:var(--text-muted);">Akumulasi infak, sedekah, dan penerimaan bulan berjalan.</p>
        </div>

        <div class="card">
            <div class="label">Uang Keluar Bulan Ini</div>
            <div class="tabular" style="font-size:1.75rem; font-weight:600; color:var(--error); margin-top:6px;">
                - Rp {{ number_format($monthlyExpense, 0, ',', '.') }}
            </div>
            <p style="margin:8px 0 0 0; font-size:0.9rem; color:var(--text-muted);">Operasional masjid, pemeliharaan, dan penyaluran program.</p>
        </div>
    </section>

    <section class="card">
        <h2 style="font-size:1.4rem; margin-bottom:6px;">Ke Mana Infak Anda Pergi</h2>
        <p style="color:var(--text-muted); margin-top:0; margin-bottom:20px;">Ringkasan 5 pos pengeluaran terbesar masjid.</p>

        @if($expenseBreakdown->isEmpty())
            <p style="color:var(--text-muted); margin:0;">Belum ada pengeluaran yang diposting pada periode ini.</p>
        @else
            @php $maxExp = max(1, (float) $expenseBreakdown->max('total')); @endphp
            <div style="display:flex; flex-direction:column; gap:14px;">
                @foreach($expenseBreakdown as $item)
                    @php $pct = round(((float) $item->total / $maxExp) * 100); @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:0.95rem; margin-bottom:6px;">
                            <span>{{ $item->category_name }}</span>
                            <strong class="tabular">Rp {{ number_format($item->total, 0, ',', '.') }}</strong>
                        </div>
                        <div style="height:10px; border-radius:999px; background:var(--surface-variant); overflow:hidden;">
                            <div style="height:100%; width:{{ $pct }}%; background:var(--primary); border-radius:999px;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
