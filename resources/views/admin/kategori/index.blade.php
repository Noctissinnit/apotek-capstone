@extends('layouts.app')
@section('title', 'Data Kategori')
@section('header', 'Data Kategori')
@section('subheader', 'Kelola master kategori obat')
@section('actions')<a href="{{ route('kategori.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">Tambah Kategori</a>@endsection
@section('content')
<div class="rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
            <tr>
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">Nama Kategori</th>
                <th class="px-5 py-3">Jumlah Obat</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($kategori as $item)
            <tr>
                <td class="px-5 py-3 font-mono text-slate-500">{{ $item->id_kategori }}</td>
                <td class="px-5 py-3 font-medium text-slate-900">{{ $item->nama_kategori }}</td>
                <td class="px-5 py-3">{{ $item->obat_count }}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex justify-end gap-3"><a href="{{ route('kategori.edit', $item) }}" class="font-medium text-emerald-600">Edit</a>
                        <form method="POST" action="{{ route('kategori.destroy', $item) }}" onsubmit="return confirm('Hapus kategori ini?')">@csrf @method('DELETE')<button class="font-medium text-red-600">Hapus</button></form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada kategori.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection