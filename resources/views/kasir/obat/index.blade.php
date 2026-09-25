@extends('layouts.app')

@section('title', 'Data Obat')
@section('header', 'Data Obat')
@section('subheader', 'Daftar seluruh obat yang tersedia')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Stok Obat</h2>
                <p class="text-sm text-slate-500">{{ $obat->count() }} item terdaftar</p>
            </div>

            <form method="GET" action="{{ route('kasir.obat.index') }}" class="w-full md:max-w-sm">
                <label for="search" class="sr-only">Cari obat</label>
                <div class="relative">
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ old('search', $search) }}"
                        placeholder="Cari nama, kode, atau kategori..."
                        class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 pr-10 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-100"
                    >
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-emerald-600" aria-label="Cari">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nama Obat</th>
                        <th class="px-5 py-3 font-medium">Kategori</th>
                        <th class="px-5 py-3 font-medium">Satuan</th>
                        <th class="px-5 py-3 font-medium">Stok</th>
                        <th class="px-5 py-3 font-medium">Harga Jual</th>
                        <th class="px-5 py-3 font-medium">Kadaluarsa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($obat as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="font-medium text-slate-900">{{ $item->nama_obat }}</div>
                                <div class="text-xs text-slate-500">{{ $item->kode_obat }}</div>
                            </td>
                            <td class="px-5 py-3 text-slate-700">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                    {{ $item->kategori }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-700">{{ $item->satuan }}</td>
                            <td class="px-5 py-3">
                                <span class="font-semibold {{ $item->stok <= $item->stok_minimum ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ $item->stok }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-700">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-slate-700">
                                {{ $item->tanggal_kadaluarsa ? $item->tanggal_kadaluarsa->translatedFormat('d M Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                                @if ($search !== '')
                                    Tidak ada obat yang cocok dengan pencarian "{{ $search }}".
                                @else
                                    Belum ada data obat.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
