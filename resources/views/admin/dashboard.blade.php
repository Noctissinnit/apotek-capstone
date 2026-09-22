@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('header', 'Dashboard Admin')
@section('subheader', 'Ringkasan data apotek')

@section('content')
    @php
        $stats = [
            ['label' => 'Jenis Obat', 'value' => number_format($totalObat)],
            ['label' => 'Stok Menipis', 'value' => number_format($stokMenipis)],
            ['label' => 'Supplier', 'value' => number_format($totalSupplier)],
            ['label' => 'Pembelian Bulan Ini', 'value' => 'Rp '.number_format($totalPembelianBulanIni, 0, ',', '.')],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">{{ $stat['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Pembelian Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3">No Faktur</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Supplier</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pembelianTerbaru as $pembelian)
                        <tr>
                            <td class="px-5 py-3 font-medium">{{ $pembelian->no_faktur }}</td>
                            <td class="px-5 py-3">{{ $pembelian->tanggal_pembelian->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3">{{ $pembelian->supplier->nama_supplier }}</td>
                            <td class="px-5 py-3 text-right">Rp {{ number_format($pembelian->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-center text-slate-500">Belum ada data pembelian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
