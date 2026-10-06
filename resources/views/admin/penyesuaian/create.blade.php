@extends('layouts.app')

@section('title', 'Buat Dokumen Retur / Pemusnahan')
@section('header', 'Retur atau Pemusnahan Stok')
@section('subheader', 'Centang batch yang barangnya sudah dikeluarkan dari rak')

@section('content')
    @php
        $semuaBatch = $batchKadaluarsa->concat($batchLain);
        $jenisLama = old('jenis', request('jenis', 'musnah'));
    @endphp

    <form method="POST" action="{{ route('penyesuaian.store') }}" class="space-y-6" data-form-penyesuaian>
        @csrf

        @if ($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <p class="font-semibold">Periksa kembali isian berikut:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="panel-card">
            <div class="panel-header"><h2 class="panel-title">Data Dokumen</h2></div>
            <div class="panel-body grid gap-4 sm:grid-cols-2">
                <fieldset class="sm:col-span-2">
                    <legend class="mb-2 text-sm font-medium text-slate-700">Tindakan</legend>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ([
                            'musnah' => ['Dimusnahkan', 'Barang dihancurkan secara fisik. Nilainya dihitung sebagai kerugian penuh.'],
                            'retur' => ['Diretur ke Supplier', 'Barang dikembalikan. Biasanya diganti barang atau uang oleh supplier.'],
                        ] as $nilai => [$judul, $penjelasan])
                            <label class="flex cursor-pointer gap-3 rounded-md border border-slate-300 bg-white p-4 transition hover:border-emerald-400 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60">
                                <input type="radio" name="jenis" value="{{ $nilai }}" required @checked($jenisLama === $nilai) class="mt-0.5 h-4 w-4 shrink-0 border-slate-300 text-emerald-600" data-pilih-jenis>
                                <span>
                                    <span class="block text-sm font-semibold text-slate-900">{{ $judul }}</span>
                                    <span class="mt-0.5 block text-xs text-slate-500">{{ $penjelasan }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </fieldset>

                <div>
                    <label for="no_dokumen" class="mb-1 block text-sm font-medium">No Dokumen</label>
                    <input id="no_dokumen" name="no_dokumen" value="{{ old('no_dokumen', $nomorUsulan) }}" required maxlength="50" class="form-input">
                </div>

                <div>
                    <label for="tanggal" class="mb-1 block text-sm font-medium">Tanggal</label>
                    <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required class="form-input">
                </div>

                <div class="sm:col-span-2" data-bagian-supplier>
                    <label for="supplier_id" class="mb-1 block text-sm font-medium">Supplier Tujuan</label>
                    <select id="supplier_id" name="supplier_id" class="form-select">
                        <option value="">Pilih supplier</option>
                        @foreach ($supplier as $item)
                            <option value="{{ $item->id }}" @selected(old('supplier_id') == $item->id)>{{ $item->nama_supplier }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Wajib diisi untuk retur.</p>
                    @error('supplier_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="keterangan" class="mb-1 block text-sm font-medium">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="2" class="form-textarea" placeholder="Disaksikan oleh, cara pemusnahan, atau catatan lain">{{ old('keterangan') }}</textarea>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <h2 class="panel-title">Pilih Batch</h2>
                <span class="badge-danger">{{ $batchKadaluarsa->count() }} batch kadaluarsa</span>
            </div>

            @if ($semuaBatch->isEmpty())
                <div class="empty-state">
                    <p>Tidak ada batch berstok yang bisa dikeluarkan.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="table-head">
                            <tr>
                                <th scope="col" class="table-th w-10"><span class="sr-only">Pilih</span></th>
                                <th scope="col" class="table-th">Obat</th>
                                <th scope="col" class="table-th">Batch</th>
                                <th scope="col" class="table-th">Kadaluarsa</th>
                                <th scope="col" class="table-th text-right">Stok</th>
                                <th scope="col" class="table-th">Jumlah Keluar</th>
                                <th scope="col" class="table-th">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($semuaBatch as $i => $batch)
                                @php $kadaluarsa = $batch->sudahKadaluarsa(); @endphp
                                <tr class="{{ $kadaluarsa ? 'bg-red-50/40' : '' }}">
                                    <td class="px-5 py-3">
                                        <input type="checkbox" name="item[{{ $i }}][pilih]" value="1" @checked(old("item.$i.pilih")) class="rounded border-slate-300 text-emerald-600" aria-label="Pilih batch {{ $batch->no_batch }}">
                                        <input type="hidden" name="item[{{ $i }}][batch_obat_id]" value="{{ $batch->id }}">
                                    </td>
                                    <td class="table-td font-medium text-slate-900">{{ $batch->obat?->nama_obat }}</td>
                                    <td class="table-td">{{ $batch->no_batch }}</td>
                                    <td class="table-td">
                                        {{ $batch->tanggal_kadaluarsa?->translatedFormat('d M Y') ?? '-' }}
                                        @if ($kadaluarsa)
                                            <span class="badge-danger ml-1">Lewat {{ abs($batch->sisaHari()) }} hari</span>
                                        @endif
                                    </td>
                                    <td class="table-td text-right">{{ $batch->stok }} {{ $batch->obat?->satuan }}</td>
                                    <td class="px-5 py-3">
                                        <input type="number" name="item[{{ $i }}][jumlah]" value="{{ old("item.$i.jumlah", $batch->stok) }}" min="0" max="{{ $batch->stok }}" class="form-input h-9 w-24">
                                    </td>
                                    <td class="px-5 py-3">
                                        <select name="item[{{ $i }}][alasan]" class="form-select h-9">
                                            @foreach (['kadaluarsa' => 'Kadaluarsa', 'rusak' => 'Rusak', 'hilang' => 'Hilang', 'lainnya' => 'Lainnya'] as $nilai => $label)
                                                <option value="{{ $nilai }}" @selected(old("item.$i.alasan", $kadaluarsa ? 'kadaluarsa' : 'rusak') === $nilai)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row">
            <a href="{{ route('penyesuaian.index') }}" class="btn-ghost">Batal</a>
            <button type="submit" class="btn-primary" @disabled($semuaBatch->isEmpty())>Simpan &amp; Kurangi Stok</button>
        </div>
    </form>
@endsection
