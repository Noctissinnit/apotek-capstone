@extends('layouts.app')

@section('title', 'Retur & Pemusnahan')
@section('header', 'Retur & Pemusnahan')
@section('subheader', 'Stok keluar di luar penjualan')

@section('actions')
    <a href="{{ route('penyesuaian.create') }}" class="btn-primary">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
        Buat Dokumen
    </a>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="stat-card">
            <p class="stat-label">Batch Kadaluarsa</p>
            <p class="stat-value {{ $jumlahBatchKadaluarsa ? 'text-red-600' : 'text-slate-900' }}">{{ $jumlahBatchKadaluarsa }}</p>
            <p class="mt-1 text-xs text-slate-500">belum diretur atau dimusnahkan</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Nilai Retur Bulan Ini</p>
            <p class="stat-value text-slate-900">Rp {{ number_format($nilaiRetur, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">biasanya diganti supplier</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Nilai Pemusnahan Bulan Ini</p>
            <p class="stat-value {{ $nilaiMusnah > 0 ? 'text-red-600' : 'text-slate-900' }}">Rp {{ number_format($nilaiMusnah, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">kerugian penuh</p>
        </div>
    </div>

    <div class="mt-6 panel-card">
        <div class="panel-header">
            <h2 class="panel-title">Riwayat Dokumen</h2>
            <span class="badge-neutral">{{ $dokumen->total() }} dokumen</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Daftar dokumen retur dan pemusnahan</caption>
                <thead class="table-head">
                    <tr>
                        <th scope="col" class="table-th">No Dokumen</th>
                        <th scope="col" class="table-th">Jenis</th>
                        <th scope="col" class="table-th">Tanggal</th>
                        <th scope="col" class="table-th">Tujuan / Petugas</th>
                        <th scope="col" class="table-th text-right">Batch</th>
                        <th scope="col" class="table-th text-right">Nilai</th>
                        <th scope="col" class="table-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($dokumen as $item)
                        <tr class="transition hover:bg-slate-50">
                            <th scope="row" class="table-td font-medium text-slate-900">{{ $item->no_dokumen }}</th>
                            <td class="table-td">
                                <span class="{{ $item->isRetur() ? 'badge-warning' : 'badge-danger' }}">
                                    {{ $item->isRetur() ? 'Retur' : 'Pemusnahan' }}
                                </span>
                            </td>
                            <td class="table-td">{{ $item->tanggal->translatedFormat('d M Y') }}</td>
                            <td class="table-td">{{ $item->isRetur() ? ($item->supplier?->nama_supplier ?? '-') : ($item->user?->name ?? '-') }}</td>
                            <td class="table-td text-right">{{ $item->detail_count }}</td>
                            <td class="table-td text-right font-semibold text-slate-900">Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</td>
                            <td class="table-td text-right">
                                <a href="{{ route('penyesuaian.show', $item) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    Detail<span class="sr-only"> dokumen {{ $item->no_dokumen }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6m4-6v6M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                    <p>Belum ada dokumen retur atau pemusnahan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($dokumen->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">{{ $dokumen->links() }}</div>
        @endif
    </div>
@endsection
