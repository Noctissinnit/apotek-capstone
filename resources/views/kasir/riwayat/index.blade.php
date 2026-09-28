@extends('layouts.app')

@section('title', 'Riwayat Penjualan')
@section('header', 'Riwayat Penjualan')
@section('subheader', 'Daftar transaksi dan laporan penjualan apotek')

@section('content')
    <div class="panel-card">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Semua Transaksi</h2>
                <p class="mt-0.5 text-sm text-slate-500">{{ $riwayat->count() }} transaksi tersimpan</p>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-3">
                <div class="text-right" data-testid="total-penjualan-hari-ini">
                    <p class="text-xs text-slate-500">Total penjualan hari ini &middot; {{ $jumlahHariIni }} transaksi</p>
                    <p class="text-lg font-bold text-slate-900">Rp {{ number_format($totalHariIni, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Daftar seluruh transaksi penjualan</caption>
                <thead class="table-head">
                    <tr>
                        <th scope="col" class="table-th">No Faktur</th>
                        <th scope="col" class="table-th">Tanggal &amp; Waktu</th>
                        <th scope="col" class="table-th">Kasir</th>
                        <th scope="col" class="table-th text-right">Item</th>
                        <th scope="col" class="table-th text-right">Total</th>
                        <th scope="col" class="table-th text-right">Laporan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($riwayat as $item)
                        <tr class="transition hover:bg-slate-50">
                            <th scope="row" class="table-td font-medium text-slate-900">{{ $item->no_transaksi }}</th>
                            <td class="table-td">{{ $item->tanggal_penjualan->format('d/m/Y H:i') }}</td>
                            <td class="table-td">{{ $item->user->name }}</td>
                            <td class="table-td text-right">{{ $item->detail->sum('jumlah') }}</td>
                            <td class="table-td text-right font-semibold text-slate-900">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            <td class="table-td text-right">
                                <a href="{{ route('kasir.riwayat.pdf', $item) }}" class="btn-ghost whitespace-nowrap px-3 py-1.5 text-xs">
                                    Unduh PDF
                                    <span class="sr-only"> transaksi {{ $item->no_transaksi }}</span>
                                </a>
                            </td>
                        </tr>
                        <tr class="bg-slate-50">
                            <td colspan="6" class="px-5 py-3 text-xs text-slate-600">
                                <span class="font-semibold text-slate-700">Rincian:</span>
                                {{ $item->detail->map(fn ($detail) => $detail->obat->nama_obat.' ('.$detail->jumlah.' '.$detail->obat->satuan.')')->join(', ') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p>Belum ada transaksi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <ul class="divide-y divide-slate-100 sm:hidden" role="list">
            @forelse ($riwayat as $item)
                <li class="px-5 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-900">{{ $item->no_transaksi }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $item->tanggal_penjualan->format('d/m/Y H:i') }} &middot; {{ $item->user->name }} &middot; {{ $item->detail->sum('jumlah') }} item
                            </p>
                            <p class="mt-1 text-xs text-slate-500">{{ $item->detail->map(fn ($detail) => $detail->obat->nama_obat.' ('.$detail->jumlah.')')->join(', ') }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="font-semibold text-slate-900">Rp {{ number_format($item->total, 0, ',', '.') }}</p>
                            <a href="{{ route('kasir.riwayat.pdf', $item) }}" class="mt-2 inline-block text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                                Unduh PDF <span class="sr-only">transaksi {{ $item->no_transaksi }}</span>
                            </a>
                        </div>
                    </div>
                </li>
            @empty
                <li class="empty-state">Belum ada transaksi.</li>
            @endforelse
        </ul>
    </div>
@endsection
