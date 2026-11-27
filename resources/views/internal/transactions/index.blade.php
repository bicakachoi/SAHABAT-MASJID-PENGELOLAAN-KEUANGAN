@extends('layouts.app')

@section('title', 'Daftar Transaksi — Masjid Pintar')

@section('content')
<div style="display:flex; flex-direction:column; gap:20px;">
    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px;">
        <div>
            <h1 style="font-size:2rem;">Transaksi Keuangan</h1>
            <p style="color:var(--text-muted); margin:4px 0 0 0;">Catatan uang masuk, keluar, dan pindah kas dengan jejak audit.</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary">+ Catat Transaksi</a>
    </div>

    <form method="GET" action="{{ route('transactions.index') }}" class="card" style="padding:16px;">
        <div class="grid grid-4" style="align-items:end;">
            <div class="field" style="margin:0;">
                <label class="label" for="q">Cari Keterangan / Nomor</label>
                <input id="q" type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Infak, listrik, TRX...">
            </div>
            <div class="field" style="margin:0;">
                <label class="label" for="type">Jenis</label>
                <select id="type" name="type" class="select">
                    <option value="">Semua Jenis</option>
                    <option value="income" @selected(request('type') === 'income')>Uang Masuk</option>
                    <option value="expense" @selected(request('type') === 'expense')>Uang Keluar</option>
                    <option value="transfer" @selected(request('type') === 'transfer')>Pindah Kas</option>
                </select>
            </div>
            <div class="field" style="margin:0;">
                <label class="label" for="fund_id">Dana</label>
                <select id="fund_id" name="fund_id" class="select">
                    <option value="">Semua Dana</option>
                    @foreach($funds as $fund)
                        <option value="{{ $fund->id }}" @selected((string) request('fund_id') === (string) $fund->id)>{{ $fund->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-secondary" style="flex:1;">Terapkan</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <div class="card" style="padding:0; overflow:hidden;">
        @if($transactions->isEmpty())
            <div style="padding:36px; text-align:center;">
                <p style="color:var(--text-muted); margin:0 0 16px 0;">Belum ada transaksi yang sesuai filter. Mulai dengan mencatat infak pertama.</p>
                <a href="{{ route('transactions.create') }}" class="btn btn-primary">+ Catat Transaksi</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nomor & Keterangan</th>
                            <th>Dana & Kas</th>
                            <th>Status</th>
                            <th class="text-right">Nominal</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $trx)
                            <tr>
                                <td class="tabular">{{ $trx->trx_date->format('d/m/Y') }}</td>
                                <td>
                                    <div style="font-weight:500; {{ $trx->status === 'void' ? 'text-decoration:line-through; color:var(--text-muted);' : '' }}">{{ $trx->description }}</div>
                                    <div style="font-size:0.8rem; color:var(--text-muted);">{{ $trx->trx_number }} • {{ $trx->category->name ?? 'Pindah Kas' }}</div>
                                </td>
                                <td>
                                    <div>{{ $trx->fund->name }}</div>
                                    <div style="font-size:0.8rem; color:var(--text-muted);">{{ $trx->cashAccount->name }}</div>
                                </td>
                                <td>
                                    @if($trx->status === 'posted')
                                        <span class="badge badge-success">Posted</span>
                                    @elseif($trx->status === 'void')
                                        <span class="badge badge-error" title="{{ $trx->void_reason }}">Void</span>
                                    @else
                                        <span class="badge badge-neutral">Draft</span>
                                    @endif
                                </td>
                                <td class="text-right tabular" style="font-weight:600; color:{{ $trx->type === 'income' ? 'var(--secondary)' : ($trx->type === 'expense' ? 'var(--error)' : 'var(--text)') }};">
                                    {{ $trx->type === 'income' ? '+' : ($trx->type === 'expense' ? '-' : '⇄') }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    @if($trx->status === 'posted')
                                        <form method="POST" action="{{ route('transactions.void', $trx) }}" onsubmit="const r = prompt('Alasan pembatalan (void):'); if(!r) return false; this.void_reason.value = r;">
                                            @csrf
                                            <input type="hidden" name="void_reason" value="">
                                            <button type="submit" class="btn btn-secondary" style="min-height:36px; padding:0 12px; font-size:0.8rem;">Void</button>
                                        </form>
                                    @else
                                        <span style="font-size:0.8rem; color:var(--text-muted);">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px;">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
