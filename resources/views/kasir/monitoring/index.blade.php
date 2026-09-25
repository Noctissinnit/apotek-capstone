@extends('layouts.app')

@section('title', 'Monitoring Stok')
@section('header', 'Monitoring Stok')
@section('subheader', 'Pantau obat yang stoknya menipis dan yang mendekati kadaluarsa')

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="stat-card">
            <p class="stat-label">Jenis Obat</p>
            <p class="stat-value text-slate-900">{{ number_format($totalObat) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Stok Menipis</p>
            <p class="stat-value {{ $obatMenipis->count() ? 'text-amber-600' : 'text-slate-900' }}">{{ $obatMenipis->count() }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Kadaluarsa &le; 3 Bulan</p>
            <p class="stat-value {{ $obatHampirKadaluarsa->count() ? 'text-red-600' : 'text-slate-900' }}">{{ $obatHampirKadaluarsa->count() }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="panel-card">
            <div class="panel-header">
                <h2 class="panel-title">Stok Menipis</h2>
                <span class="badge-neutral">{{ $obatMenipis->count() }} obat</span>
            </div>

            <ul class="divide-y divide-slate-100" role="list">
                @forelse ($obatMenipis as $obat)
                    @php
                        // Batas minimum dipakai sebagai 100% agar panjang bar mudah dibaca
                        $persen = $obat->stok_minimum > 0
                            ? min(100, (int) round($obat->stok / $obat->stok_minimum * 100))
                            : ($obat->stok > 0 ? 100 : 0);
                        $habis = $obat->stok < 1;
                    @endphp
                    <li class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900">{{ $obat->nama_obat }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $obat->kode_obat }} &middot; {{ $obat->kategori }}</p>
                            </div>
                            <span class="{{ $habis ? 'badge-danger' : 'badge-warning' }} shrink-0">
                                {{ $obat->stok }} / {{ $obat->stok_minimum }} {{ $obat->satuan }}
                            </span>
                        </div>

                        <div
                            class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"
                            role="progressbar"
                            aria-valuenow="{{ $obat->stok }}"
                            aria-valuemin="0"
                            aria-valuemax="{{ max($obat->stok_minimum, 1) }}"
                            aria-label="Stok {{ $obat->nama_obat }} terhadap batas minimum"
                        >
                            <div class="h-full rounded-full {{ $habis ? 'bg-red-500' : 'bg-amber-500' }}" style="width: {{ $persen }}%"></div>
                        </div>
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
                <h2 class="panel-title">Mendekati Kadaluarsa</h2>
                <span class="badge-neutral">{{ $obatHampirKadaluarsa->count() }} obat</span>
            </div>

            <ul class="divide-y divide-slate-100" role="list">
                @forelse ($obatHampirKadaluarsa as $obat)
                    @php
                        $sisaHari = (int) now()->startOfDay()->diffInDays($obat->tanggal_kadaluarsa, false);
                    @endphp
                    <li class="flex items-start justify-between gap-3 px-5 py-4">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-900">{{ $obat->nama_obat }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $obat->tanggal_kadaluarsa->translatedFormat('d F Y') }} &middot; stok {{ $obat->stok }} {{ $obat->satuan }}
                            </p>
                        </div>
                        <span class="{{ $sisaHari < 0 ? 'badge-danger' : 'badge-warning' }} shrink-0">
                            {{ $sisaHari < 0 ? 'Lewat '.abs($sisaHari).' hari' : 'Sisa '.$sisaHari.' hari' }}
                        </span>
                    </li>
                @empty
                    <li class="empty-state">
                        <svg class="h-8 w-8 text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p>Tidak ada obat yang mendekati kadaluarsa.</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
