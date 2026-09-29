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
    <div class="border-b border-slate-200 p-5">
        <form method="GET" action="{{ route('obat.index') }}" class="space-y-3">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau kategori..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <div class="flex flex-nowrap items-end gap-3 overflow-x-auto pb-1">
                <label class="block min-w-[220px] flex-1"><span class="mb-1 block text-xs font-medium text-slate-500">Urutkan</span><select name="sort_option" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="kode|asc" @selected($sort==='kode' && $direction==='asc' )>Kode Obat (naik)</option>
                        <option value="kode|desc" @selected($sort==='kode' && $direction==='desc' )>Kode Obat (turun)</option>
                        <option value="nama|asc" @selected($sort==='nama' && $direction==='asc' )>Nama / Abjad (A-Z)</option>
                        <option value="nama|desc" @selected($sort==='nama' && $direction==='desc' )>Nama / Abjad (Z-A)</option>
                        <option value="harga_jual|asc" @selected($sort==='harga_jual' && $direction==='asc' )>Harga Jual (terendah)</option>
                        <option value="harga_jual|desc" @selected($sort==='harga_jual' && $direction==='desc' )>Harga Jual (tertinggi)</option>
                        <option value="stok|asc" @selected($sort==='stok' && $direction==='asc' )>Stok (terendah)</option>
                        <option value="stok|desc" @selected($sort==='stok' && $direction==='desc' )>Stok (tertinggi)</option>
                    </select></label>
                <label class="block min-w-[220px] flex-1"><span class="mb-1 block text-xs font-medium text-slate-500">Kategori</span><select name="kategori_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Kategori</option>@foreach ($kategori as $item)<option value="{{ $item->id_kategori }}" @selected((string) request('kategori_id')===(string) $item->id_kategori)>{{ $item->nama_kategori }}</option>@endforeach
                    </select></label>
                <label class="block min-w-[220px] flex-1"><span class="mb-1 block text-xs font-medium text-slate-500">Satuan</span><select name="satuan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">Semua Satuan</option>@foreach ($satuan as $item)<option value="{{ $item }}" @selected(request('satuan')===$item)>{{ $item }}</option>@endforeach
                    </select></label>
                <button type="submit" class="min-w-[120px] shrink-0 rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Terapkan</button>
            </div>
        </form>
        <div class="mt-3 flex items-center justify-between gap-3">
            <p class="text-sm text-slate-500">{{ $obat->total() }} jenis obat</p><a href="{{ route('obat.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Reset</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr>
                    <th class="px-5 py-3">Kode</th>
                    <th class="px-5 py-3">Nama Obat</th>
                    <th class="px-5 py-3">Apotek</th>
                    <th class="px-5 py-3">Kategori</th>
                    <th class="px-5 py-3">Satuan</th>
                    <th class="px-5 py-3 text-right">Harga Jual</th>
                    <th class="px-5 py-3 text-right">Stok</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($obat as $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-medium text-slate-900">{{ $item->kode_obat }}</td>
                    <td class="px-5 py-3">{{ $item->nama_obat }}</td>
                    <td class="px-5 py-3">{{ $item->apotek ?: '-' }}</td>
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
                    <td colspan="8" class="px-5 py-8 text-center text-slate-500">Belum ada data obat.</td>
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