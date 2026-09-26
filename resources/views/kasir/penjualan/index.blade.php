@extends('layouts.app')

@section('title', 'Riwayat Penjualan')
@section('header', 'Riwayat Penjualan')
@section('subheader', 'Pantau transaksi penjualan dan unduh laporan sesuai periode.')

@section('actions')
    <a href="{{ route('kasir.penjualan.pdf', request()->query()) }}"
       class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l4-4m-4 4l-4-4M5 17v3h14v-3"/>
        </svg>
        Export PDF
    </a>
@endsection

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Jumlah Transaksi</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($totalTransaksi) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Total Penjualan</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-700">Rp {{ number_format((float) $totalPenjualan, 0, ',', '.') }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('kasir.penjualan.index') }}" class="mb-5 rounded-xl border border-slate-200 bg-white p-4">
        <div class="grid gap-3 md:grid-cols-[minmax(12rem,2fr)_1fr_1fr_auto_auto] md:items-end">
            <label class="block text-sm font-medium text-slate-700">
                Cari transaksi / pelanggan / kasir
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100"
                       placeholder="Contoh: PJ-202609 atau Dewi"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </label>
            <label class="block text-sm font-medium text-slate-700">
                Dari tanggal
                <input type="date" name="tanggal_mulai" value="{{ $filters['tanggal_mulai'] ?? '' }}"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </label>
            <label class="block text-sm font-medium text-slate-700">
                Sampai tanggal
                <input type="date" name="tanggal_akhir" value="{{ $filters['tanggal_akhir'] ?? '' }}"
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </label>
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Filter</button>
            <a href="{{ route('kasir.penjualan.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
        </div>
        @if ($errors->any())
            <p class="mt-3 text-sm text-red-600">{{ $errors->first() }}</p>
        @endif
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">No. Transaksi</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Pelanggan</th>
                        <th class="px-5 py-3">Item</th>
                        <th class="px-5 py-3">Kasir</th>
                        <th class="px-5 py-3">Pembayaran</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($penjualan as $transaksi)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-5 py-4 font-medium text-slate-900">{{ $transaksi->no_transaksi }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $transaksi->tanggal_penjualan->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $transaksi->nama_pelanggan ?: 'Pelanggan Umum' }}</td>
                            <td class="min-w-48 px-5 py-4 text-slate-600">
                                @foreach ($transaksi->detail as $item)
                                    <div>{{ $item->obat->nama_obat }} <span class="text-slate-400">× {{ $item->jumlah }}</span></div>
                                @endforeach
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $transaksi->user->name }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $transaksi->metode_pembayaran }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right font-semibold text-slate-900">Rp {{ number_format((float) $transaksi->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">Belum ada transaksi penjualan pada filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($penjualan->hasPages())
            <div class="border-t border-slate-200 px-5 py-4">{{ $penjualan->links() }}</div>
        @endif
    </div>
@endsection
