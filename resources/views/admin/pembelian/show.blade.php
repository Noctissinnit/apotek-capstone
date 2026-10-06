@extends('layouts.app')

@section('title', 'Detail Faktur')
@section('header', $pembelian->no_faktur)
@section('subheader', 'Rincian barang masuk')

@section('actions')
    <a href="{{ route('pembelian.index') }}" class="btn-ghost">Kembali</a>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-4">
        @foreach ([
            ['Supplier', $pembelian->supplier?->nama_supplier ?? '-'],
            ['Tanggal', $pembelian->tanggal_pembelian->translatedFormat('d F Y')],
            ['Dicatat oleh', $pembelian->user?->name ?? '-'],
            ['Total', 'Rp '.number_format($pembelian->total, 0, ',', '.')],
        ] as [$label, $nilai])
            <div class="stat-card">
                <p class="stat-label">{{ $label }}</p>
                <p class="mt-2 font-semibold text-slate-900">{{ $nilai }}</p>
            </div>
        @endforeach
    </div>

    @if ($pembelian->keterangan)
        <p class="mt-4 rounded-md border border-slate-200 bg-white px-5 py-3 text-sm text-slate-600">{{ $pembelian->keterangan }}</p>
    @endif

    <div class="mt-6 panel-card">
        <div class="panel-header">
            <h2 class="panel-title">Obat yang Masuk</h2>
            <span class="badge-neutral">{{ $pembelian->detail->count() }} baris</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Rincian obat pada faktur {{ $pembelian->no_faktur }}</caption>
                <thead class="table-head">
                    <tr>
                        <th scope="col" class="table-th">Obat</th>
                        <th scope="col" class="table-th">No Batch</th>
                        <th scope="col" class="table-th">Kadaluarsa</th>
                        <th scope="col" class="table-th">Jumlah</th>
                        <th scope="col" class="table-th text-right">Harga</th>
                        <th scope="col" class="table-th text-right">Diskon</th>
                        <th scope="col" class="table-th text-right">Modal/Satuan</th>
                        <th scope="col" class="table-th text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($pembelian->detail as $baris)
                        <tr>
                            <th scope="row" class="table-td text-left font-medium text-slate-900">{{ $baris->obat?->nama_obat ?? '-' }}</th>
                            <td class="table-td">{{ $baris->no_batch }}</td>
                            <td class="table-td">{{ $baris->tanggal_kadaluarsa?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="table-td">{{ $baris->keteranganJumlah() }}</td>
                            <td class="table-td text-right">Rp {{ number_format($baris->harga_beli, 0, ',', '.') }}</td>
                            <td class="table-td text-right">{{ $baris->diskon_persen ? rtrim(rtrim(number_format($baris->diskon_persen, 2), '0'), ',.').'%' : '-' }}</td>
                            <td class="table-td text-right">Rp {{ number_format($baris->modal_per_satuan, 0, ',', '.') }}</td>
                            <td class="table-td text-right font-semibold text-slate-900">Rp {{ number_format($baris->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50">
                        <td colspan="7" class="px-5 py-3 text-right font-medium text-slate-700">Total</td>
                        <td class="px-5 py-3 text-right text-base font-bold text-slate-900">Rp {{ number_format($pembelian->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
