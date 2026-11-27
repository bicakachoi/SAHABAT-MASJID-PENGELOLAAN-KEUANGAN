@extends('layouts.app')

@section('title', 'Neraca Interaktif — Masjid Pintar')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">
    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-end; gap:16px;">
        <div>
            <h1 style="font-size:2rem;">Neraca (Laporan Posisi Keuangan)</h1>
            <p style="color:var(--text-muted); margin:4px 0 0 0;">Persamaan nirlaba: <strong>Aset = Liabilitas + Aset Neto</strong> per {{ \Carbon\Carbon::parse($asOf)->format('d M Y') }}.</p>
        </div>

        <form method="GET" action="{{ route('balance-sheet') }}" style="display:flex; align-items:center; gap:10px;">
            <input type="date" name="as_of" value="{{ $asOf }}" class="input" style="width:auto;">
            <button type="submit" class="btn btn-secondary">Filter</button>
            <span class="badge {{ $balanceSheet['is_balanced'] ? 'badge-success' : 'badge-error' }}">
                {{ $balanceSheet['is_balanced'] ? '✓ Seimbang' : 'Tidak Seimbang' }}
            </span>
        </form>
    </div>

    <div class="grid grid-2">
        <!-- Kolom Kiri: Aset -->
        <section class="card">
            <h2 style="font-size:1.35rem; margin-bottom:14px;">Aset</h2>
            <div style="display:flex; flex-direction:column; gap:10px;">
                @foreach($accountRows->where('type', 'asset') as $row)
                    <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--outline);">
                        <span><code style="color:var(--text-muted);">{{ $row->code }}</code> {{ $row->name }}</span>
                        <strong class="tabular">Rp {{ number_format($row->balance, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
                <div style="display:flex; justify-content:space-between; padding-top:12px; font-size:1.1rem;">
                    <strong>Total Aset</strong>
                    <strong class="tabular" style="color:var(--primary);">Rp {{ number_format($balanceSheet['assets'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </section>

        <!-- Kolom Kanan: Liabilitas + Aset Neto -->
        <section class="card">
            <h2 style="font-size:1.35rem; margin-bottom:14px;">Liabilitas & Aset Neto</h2>
            <div style="display:flex; flex-direction:column; gap:10px;">
                @foreach($accountRows->whereIn('type', ['liability', 'net_asset']) as $row)
                    <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--outline);">
                        <span><code style="color:var(--text-muted);">{{ $row->code }}</code> {{ $row->name }}</span>
                        <strong class="tabular">Rp {{ number_format($row->balance, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
                <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--outline);">
                    <span>Surplus / (Defisit) Periode Berjalan</span>
                    <strong class="tabular">Rp {{ number_format($balanceSheet['current_surplus'], 0, ',', '.') }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; padding-top:12px; font-size:1.1rem;">
                    <strong>Total Liabilitas + Aset Neto</strong>
                    <strong class="tabular" style="color:var(--primary);">Rp {{ number_format($balanceSheet['liabilities'] + $balanceSheet['net_assets'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </section>
    </div>

    <section class="card">
        <h2 style="font-size:1.35rem; margin-bottom:6px;">Drill-Down Jurnal Otomatis</h2>
        <p style="color:var(--text-muted); margin-top:0; margin-bottom:16px;">10 entri jurnal terakhir yang membentuk saldo neraca di atas.</p>

        @if($recentJournals->isEmpty())
            <p style="color:var(--text-muted); margin:0;">Belum ada entri jurnal yang diposting.</p>
        @else
            <div style="display:flex; flex-direction:column; gap:12px;">
                @foreach($recentJournals as $entry)
                    <details style="border:1px solid var(--outline); border-radius:var(--radius-sm); padding:12px 16px; background:var(--bg);">
                        <summary style="cursor:pointer; display:flex; justify-content:space-between; font-weight:500;">
                            <span>{{ $entry->entry_date->format('d M Y') }} — {{ $entry->memo }}</span>
                            <span class="tabular" style="color:var(--text-muted);">{{ $entry->transaction->trx_number }}</span>
                        </summary>
                        <div class="table-wrap" style="margin-top:12px;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Akun COA</th>
                                        <th class="text-right">Debit</th>
                                        <th class="text-right">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entry->lines as $line)
                                        <tr>
                                            <td>{{ $line->account->code }} — {{ $line->account->name }}</td>
                                            <td class="text-right tabular">{{ $line->debit > 0 ? 'Rp '.number_format($line->debit, 0, ',', '.') : '-' }}</td>
                                            <td class="text-right tabular">{{ $line->credit > 0 ? 'Rp '.number_format($line->credit, 0, ',', '.') : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
