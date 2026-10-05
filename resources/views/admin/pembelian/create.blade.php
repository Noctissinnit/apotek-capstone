@extends('layouts.app')

@section('title', 'Catat Barang Masuk')
@section('header', 'Catat Barang Masuk')
@section('subheader', 'Satu baris untuk satu batch. Obat yang sama boleh ditulis beberapa baris.')

@section('content')
    @php
        // dipakai JavaScript untuk menghitung dan menampilkan konversi box ke satuan terkecil
        $isiPerBox = $obat->mapWithKeys(fn ($item) => [$item->id => ['isi' => $item->isi_per_box, 'satuan' => $item->satuan]]);
    @endphp

    <form method="POST" action="{{ route('pembelian.store') }}" class="space-y-6">
        @csrf

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-semibold">Periksa kembali isian berikut:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="panel-card">
            <div class="panel-header"><h2 class="panel-title">Data Faktur</h2></div>
            <div class="panel-body grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="no_faktur" class="mb-1 block text-sm font-medium">No Faktur</label>
                    <input id="no_faktur" name="no_faktur" value="{{ old('no_faktur', $nomorUsulan) }}" required maxlength="50" class="form-input">
                </div>
                <div>
                    <label for="supplier_id" class="mb-1 block text-sm font-medium">Supplier</label>
                    <select id="supplier_id" name="supplier_id" required class="form-select">
                        <option value="">Pilih supplier</option>
                        @foreach ($supplier as $item)
                            <option value="{{ $item->id }}" @selected(old('supplier_id') == $item->id)>{{ $item->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tanggal_pembelian" class="mb-1 block text-sm font-medium">Tanggal</label>
                    <input id="tanggal_pembelian" type="date" name="tanggal_pembelian" value="{{ old('tanggal_pembelian', now()->toDateString()) }}" required class="form-input">
                </div>
                <div class="sm:col-span-3">
                    <label for="keterangan" class="mb-1 block text-sm font-medium">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="2" class="form-textarea">{{ old('keterangan') }}</textarea>
                </div>
            </div>
        </div>

        <div class="panel-card" data-daftar-item>
            <div class="panel-header">
                <h2 class="panel-title">Obat yang Masuk</h2>
                <button type="button" class="btn-ghost px-3 py-1.5 text-xs" data-tambah-baris>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                    Tambah Baris
                </button>
            </div>

            <div class="panel-body space-y-4" data-wadah-baris>
                @php
                    $barisLama = old('item', [[]]);
                @endphp

                @foreach ($barisLama as $i => $baris)
                    <fieldset class="rounded-xl border border-slate-200 p-4" data-baris>
                        <legend class="px-1 text-xs font-semibold text-slate-500">Baris <span data-nomor-baris>{{ $i + 1 }}</span></legend>

                        <div class="grid gap-3 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label class="mb-1 block text-xs font-medium text-slate-600">Obat</label>
                                <select name="item[{{ $i }}][obat_id]" required class="form-select" data-pilih-obat>
                                    <option value="">Pilih obat</option>
                                    @foreach ($obat as $item)
                                        <option value="{{ $item->id }}" @selected(($baris['obat_id'] ?? '') == $item->id)>{{ $item->nama_obat }} ({{ $item->kode_obat }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Satuan</label>
                                <select name="item[{{ $i }}][satuan_input]" class="form-select" data-satuan-input>
                                    <option value="box" @selected(($baris['satuan_input'] ?? 'box') === 'box')>Box</option>
                                    <option value="satuan" @selected(($baris['satuan_input'] ?? '') === 'satuan')>Satuan terkecil</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Jumlah</label>
                                <input type="number" name="item[{{ $i }}][jumlah]" value="{{ $baris['jumlah'] ?? '' }}" required min="1" class="form-input" data-jumlah>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Harga Beli</label>
                                <input type="number" name="item[{{ $i }}][harga_beli]" value="{{ $baris['harga_beli'] ?? '' }}" required min="0" step="0.01" class="form-input" data-harga>
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Diskon (%)</label>
                                <input type="number" name="item[{{ $i }}][diskon_persen]" value="{{ $baris['diskon_persen'] ?? '' }}" min="0" max="100" step="0.01" placeholder="0" class="form-input" data-diskon>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-slate-600">No Batch</label>
                                <input name="item[{{ $i }}][no_batch]" value="{{ $baris['no_batch'] ?? '' }}" required maxlength="50" class="form-input">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-medium text-slate-600">Tanggal Kadaluarsa</label>
                                <input type="date" name="item[{{ $i }}][tanggal_kadaluarsa]" value="{{ $baris['tanggal_kadaluarsa'] ?? '' }}" class="form-input">
                            </div>

                            <div class="flex items-end justify-between gap-2 sm:col-span-2">
                                <p class="text-xs text-slate-500" data-ringkasan>&nbsp;</p>
                                <button type="button" class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600" data-hapus-baris aria-label="Hapus baris ini">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </fieldset>
                @endforeach
            </div>

            <div class="border-t border-slate-200 px-5 py-4 text-right">
                <p class="text-sm text-slate-500">Perkiraan total faktur</p>
                <p class="text-2xl font-bold text-slate-900" data-total-faktur>Rp 0</p>
            </div>
        </div>

        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
            <a href="{{ route('pembelian.index') }}" class="btn-ghost">Batal</a>
            <button type="submit" class="btn-primary">Simpan &amp; Tambah Stok</button>
        </div>
    </form>

    <script id="data-isi-per-box" type="application/json">@json($isiPerBox)</script>
@endsection
