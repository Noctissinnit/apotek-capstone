@extends('layouts.app')

@section('title', 'Monitoring Stok')
@section('header', 'Monitoring Stok')
@section('subheader', 'Pantau obat yang stoknya mulai menipis')

@section('content')
    <div class="panel-card">
        <div class="panel-header">
            <h2 class="panel-title">Stok Menipis</h2>
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
@endsection
