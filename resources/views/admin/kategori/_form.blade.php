@csrf
<label for="nama_kategori" class="mb-1 block text-sm font-medium">Nama Kategori</label>
<input id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori ?? '') }}" required maxlength="50" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
@error('nama_kategori')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
<div class="mt-6 flex justify-end gap-3"><a href="{{ route('kategori.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">Batal</a><button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white">{{ $submitLabel }}</button></div>