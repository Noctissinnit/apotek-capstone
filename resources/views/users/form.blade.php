<form method="POST" action="{{ $action }}" class="max-w-3xl rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
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

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email Login</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
        <div>
            <label for="kontak" class="mb-1 block text-sm font-medium text-slate-700">Kontak</label>
            <input id="kontak" name="kontak" value="{{ old('kontak', $user->kontak) }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
        <div class="sm:col-span-2">
            <label for="alamat" class="mb-1 block text-sm font-medium text-slate-700">Alamat</label>
            <textarea id="alamat" name="alamat" rows="3" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('alamat', $user->alamat) }}</textarea>
        </div>
        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password {{ $user->exists ? '(opsional)' : '' }}</label>
            <input id="password" type="password" name="password" {{ $user->exists ? '' : 'required' }} class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" {{ $user->exists ? '' : 'required' }} class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
        <div>
            <label for="role" class="mb-1 block text-sm font-medium text-slate-700">Role</label>
            <select id="role" name="role" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Pilih role</option>
                @foreach (['admin' => 'Admin', 'kasir' => 'Kasir'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', $user->exists ? $user->getRoleNames()->first() : '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-2">
        <a href="{{ route('user.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Batal</a>
        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ $submitLabel }}</button>
    </div>
</form>
