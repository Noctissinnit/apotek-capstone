@extends('layouts.app')

@section('title', 'Barang Masuk')
@section('header', 'Barang Masuk')
@section('subheader', 'Faktur pembelian dari supplier')

@section('actions')
    @can('pembelian.kelola')
        <a href="{{ route('pembelian.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
            Catat Barang Masuk
        </a>
    @endcan
@endsection

@section('content')
    <div class="panel-card">
        <div class="panel-header">
            <h2 class="panel-title">Daftar Faktur</h2>
            <span class="badge-neutral">{{ $pembelian->total() }} faktur</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Daftar faktur barang masuk</caption>
                <thead class="table-head">
                    <tr>
                        <th scope="col" class="table-th">No Faktur</th>
                        <th scope="col" class="table-th">Tanggal</th>
                        <th scope="col" class="table-th">Supplier</th>
                        <th scope="col" class="table-th text-right">Jenis Obat</th>
                        <th scope="col" class="table-th text-right">Total</th>
                        <th scope="col" class="table-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pembelian as $faktur)
                        <tr class="transition hover:bg-slate-50">
                            <th scope="row" class="table-td font-medium text-slate-900">{{ $faktur->no_faktur }}</th>
                            <td class="table-td">{{ $faktur->tanggal_pembelian->translatedFormat('d M Y') }}</td>
                            <td class="table-td">{{ $faktur->supplier?->nama_supplier ?? '-' }}</td>
                            <td class="table-td text-right">{{ $faktur->detail_count }}</td>
                            <td class="table-td text-right font-semibold text-slate-900">Rp {{ number_format($faktur->total, 0, ',', '.') }}</td>
                            <td class="table-td text-right">
                                <a href="{{ route('pembelian.show', $faktur) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    Detail<span class="sr-only"> faktur {{ $faktur->no_faktur }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4l-2 3h-4l-2-3H4"/></svg>
                                    <p>Belum ada faktur barang masuk.</p>
                                    @can('pembelian.kelola')
                                        <a href="{{ route('pembelian.create') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Catat yang pertama</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pembelian->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">{{ $pembelian->links() }}</div>
        @endif
    </div>
@endsection
