@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('header', 'Riwayat Transaksi')
@section('subheader', 'Daftar transaksi yang sudah diproses')

@section('content')
    <div class="panel-card">
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
                    @foreach ($riwayat as $item)
                        <tr>
                            <td class="px-5 py-3 text-slate-700">{{ $item['waktu'] }}</td>
                            <td class="px-5 py-3 text-slate-700">{{ $item['kasir'] }}</td>
                            <td class="px-5 py-3 font-medium text-slate-900">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
