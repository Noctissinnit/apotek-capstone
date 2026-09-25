@extends('layouts.app')

@section('title', 'Manajemen User')
@section('header', 'Manajemen User')
@section('subheader', 'Kelola akun dan hak akses pengguna apotek')

@section('actions')
    <a href="{{ route('user.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700">
        <span class="text-lg leading-none">+</span> Tambah User
    </a>
@endsection

@section('content')
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-5 py-3">ID User</th>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Alamat</th>
                        <th class="px-5 py-3">Kontak</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-900">#{{ $user->id }}</td>
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </td>
                            <td class="px-5 py-3">{{ $user->alamat ?: '-' }}</td>
                            <td class="px-5 py-3">{{ $user->kontak ?: '-' }}</td>
                            <td class="px-5 py-3">
                                @foreach ($user->getRoleNames() as $role)
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $role === 'admin' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">{{ ucfirst($role) }}</span>
                                @endforeach
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('user.edit', $user) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Edit</a>
                                    <form method="POST" action="{{ route('user.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada data user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
