@extends('layouts.app')

@section('title', 'Transaksi Penjualan')
@section('header', 'Transaksi Penjualan')
@section('subheader', 'Pilih obat di kiri, periksa keranjang di kanan, lalu proses pembayaran')

@section('actions')
    <span class="badge-warning">Tampilan contoh &middot; belum tersimpan</span>
@endsection

@section('content')
    @php
        $subtotal = collect($keranjang)->sum(fn ($item) => $item['jumlah'] * $item['harga']);
        $jumlahItem = collect($keranjang)->sum('jumlah');
    @endphp

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,0.9fr)]">
        {{-- Kolom kiri: pencarian dan daftar obat --}}
        <div class="panel-card">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Pilih Obat</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $obat->count() }} obat tersedia</p>
                </div>
            </div>

            <div class="panel-body">
                <label for="cari-obat" class="mb-1.5 block text-sm font-medium text-slate-700">Cari obat</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input
                        id="cari-obat"
                        type="search"
                        class="soft-input pl-11"
                        placeholder="Ketik nama atau kode obat..."
                        aria-describedby="cari-obat-bantuan"
                        disabled
                    >
                </div>
                <p id="cari-obat-bantuan" class="mt-1.5 text-xs text-slate-500">
                    Pencarian aktif setelah modul kasir dikerjakan (W6-02).
                </p>

                <ul class="mt-5 space-y-3" role="list">
                    @forelse ($obat as $item)
                        @php
                            $habis = $item->stok < 1;
                            $menipis = ! $habis && $item->stok <= $item->stok_minimum;
                        @endphp
                        <li class="list-row">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900">{{ $item->nama_obat }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $item->kode_obat }} &middot; {{ $item->satuan }}
                                </p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-900">
                                        Rp {{ number_format($item->harga_jual, 0, ',', '.') }}
                                    </span>
                                    @if ($habis)
                                        <span class="badge-danger">Stok habis</span>
                                    @elseif ($menipis)
                                        <span class="badge-warning">Sisa {{ $item->stok }} {{ $item->satuan }}</span>
                                    @else
                                        <span class="badge-neutral">Stok {{ $item->stok }}</span>
                                    @endif
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn-ghost shrink-0 px-3 py-1.5 text-xs"
                                aria-label="Tambah {{ $item->nama_obat }} ke keranjang"
                                disabled
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                                Tambah
                            </button>
                        </li>
                    @empty
                        <li class="empty-state">
                            <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4l-2 3h-4l-2-3H4"/></svg>
                            <p>Belum ada data obat.</p>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Kolom kanan: keranjang --}}
        <div class="panel-card xl:sticky xl:top-20">
            <div class="panel-header">
                <h2 class="panel-title">Keranjang</h2>
                <span class="badge-neutral">{{ $jumlahItem }} item</span>
            </div>

            <div class="panel-body">
                @forelse ($keranjang as $item)
                    <div class="mb-3 flex items-start justify-between gap-3 rounded-xl bg-slate-50 p-3">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-900">{{ $item['nama'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $item['jumlah'] }} {{ $item['satuan'] }} &times; Rp {{ number_format($item['harga'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span class="text-sm font-semibold text-slate-900">
                                Rp {{ number_format($item['jumlah'] * $item['harga'], 0, ',', '.') }}
                            </span>
                            <button
                                type="button"
                                class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-slate-400"
                                aria-label="Hapus {{ $item['nama'] }} dari keranjang"
                                disabled
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <p>Keranjang masih kosong.</p>
                        <p class="text-xs">Pilih obat di daftar sebelah kiri.</p>
                    </div>
                @endforelse

                <dl class="mt-5 space-y-2 border-t border-slate-200 pt-4 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Subtotal</dt>
                        <dd class="font-medium text-slate-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Diskon</dt>
                        <dd class="font-medium text-slate-800">Rp 0</dd>
                    </div>
                    <div class="flex items-baseline justify-between border-t border-slate-200 pt-3">
                        <dt class="font-medium text-slate-700">Total</dt>
                        <dd class="text-xl font-bold text-slate-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <button type="button" class="btn-primary mt-5 w-full py-2.5" aria-describedby="bayar-bantuan" disabled>
                    Proses Pembayaran
                </button>
                <p id="bayar-bantuan" class="mt-2 text-center text-xs text-slate-500">
                    Aktif setelah transaksi tersimpan ke database (W6-09, W6-10).
                </p>
            </div>
        </div>
    </div>
@endsection
