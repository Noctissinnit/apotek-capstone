@extends('layouts.app')

@section('title', 'Dashboard Kasir')
@section('header', 'Dashboard Kasir')
@section('subheader', 'Selamat bekerja, '.auth()->user()->name)

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="stat-card">
            <p class="stat-label">Jenis Obat</p>
            <p class="stat-value text-emerald-600">{{ number_format($totalObat) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Stok Menipis</p>
            <p class="stat-value text-amber-600">{{ $obatMenipis->count() }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Kadaluarsa &le; 3 Bulan</p>
            <p class="stat-value text-red-600">{{ $obatHampirKadaluarsa->count() }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
        <div class="space-y-6">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Transaksi Penjualan</h2>
                    <button type="button" class="soft-button bg-emerald-600 text-white hover:bg-emerald-700">
                        Baru
                    </button>
                </div>

                <div class="p-5">
                    <label for="cari-obat" class="mb-2 block text-sm font-medium text-slate-700">Cari Obat</label>
                    <div class="relative">
                        <input id="cari-obat" type="text" placeholder="Cari nama obat..." class="soft-input">
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach (['Paracetamol 500 mg', 'Vitamin C 1000 mg', 'Amoxicillin 500 mg', 'Cetirizine 10 mg'] as $nama)
                            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-3 transition hover:border-emerald-200 hover:bg-emerald-50/40">
                                <div>
                                    <p class="font-medium text-slate-800">{{ $nama }}</p>
                                    <p class="text-xs text-slate-500">Stok: 12</p>
                                </div>
                                <button type="button" class="soft-button bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-emerald-100 hover:text-emerald-700">
                                    + Tambah
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Riwayat Transaksi</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-5 py-3 font-medium">Waktu</th>
                                <th class="px-5 py-3 font-medium">Kasir</th>
                                <th class="px-5 py-3 font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr>
                                <td class="px-5 py-3 text-slate-700">08:45</td>
                                <td class="px-5 py-3 text-slate-700">Kasir Apotek</td>
                                <td class="px-5 py-3 font-medium text-slate-900">Rp 28.000</td>
                            </tr>
                            <tr>
                                <td class="px-5 py-3 text-slate-700">09:15</td>
                                <td class="px-5 py-3 text-slate-700">Kasir Apotek</td>
                                <td class="px-5 py-3 font-medium text-slate-900">Rp 72.500</td>
                            </tr>
                            <tr>
                                <td class="px-5 py-3 text-slate-700">10:20</td>
                                <td class="px-5 py-3 text-slate-700">Kasir Apotek</td>
                                <td class="px-5 py-3 font-medium text-slate-900">Rp 43.000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Keranjang</h2>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                            <div>
                                <p class="font-medium text-slate-800">Paracetamol 500 mg</p>
                                <p class="text-xs text-slate-500">1 x Rp 5.000</p>
                            </div>
                            <span class="font-medium text-slate-900">Rp 5.000</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 p-3">
                            <div>
                                <p class="font-medium text-slate-800">Vitamin C 1000 mg</p>
                                <p class="text-xs text-slate-500">2 x Rp 30.000</p>
                            </div>
                            <span class="font-medium text-slate-900">Rp 60.000</span>
                        </div>
                    </div>

                    <div class="mt-5 space-y-2 border-t border-slate-200 pt-4 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-medium text-slate-800">Rp 65.000</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Diskon</span><span class="font-medium text-slate-800">Rp 0</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Total</span><span class="text-lg font-semibold text-slate-900">Rp 65.000</span></div>
                    </div>

                    <button type="button" class="mt-5 w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        Proses Pembayaran
                    </button>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-header">
                    <h2 class="panel-title">Monitoring Stok</h2>
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
        </div>
    </div>
@endsection
