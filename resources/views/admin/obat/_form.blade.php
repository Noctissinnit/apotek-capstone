@csrf
<div class="grid gap-4 sm:grid-cols-2">
    <div><label for="kode_obat" class="mb-1 block text-sm font-medium">Kode Obat</label><input id="kode_obat" name="kode_obat" value="{{ old('kode_obat', $obat->kode_obat ?? '') }}" required maxlength="20" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('kode_obat')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="nama_obat" class="mb-1 block text-sm font-medium">Nama Obat</label><input id="nama_obat" name="nama_obat" value="{{ old('nama_obat', $obat->nama_obat ?? '') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('nama_obat')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><span class="mb-1 block text-sm font-medium">Apotek</span>
        <div class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ auth()->user()->apotek }}</div>
    </div>
    <div><label for="kategori_id" class="mb-1 block text-sm font-medium">Kategori</label><select id="kategori_id" name="kategori_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Pilih kategori</option>@foreach ($kategori as $item)<option value="{{ $item->id_kategori }}" @selected(old('kategori_id', $obat->kategori_id ?? '') == $item->id_kategori)>{{ $item->nama_kategori }}</option>@endforeach
        </select>@error('kategori_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="satuan" class="mb-1 block text-sm font-medium">Satuan</label><input id="satuan" name="satuan" value="{{ old('satuan', $obat->satuan ?? '') }}" required maxlength="20" placeholder="Tablet, Strip, Botol" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('satuan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="harga_beli" class="mb-1 block text-sm font-medium">Harga Beli</label><input id="harga_beli" type="number" name="harga_beli" value="{{ old('harga_beli', $obat->harga_beli ?? 0) }}" required min="0" step="0.01" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('harga_beli')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="harga_jual" class="mb-1 block text-sm font-medium">Harga Jual</label><input id="harga_jual" type="number" name="harga_jual" value="{{ old('harga_jual', $obat->harga_jual ?? 0) }}" required min="0" step="0.01" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('harga_jual')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="stok" class="mb-1 block text-sm font-medium">Stok</label><input id="stok" type="number" name="stok" value="{{ old('stok', $obat->stok ?? 0) }}" required min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('stok')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="stok_minimum" class="mb-1 block text-sm font-medium">Stok Minimum</label><input id="stok_minimum" type="number" name="stok_minimum" value="{{ old('stok_minimum', $obat->stok_minimum ?? 0) }}" required min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('stok_minimum')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div><label for="tanggal_kadaluarsa" class="mb-1 block text-sm font-medium">Tanggal Kadaluarsa</label><input id="tanggal_kadaluarsa" type="date" name="tanggal_kadaluarsa" value="{{ old('tanggal_kadaluarsa', isset($obat) && $obat->tanggal_kadaluarsa ? $obat->tanggal_kadaluarsa->format('Y-m-d') : '') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">@error('tanggal_kadaluarsa')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
    <div class="sm:col-span-2"><label for="keterangan" class="mb-1 block text-sm font-medium">Keterangan</label><textarea id="keterangan" name="keterangan" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('keterangan', $obat->keterangan ?? '') }}</textarea></div>

    <div class="sm:col-span-2">
        <label for="gambar" class="mb-1 block text-sm font-medium">Gambar Obat</label>

        <div class="flex flex-wrap items-start gap-4">
            <img
                id="pratinjau-gambar"
                src="{{ isset($obat) && $obat->urlGambar() ? $obat->urlGambar() : '' }}"
                alt="Pratinjau gambar obat"
                class="h-24 w-24 rounded-xl border border-slate-200 bg-slate-50 object-cover {{ isset($obat) && $obat->urlGambar() ? '' : 'hidden' }}"
            >
            <span id="gambar-kosong" class="flex h-24 w-24 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-slate-300 {{ isset($obat) && $obat->urlGambar() ? 'hidden' : '' }}">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2 1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </span>

            <div class="min-w-0 flex-1">
                <input
                    id="gambar"
                    type="file"
                    name="gambar"
                    accept="image/jpeg,image/png,image/webp"
                    data-pratinjau="pratinjau-gambar"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100"
                >
                <p class="mt-1 text-xs text-slate-500">Format JPG, PNG, atau WEBP. Ukuran maksimal 2 MB.</p>
                @error('gambar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                @if (isset($obat) && $obat->urlGambar())
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="hapus_gambar" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                        Hapus gambar yang sekarang
                    </label>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="mt-6 flex justify-end gap-3"><a href="{{ route('obat.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a><button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">{{ $submitLabel }}</button></div>