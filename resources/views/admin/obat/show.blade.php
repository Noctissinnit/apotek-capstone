@extends('layouts.app')
@section('title', 'Detail Obat')
@section('header', $obat->nama_obat)
@section('subheader', 'Detail informasi obat')
@section('actions')
@can('obat.kelola')<a href="{{ route('obat.edit', $obat) }}" class="btn-primary">Edit</a>@endcan
@endsection
@section('content')
<div class="grid gap-4 rounded-md border border-slate-200 bg-white p-5 sm:grid-cols-2 sm:p-6">
    @if ($obat->urlGambar())
        <div class="sm:col-span-2">
            <img src="{{ $obat->urlGambar() }}" alt="Gambar {{ $obat->nama_obat }}" class="h-48 w-48 rounded-md border border-slate-200 object-cover">
        </div>
    @endif

    @foreach ([['Kode Obat', $obat->kode_obat], ['Apotek', $obat->apotek ?: '-'], ['Kategori', $obat->kategoriRelasi?->nama_kategori ?: ($obat->kategori ?: '-')], ['Satuan', $obat->satuan], ['Isi per Box', $obat->isi_per_box.' '.$obat->satuan], ['Harga Beli', 'Rp '.number_format($obat->harga_beli, 0, ',', '.')], ['Harga Jual', 'Rp '.number_format($obat->harga_jual, 0, ',', '.')], ['Stok', $obat->stok.' '.$obat->satuan], ['Stok Minimum', $obat->stok_minimum.' '.$obat->satuan], ['Kadaluarsa', $obat->tanggal_kadaluarsa?->translatedFormat('d M Y') ?: '-']] as [$label, $value])
    <div>
        <dt class="text-sm text-slate-500">{{ $label }}</dt>
        <dd class="mt-1 font-medium text-slate-900">{{ $value }}</dd>
    </div>
    @endforeach
    <div class="sm:col-span-2">
        <dt class="text-sm text-slate-500">Keterangan</dt>
        <dd class="mt-1 text-slate-900">{{ $obat->keterangan ?: '-' }}</dd>
    </div>
</div>
@endsection