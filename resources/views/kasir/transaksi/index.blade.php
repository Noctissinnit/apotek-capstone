@extends('layouts.app')

@section('title', 'Transaksi Penjualan')
@section('header', 'Transaksi Penjualan')
@section('subheader', 'Pilih obat di kiri, periksa keranjang di kanan, lalu proses pembayaran')

@section('content')
    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,0.9fr)]">
        <div class="panel-card">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Pilih Obat</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $obat->count() }} hasil ditemukan</p>
                </div>
            </div>

            <div class="panel-body">
                <form method="GET" action="{{ route('kasir.transaksi') }}" class="flex gap-2">
                    <label class="sr-only" for="cari-obat">Cari obat berdasarkan nama atau kode</label>
                    <input id="cari-obat" name="q" type="search" value="{{ $search }}" class="soft-input" placeholder="Cari nama atau kode obat...">
                    <button class="btn-ghost shrink-0" type="submit" aria-label="Cari obat">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>

                <ul class="mt-5 space-y-3" role="list">
                    @forelse ($obat as $item)
                        @php
                            $jumlahDiKeranjang = (int) ($jumlahPerObat[$item->id] ?? 0);
                            $sisaStok = max(0, $item->stok - $jumlahDiKeranjang);
                            $habis = $sisaStok < 1;
                            $menipis = ! $habis && $sisaStok <= $item->stok_minimum;
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
                                    @if ($jumlahDiKeranjang > 0)
                                        <span class="badge-neutral">Di keranjang {{ $jumlahDiKeranjang }}</span>
                                    @endif
                                    @if ($habis)
                                        <span class="badge-danger">Tersedia 0 {{ $item->satuan }}</span>
                                    @elseif ($menipis)
                                        <span class="badge-warning">Tersedia {{ $sisaStok }} {{ $item->satuan }}</span>
                                    @else
                                        <span class="badge-neutral">Tersedia {{ $sisaStok }} {{ $item->satuan }}</span>
                                    @endif
                                </div>
                            </div>

                            <form method="POST" action="{{ route('kasir.keranjang.add') }}">
                                @csrf
                                <input type="hidden" name="obat_id" value="{{ $item->id }}">
                                <button type="submit" class="btn-ghost shrink-0 px-3 py-1.5 text-xs" aria-label="Tambah {{ $item->nama_obat }} ke keranjang" @disabled($habis)>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                                    Tambah
                                </button>
                            </form>
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

        <div class="panel-card xl:sticky xl:top-20">
            <div class="panel-header">
                <h2 class="panel-title">Keranjang</h2>
                <span class="badge-neutral">{{ $jumlahKeranjang }} item</span>
            </div>

            @if ($items->isEmpty())
                <div class="panel-body">
                    <div class="empty-state">
                        <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <p>Keranjang masih kosong.</p>
                        <p class="text-xs">Tekan tombol Tambah pada daftar obat di sebelah kiri.</p>
                    </div>
                </div>
            @else
                {{-- Daftar obat yang sudah masuk keranjang --}}
                <ul class="max-h-96 divide-y divide-slate-100 overflow-y-auto" role="list">
                    @foreach ($items as $item)
                        <li class="flex items-start justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $item['obat']->nama_obat }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $item['jumlah'] }} {{ $item['obat']->satuan }} &times; Rp {{ number_format($item['harga_cents'] / 100, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1.5">
                                <span class="text-sm font-semibold text-slate-900">
                                    Rp {{ number_format($item['subtotal_cents'] / 100, 0, ',', '.') }}
                                </span>
                                <form method="POST" action="{{ route('kasir.keranjang.remove', $item['obat']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-red-50 hover:text-red-600 focus-visible:ring-2 focus-visible:ring-red-200 focus-visible:outline-none" aria-label="Hapus {{ $item['obat']->nama_obat }} dari keranjang" title="Hapus item">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="panel-body border-t border-slate-200">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Jenis obat</dt>
                            <dd class="font-medium text-slate-800">{{ $items->count() }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between border-t border-slate-200 pt-3">
                            <dt class="font-medium text-slate-700">Total</dt>
                            <dd class="text-xl font-bold text-slate-900">Rp {{ number_format($subtotal / 100, 0, ',', '.') }}</dd>
                        </div>
                    </dl>

                    <form method="POST" action="{{ route('kasir.checkout') }}">
                        @csrf
                        @include('kasir.partials.metode-pembayaran')

                        <button type="submit" class="btn-primary mt-5 w-full py-2.5">
                            Proses Pembayaran
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
                        </button>
                    </form>

                    <a href="{{ route('kasir.keranjang') }}" class="mt-3 block text-center text-sm font-medium text-emerald-700 hover:text-emerald-800">
                        Buka halaman keranjang
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
