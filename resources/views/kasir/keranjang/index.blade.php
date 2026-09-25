@extends('layouts.app')

@section('title', 'Keranjang')
@section('header', 'Keranjang')
@section('subheader', 'Review pembelian sebelum dibayar')

@section('content')
    <div class="panel-card">
        <div class="panel-header">
            <h2 class="panel-title">Daftar Item</h2>
        </div>

        <div class="p-5">
            @php
                $subtotal = collect($items)->sum(fn($item) => $item['jumlah'] * $item['harga']);
            @endphp

            @foreach ($items as $item)
                <div class="mb-3 flex items-center justify-between rounded-xl bg-slate-50 p-3">
                    <div>
                        <p class="font-medium text-slate-800">{{ $item['nama'] }}</p>
                        <p class="text-xs text-slate-500">{{ $item['jumlah'] }} x Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                    </div>
                    <span class="font-medium text-slate-900">Rp {{ number_format($item['jumlah'] * $item['harga'], 0, ',', '.') }}</span>
                </div>
            @endforeach

            <div class="mt-5 space-y-2 border-t border-slate-200 pt-4 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-medium text-slate-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Diskon</span><span class="font-medium text-slate-800">Rp 0</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Total</span><span class="text-lg font-semibold text-slate-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
            </div>

            <button type="button" class="mt-5 w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                Proses Pembayaran
            </button>
        </div>
    </div>
@endsection
