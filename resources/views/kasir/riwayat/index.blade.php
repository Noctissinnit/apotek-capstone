@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('header', 'Riwayat Transaksi')
@section('subheader', 'Daftar transaksi yang sudah diproses')

@section('actions')
    <span class="badge-warning">Tampilan contoh &middot; belum dari database</span>
@endsection

@section('content')
    @php
        $totalHari = collect($riwayat)->sum('total');
    @endphp

    <div class="panel-card">
        <div class="panel-header">
            <div>
                <h2 class="panel-title">Hari Ini</h2>
                <p class="mt-0.5 text-sm text-slate-500">{{ count($riwayat) }} transaksi</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500">Total penjualan</p>
                <p class="text-lg font-bold text-slate-900">Rp {{ number_format($totalHari, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Tabel untuk layar sedang ke atas --}}
        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Daftar transaksi penjualan hari ini</caption>
                <thead class="table-head">
                    <tr>
                        <th scope="col" class="table-th">No Faktur</th>
                        <th scope="col" class="table-th">Waktu</th>
                        <th scope="col" class="table-th">Kasir</th>
                        <th scope="col" class="table-th text-right">Item</th>
                        <th scope="col" class="table-th text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($riwayat as $item)
                        <tr class="transition hover:bg-slate-50">
                            <th scope="row" class="table-td font-medium text-slate-900">{{ $item['no_faktur'] }}</th>
                            <td class="table-td">{{ $item['waktu'] }}</td>
                            <td class="table-td">{{ $item['kasir'] }}</td>
                            <td class="table-td text-right">{{ $item['item'] }}</td>
                            <td class="table-td text-right font-semibold text-slate-900">
                                Rp {{ number_format($item['total'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <svg class="h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p>Belum ada transaksi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Kartu untuk layar kecil, supaya tidak perlu geser ke samping --}}
        <ul class="divide-y divide-slate-100 sm:hidden" role="list">
            @forelse ($riwayat as $item)
                <li class="px-5 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-slate-900">{{ $item['no_faktur'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $item['waktu'] }} &middot; {{ $item['kasir'] }} &middot; {{ $item['item'] }} item
                            </p>
                        </div>
                        <span class="shrink-0 font-semibold text-slate-900">
                            Rp {{ number_format($item['total'], 0, ',', '.') }}
                        </span>
                    </div>
                </li>
            @empty
                <li class="empty-state">Belum ada transaksi.</li>
            @endforelse
        </ul>
    </div>
@endsection
