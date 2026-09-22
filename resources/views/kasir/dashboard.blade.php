@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('header', 'Dashboard Kasir')
@section('subheader', 'Selamat bekerja, '.auth()->user()->name)

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Jenis Obat</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($totalObat) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Stok Menipis</p>
            <p class="mt-2 text-2xl font-semibold text-amber-600">{{ $obatMenipis->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Kadaluarsa &le; 3 Bulan</p>
            <p class="mt-2 text-2xl font-semibold text-red-600">{{ $obatHampirKadaluarsa->count() }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Stok Menipis</h2>
            </div>
            <ul class="divide-y divide-slate-100 text-sm">
                @forelse ($obatMenipis as $obat)
                    <li class="flex items-center justify-between px-5 py-3">
                        <span>{{ $obat->nama_obat }}</span>
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                            {{ $obat->stok }} / min {{ $obat->stok_minimum }} {{ $obat->satuan }}
                        </span>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-slate-500">Semua stok aman.</li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">Mendekati Kadaluarsa</h2>
            </div>
            <ul class="divide-y divide-slate-100 text-sm">
                @forelse ($obatHampirKadaluarsa as $obat)
                    <li class="flex items-center justify-between px-5 py-3">
                        <span>{{ $obat->nama_obat }}</span>
                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                            {{ $obat->tanggal_kadaluarsa->translatedFormat('d M Y') }}
                        </span>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-slate-500">Tidak ada obat yang mendekati kadaluarsa.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
