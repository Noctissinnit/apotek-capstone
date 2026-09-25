@extends('layouts.app')

@section('title', 'Data Obat')
@section('header', 'Data Obat')
@section('subheader', 'Kelola persediaan dan informasi obat')

@section('actions')
@can('obat.kelola')
<a href="{{ route('obat.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">Tambah Obat</a>
@endcan
@endsection

@section('content')
<div class="rounded-xl border border-slate-200 bg-white">
    <div class="flex flex-col gap-3 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('obat.index') }}" class="flex w-full gap-2 sm:max-w-md">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau kategori..." class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cari</button>
        </form>
        <p class="text-sm text-slate-500">{{ $obat->total() }} jenis obat</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    @php
                    $sortLink = function (string $column) use ($sort, $direction) {
                    $nextDirection = $sort === $column && $direction === 'asc' ? 'desc' : 'asc';

                    return route('obat.index', array_merge(request()->query(), ['sort' => $column, 'direction' => $nextDirection, 'page' => null]));
                    };
                    $sortIndicator = function (string $column) use ($sort, $direction) {
                    return $sort === $column ? ($direction === 'asc' ? ' ↑' : ' ↓') : '';
                    };
                    @endphp
                    <th class="px-5 py-3"><a href="{{ $sortLink('kode') }}" class="hover:text-slate-900">Kode{{ $sortIndicator('kode') }}</a></th>
                    <th class="px-5 py-3"><a href="{{ $sortLink('nama') }}" class="hover:text-slate-900">Nama Obat{{ $sortIndicator('nama') }}</a></th>
                    <th class="px-5 py-3"><a href="{{ $sortLink('kategori') }}" class="hover:text-slate-900">Kategori{{ $sortIndicator('kategori') }}</a></th>
                    <th class="px-5 py-3"><a href="{{ $sortLink('satuan') }}" class="hover:text-slate-900">Satuan{{ $sortIndicator('satuan') }}</a></th>
                    <th class="px-5 py-3 text-right"><a href="{{ $sortLink('harga_jual') }}" class="hover:text-slate-900">Harga Jual{{ $sortIndicator('harga_jual') }}</a></th>
                    <th class="px-5 py-3 text-right"><a href="{{ $sortLink('stok') }}" class="hover:text-slate-900">Stok{{ $sortIndicator('stok') }}</a></th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($obat as $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-900">{{ $item->kode_obat }}</td>
                    <td class="px-5 py-3">{{ $item->nama_obat }}</td>
                    <td class="px-5 py-3">{{ $item->kategoriRelasi?->nama_kategori ?: ($item->kategori ?: '-') }}</td>
                    <td class="px-5 py-3">{{ $item->satuan }}</td>
                    <td class="px-5 py-3 text-right">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                    <td class="px-5 py-3 text-right"><span @class(['font-medium text-red-600'=> $item->stok <= $item->stok_minimum])>{{ number_format($item->stok) }} {{ $item->satuan }}</span></td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('obat.show', $item) }}" class="font-medium text-slate-600 hover:text-slate-900">Detail</a>
                            @can('obat.kelola')
                            <a href="{{ route('obat.edit', $item) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                            <form method="POST" action="{{ route('obat.destroy', $item) }}" onsubmit="return confirm('Hapus data obat ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-700">Hapus</button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada data obat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($obat->hasPages())
    <div class="border-t border-slate-200 px-5 py-4">{{ $obat->links() }}</div>
    @endif
</div>
@endsection