<form method="POST" action="{{ $action }}" class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-medium">Periksa kembali input berikut:</p>
            <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mb-6 border-b border-slate-100 pb-5">
        <h2 class="text-base font-semibold text-slate-900">Informasi Akun</h2>
        <p class="mt-1 text-sm text-slate-500">Lengkapi data dasar dan akses login user.</p>
    </div>

    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="form-input" placeholder="Masukkan nama lengkap">
        </div>
        <div>
            <label for="email" class="form-label">Email Login</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input" placeholder="contoh@email.com">
        </div>
        <div>
            <label for="kontak" class="form-label">Nomor Kontak</label>
            <input id="kontak" name="kontak" value="{{ old('kontak', $user->kontak) }}" class="form-input" placeholder="08xxxxxxxxxx">
        </div>
        <div class="sm:col-span-2">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea id="alamat" name="alamat" rows="3" class="form-textarea" placeholder="Masukkan alamat lengkap">{{ old('alamat', $user->alamat) }}</textarea>
        </div>
    </div>

    <div class="my-6 border-b border-slate-100 pb-5">
        <h2 class="text-base font-semibold text-slate-900">Keamanan & Hak Akses</h2>
        <p class="mt-1 text-sm text-slate-500">Tentukan password dan role untuk user ini.</p>
    </div>

    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
        <div>
            <label for="password" class="form-label">Password {{ $user->exists ? '(opsional)' : '' }}</label>
            <input id="password" type="password" name="password" {{ $user->exists ? '' : 'required' }} class="form-input" placeholder="Minimal 8 karakter">
        </div>
        <div>
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" {{ $user->exists ? '' : 'required' }} class="form-input" placeholder="Ulangi password">
        </div>
        <div>
            <label for="role" class="form-label">Role User</label>
            <div class="relative">
                <select id="role" name="role" required class="form-select">
                    <option value="">Pilih role</option>
                    @foreach (['admin' => 'Admin', 'kasir' => 'Kasir'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->exists ? $user->getRoleNames()->first() : '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <svg class="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    <div class="mt-8 flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row">
        <a href="{{ route('user.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 px-5 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:bg-slate-50">Batal</a>
        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 hover:shadow-md focus:ring-4 focus:ring-emerald-500/20 focus:outline-none">{{ $submitLabel }}</button>
    </div>
</form>
