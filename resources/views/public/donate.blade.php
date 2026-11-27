@extends('layouts.app')

@section('title', 'Berdonasi — Masjid Pintar')

@section('content')
<div class="container-narrow" style="padding:0; display:flex; flex-direction:column; gap:24px;">
    <section class="hero-card">
        <div class="geo-bg" aria-hidden="true"></div>
        <div class="hero-content">
            <span class="badge badge-success">Salurkan Infak, Sedekah & Zakat</span>
            <h1 style="font-size:2rem; margin-top:12px;">Berdonasi untuk Kemakmuran Masjid</h1>
            <p style="color:var(--text-muted); margin:8px 0 0 0;">
                Setiap donasi dicatat terpisah sesuai peruntukannya agar amanah umat terjaga.
            </p>
        </div>
    </section>

    <section class="card">
        <h2 style="font-size:1.35rem; margin-bottom:14px;">Rekening & Kanal Resmi</h2>
        <div style="display:flex; flex-direction:column; gap:12px;">
            @forelse($bankAccounts as $acc)
                <div style="padding:16px; border-radius:var(--radius-sm); background:var(--surface-variant); display:flex; justify-content:space-between; align-items:center; gap:12px;">
                    <div>
                        <div style="font-weight:600;">{{ $acc->name }}</div>
                        <div style="font-size:0.85rem; color:var(--text-muted);">Tipe: {{ strtoupper($acc->type) }}</div>
                    </div>
                    <span class="badge badge-neutral">Aktif</span>
                </div>
            @empty
                <p style="color:var(--text-muted);">Belum ada rekening bank terdaftar.</p>
            @endforelse
        </div>
    </section>

    <section class="card">
        <h2 style="font-size:1.35rem; margin-bottom:14px;">Pilihan Program Dana</h2>
        <div class="grid grid-2">
            @foreach($funds as $fund)
                <div style="padding:16px; border:1px solid var(--outline); border-radius:var(--radius-sm);">
                    <div style="font-weight:600;">{{ $fund->name }}</div>
                    <div style="font-size:0.85rem; color:var(--text-muted); margin-top:4px;">
                        {{ $fund->isRestricted() ? 'Dana Terikat (khusus peruntukan syariah)' : 'Dana Umum (operasional & kegiatan)' }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
