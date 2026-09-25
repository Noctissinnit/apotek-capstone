@extends('layouts.app')

@section('title', 'Transaksi Penjualan')
@section('header', 'Transaksi Penjualan')
@section('subheader', 'Tambah obat ke keranjang dan proses pembelian')

@section('content')
    <div class="panel-card">
        <div class="panel-header">
            <h2 class="panel-title">Cari Obat</h2>
            <button type="button" class="soft-button bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                Baru
            </button>
        </div>

        <div class="p-5">
            <input type="text" placeholder="Cari nama obat..." class="soft-input">

            <div class="mt-4 space-y-3">
                @foreach ($obat as $item)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-3 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                        <div>
                            <p class="font-medium text-slate-800">{{ $item->nama_obat }}</p>
                            <p class="text-xs text-slate-500">Stok: {{ $item->stok }}</p>
                        </div>
                        <button type="button" class="soft-button bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-emerald-100 hover:text-emerald-700">
                            + Tambah
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
