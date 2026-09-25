@php
    $roleTerpilih = old('role', $user->exists ? $user->getRoleNames()->first() : '');

    $daftarRole = [
        'admin' => [
            'label' => 'Admin',
            'deskripsi' => 'Kelola obat, supplier, pembelian, dan akun pengguna.',
        ],
        'kasir' => [
            'label' => 'Kasir',
            'deskripsi' => 'Melayani transaksi penjualan dan melihat data obat.',
        ],
    ];
@endphp

<form method="POST" action="{{ $action }}" class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <p class="font-semibold">Periksa kembali {{ $errors->count() }} isian berikut:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-end justify-between gap-2 border-b border-slate-100 pb-5">
        <div>
            <h2 class="text-base font-semibold text-slate-900">Informasi Akun</h2>
            <p class="mt-1 text-sm text-slate-500">Lengkapi data dasar dan akses login user.</p>
        </div>
        <p class="text-xs text-slate-500"><span class="text-red-500">*</span> wajib diisi</p>
    </div>

    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
            <input
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                autocomplete="name"
                placeholder="Masukkan nama lengkap"
                class="form-input @error('name') border-red-400 focus:border-red-500 focus:ring-red-500/10 @enderror"
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
            >
            @error('name')
                <p id="name-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="form-label">Email Login <span class="text-red-500">*</span></label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="email"
                placeholder="contoh@email.com"
                class="form-input @error('email') border-red-400 focus:border-red-500 focus:ring-red-500/10 @enderror"
                aria-describedby="email-bantuan @error('email') email-error @enderror"
            >
            @error('email')
                <p id="email-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p id="email-bantuan" class="mt-1.5 text-xs text-slate-500">Dipakai user untuk masuk ke sistem.</p>
        </div>

        <div>
            <label for="kontak" class="form-label">Nomor Kontak</label>
            <input
                id="kontak"
                name="kontak"
                value="{{ old('kontak', $user->kontak) }}"
                inputmode="tel"
                autocomplete="tel"
                placeholder="08xxxxxxxxxx"
                class="form-input @error('kontak') border-red-400 @enderror"
            >
            @error('kontak')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea
                id="alamat"
                name="alamat"
                rows="3"
                autocomplete="street-address"
                placeholder="Masukkan alamat lengkap"
                class="form-textarea @error('alamat') border-red-400 @enderror"
            >{{ old('alamat', $user->alamat) }}</textarea>
            @error('alamat')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="my-6 border-b border-slate-100 pb-5">
        <h2 class="text-base font-semibold text-slate-900">Keamanan &amp; Hak Akses</h2>
        <p class="mt-1 text-sm text-slate-500">
            {{ $user->exists
                ? 'Kosongkan kolom password jika tidak ingin menggantinya.'
                : 'Tentukan password awal dan role untuk user ini.' }}
        </p>
    </div>

    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
        <div>
            <label for="password" class="form-label">
                Password @if (! $user->exists)<span class="text-red-500">*</span>@else <span class="font-normal text-slate-400">(opsional)</span>@endif
            </label>
            <div class="relative">
                <input
                    id="password"
                    type="password"
                    name="password"
                    {{ $user->exists ? '' : 'required' }}
                    autocomplete="new-password"
                    placeholder="Minimal 8 karakter"
                    class="form-input pr-11 @error('password') border-red-400 focus:border-red-500 focus:ring-red-500/10 @enderror"
                    aria-describedby="password-bantuan @error('password') password-error @enderror"
                >
                <button
                    type="button"
                    data-toggle-password="password"
                    class="absolute top-1/2 right-2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Tampilkan password"
                    aria-pressed="false"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
            @error('password')
                <p id="password-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p id="password-bantuan" class="mt-1.5 text-xs text-slate-500">Minimal 8 karakter.</p>
        </div>

        <div>
            <label for="password_confirmation" class="form-label">
                Konfirmasi Password @if (! $user->exists)<span class="text-red-500">*</span>@endif
            </label>
            <div class="relative">
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    {{ $user->exists ? '' : 'required' }}
                    autocomplete="new-password"
                    placeholder="Ulangi password"
                    class="form-input pr-11"
                >
                <button
                    type="button"
                    data-toggle-password="password_confirmation"
                    class="absolute top-1/2 right-2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Tampilkan konfirmasi password"
                    aria-pressed="false"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
        </div>

        <fieldset class="sm:col-span-2">
            <legend class="form-label">Role User <span class="text-red-500">*</span></legend>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($daftarRole as $value => $role)
                    <label class="group relative flex cursor-pointer gap-3 rounded-xl border border-slate-300 bg-white p-4 transition hover:border-emerald-400 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60 has-[:checked]:ring-2 has-[:checked]:ring-emerald-500/20 has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-emerald-500/20">
                        <input
                            type="radio"
                            name="role"
                            value="{{ $value }}"
                            required
                            @checked($roleTerpilih === $value)
                            class="mt-0.5 h-4 w-4 shrink-0 border-slate-300 text-emerald-600 focus:ring-emerald-500"
                        >
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">{{ $role['label'] }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ $role['deskripsi'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('role')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </fieldset>
    </div>

    <div class="mt-8 flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row">
        <a href="{{ route('admin.user.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 px-5 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:bg-slate-50 focus-visible:ring-4 focus-visible:ring-slate-300/40 focus-visible:outline-none">Batal</a>
        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 hover:shadow-md focus-visible:ring-4 focus-visible:ring-emerald-500/20 focus-visible:outline-none">{{ $submitLabel }}</button>
    </div>
</form>
