@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('subheader', 'Ringkasan data apotek')

@section('content')
    @php
        $stats = [
            ['label' => 'Jenis Obat', 'value' => \App\Models\Obat::count()],
            ['label' => 'Stok Menipis', 'value' => \App\Models\Obat::stokMenipis()->count()],
            ['label' => 'Supplier', 'value' => \App\Models\Supplier::count()],
            ['label' => 'Transaksi Pembelian', 'value' => \App\Models\Pembelian::count()],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">{{ $stat['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ number_format($stat['value']) }}</p>
            </div>
        @endforeach
    </div>
@endsection
