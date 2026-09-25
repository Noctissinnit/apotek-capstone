@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('header', 'Dashboard Kasir')
@section('subheader', 'Selamat bekerja, '.auth()->user()->name)

@section('actions')
    <a href="{{ route('kasir.transaksi') }}" class="btn-primary">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
        Transaksi Baru
    </a>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="stat-card">
            <p class="stat-label">Jenis Obat</p>
            <p class="stat-value text-slate-900">{{ number_format($totalObat) }}</p>
            <p class="mt-1 text-xs text-slate-500">Terdaftar di sistem</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Stok Menipis</p>
            <p class="stat-value {{ $obatMenipis->count() ? 'text-amber-600' : 'text-slate-900' }}">{{ $obatMenipis->count() }}</p>
            <p class="mt-1 text-xs text-slate-500">Stok di bawah batas minimum</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Kadaluarsa &le; 3 Bulan</p>
            <p class="stat-value {{ $obatHampirKadaluarsa->count() ? 'text-red-600' : 'text-slate-900' }}">{{ $obatHampirKadaluarsa->count() }}</p>
            <p class="mt-1 text-xs text-slate-500">Perlu diperiksa lebih dulu</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="panel-card">
            <div class="panel-header">
                <h2 class="panel-title">Perlu Perhatian</h2>
                <a href="{{ route('kasir.monitoring') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
                    Lihat semua
                </a>
            </div>

            <ul class="divide-y divide-slate-100" role="list">
                @forelse ($obatMenipis->take(5) as $obat)
                    <li class="flex items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $obat->nama_obat }}</p>
                            <p class="text-xs text-slate-500">Minimum {{ $obat->stok_minimum }} {{ $obat->satuan }}</p>
                        </div>
                        <span class="{{ $obat->stok < 1 ? 'badge-danger' : 'badge-warning' }} shrink-0">
                            Sisa {{ $obat->stok }}
                        </span>
                    </li>
                @empty
                    <li class="empty-state">
                        <svg class="h-8 w-8 text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p>Semua stok aman.</p>
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Transaksi Terakhir</h2>
                    <p class="mt-0.5 text-xs text-amber-700">Data contoh, belum dari database</p>
                </div>
                <a href="{{ route('kasir.riwayat') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
                    Lihat semua
                </a>
            </div>

            <ul class="divide-y divide-slate-100" role="list">
                @forelse ($riwayatTerakhir as $item)
                    <li class="flex items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $item['no_faktur'] }}</p>
                            <p class="text-xs text-slate-500">{{ $item['waktu'] }} &middot; {{ $item['item'] }} item</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-slate-900">
                            Rp {{ number_format($item['total'], 0, ',', '.') }}
                        </span>
                    </li>
                @empty
                    <li class="empty-state">
                        <p>Belum ada transaksi hari ini.</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
