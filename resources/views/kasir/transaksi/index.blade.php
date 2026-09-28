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

            <div class="panel-body">
                <p class="mb-4 text-sm text-slate-500">Item terpilih akan tampil di keranjang.</p>
                @if ($jumlahKeranjang === 0)
                    <div class="empty-state">
                        <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <p>Keranjang masih kosong.</p>
                    </div>
                @else
                    <a href="{{ route('kasir.keranjang') }}" class="btn-primary w-full py-2.5">Lihat Keranjang</a>
                @endif
            </div>
        </div>
    </div>
@endsection
