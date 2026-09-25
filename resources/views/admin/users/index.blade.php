@extends('layouts.app')

@section('title', 'Manajemen User')
@section('header', 'Manajemen User')
@section('subheader', 'Kelola akun dan hak akses pengguna apotek')

@section('actions')
    <a href="{{ route('admin.user.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:ring-4 focus-visible:ring-emerald-500/20 focus-visible:outline-none">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
        Tambah User
    </a>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total User</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($totalUser) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Admin</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-emerald-600">{{ number_format($totalAdmin) }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Kasir</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-blue-600">{{ number_format($totalKasir) }}</p>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/80 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">Daftar User</h2>
            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                {{ $users->total() }} akun
            </span>
        </div>

        {{-- Tabel untuk layar sedang ke atas --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left text-sm">
                <caption class="sr-only">Daftar akun pengguna beserta role dan aksinya</caption>
                <thead class="bg-slate-50 text-xs tracking-wide text-slate-500 uppercase">
                    <tr>
                        <th scope="col" class="px-5 py-3 font-semibold">Nama</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Kontak</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Alamat</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Role</th>
                        <th scope="col" class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        @php
                            $isSelf = $user->id === auth()->id();
                            $adminTerakhir = $user->hasRole('admin') && $totalAdmin <= 1;
                            $alasanKunci = match (true) {
                                $isSelf => 'Akun yang sedang Anda gunakan tidak dapat dihapus.',
                                $adminTerakhir => 'Admin terakhir tidak dapat dihapus.',
                                default => null,
                            };
                        @endphp
                        <tr class="transition hover:bg-slate-50">
                            <th scope="row" class="px-5 py-3 text-left font-normal">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700" aria-hidden="true">
                                        {{ str($user->name)->substr(0, 1)->upper() }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="flex items-center gap-2 font-medium text-slate-900">
                                            <span class="truncate">{{ $user->name }}</span>
                                            @if ($isSelf)
                                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Anda</span>
                                            @endif
                                        </p>
                                        <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </th>
                            <td class="px-5 py-3 text-slate-700">{{ $user->kontak ?: '—' }}</td>
                            <td class="px-5 py-3 text-slate-700">
                                <span class="line-clamp-2">{{ $user->alamat ?: '—' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @forelse ($user->getRoleNames() as $role)
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $role === 'admin' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ ucfirst($role) }}
                                    </span>
                                @empty
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Tanpa role</span>
                                @endforelse
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.user.edit', $user) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700 focus-visible:ring-4 focus-visible:ring-emerald-500/15 focus-visible:outline-none">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit</span>
                                        <span class="sr-only">user {{ $user->name }}</span>
                                    </a>

                                    @if ($alasanKunci)
                                        <button type="button" disabled title="{{ $alasanKunci }}" class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-400">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            Hapus
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('admin.user.destroy', $user) }}" onsubmit="return confirm('Hapus user {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 focus-visible:ring-4 focus-visible:ring-red-500/15 focus-visible:outline-none">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Hapus</span>
                                                <span class="sr-only">user {{ $user->name }}</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="flex flex-col items-center gap-3 px-5 py-12 text-center">
                                    <svg class="h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <p class="text-sm text-slate-500">Belum ada data user.</p>
                                    <a href="{{ route('admin.user.create') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Tambah user pertama</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Kartu untuk layar kecil --}}
        <ul class="divide-y divide-slate-100 md:hidden" role="list">
            @forelse ($users as $user)
                @php
                    $isSelf = $user->id === auth()->id();
                    $adminTerakhir = $user->hasRole('admin') && $totalAdmin <= 1;
                    $alasanKunci = match (true) {
                        $isSelf => 'Akun yang sedang Anda gunakan tidak dapat dihapus.',
                        $adminTerakhir => 'Admin terakhir tidak dapat dihapus.',
                        default => null,
                    };
                @endphp
                <li class="px-5 py-4">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700" aria-hidden="true">
                            {{ str($user->name)->substr(0, 1)->upper() }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="flex flex-wrap items-center gap-2 font-medium text-slate-900">
                                <span class="truncate">{{ $user->name }}</span>
                                @if ($isSelf)
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">Anda</span>
                                @endif
                            </p>
                            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>

                            <dl class="mt-2 space-y-1 text-xs text-slate-600">
                                <div class="flex gap-2">
                                    <dt class="w-16 shrink-0 text-slate-400">Kontak</dt>
                                    <dd>{{ $user->kontak ?: '—' }}</dd>
                                </div>
                                <div class="flex gap-2">
                                    <dt class="w-16 shrink-0 text-slate-400">Alamat</dt>
                                    <dd>{{ $user->alamat ?: '—' }}</dd>
                                </div>
                            </dl>

                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                @forelse ($user->getRoleNames() as $role)
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $role === 'admin' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ ucfirst($role) }}
                                    </span>
                                @empty
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Tanpa role</span>
                                @endforelse

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.user.edit', $user) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-emerald-300 hover:text-emerald-700">
                                        Edit<span class="sr-only"> user {{ $user->name }}</span>
                                    </a>
                                    @if ($alasanKunci)
                                        <button type="button" disabled title="{{ $alasanKunci }}" class="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-400">Hapus</button>
                                    @else
                                        <form method="POST" action="{{ route('admin.user.destroy', $user) }}" onsubmit="return confirm('Hapus user {{ $user->name }}? Tindakan ini tidak dapat dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                                Hapus<span class="sr-only"> user {{ $user->name }}</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="flex flex-col items-center gap-3 px-5 py-12 text-center">
                    <p class="text-sm text-slate-500">Belum ada data user.</p>
                    <a href="{{ route('admin.user.create') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Tambah user pertama</a>
                </li>
            @endforelse
        </ul>

        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
