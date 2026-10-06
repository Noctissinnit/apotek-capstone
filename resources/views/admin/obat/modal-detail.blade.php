{{-- Isi modal untuk melihat detail obat, tanpa meninggalkan daftar. --}}
<x-modal-isi :judul="$obat->nama_obat" :keterangan="$obat->kode_obat.' · '.$obat->apotek">
    <div class="flex gap-4">
        @if ($obat->urlGambar())
            <img src="{{ $obat->urlGambar() }}" alt="" class="h-28 w-28 shrink-0 rounded-md border border-slate-200 object-cover">
        @endif

        <dl class="grid flex-1 grid-cols-2 gap-x-4 gap-y-3 text-sm">
            @foreach ([
                ['Kategori', $obat->kategoriRelasi?->nama_kategori ?: '-'],
                ['Satuan', $obat->satuan],
                ['Isi per Box', $obat->isi_per_box.' '.$obat->satuan],
                ['Harga Jual', 'Rp '.number_format($obat->harga_jual, 0, ',', '.')],
                ['Stok Tercatat', $obat->stok.' '.$obat->satuan],
                ['Siap Dijual', $obat->stokSiapDijual().' '.$obat->satuan],
            ] as [$label, $nilai])
                <div>
                    <dt class="text-xs tracking-wide text-slate-500 uppercase">{{ $label }}</dt>
                    <dd class="mt-0.5 font-medium text-slate-900">{{ $nilai }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    @if ($obat->keterangan)
        <p class="mt-4 rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ $obat->keterangan }}</p>
    @endif

    <div class="mt-5">
        <h3 class="mb-2 text-xs font-semibold tracking-wide text-slate-500 uppercase">Batch</h3>

        @forelse ($obat->batch()->orderBy('tanggal_kadaluarsa')->get() as $batch)
            @php $tingkat = $batch->tingkatKadaluarsa(); @endphp
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0">
                <div>
                    <span class="font-medium text-slate-900">{{ $batch->no_batch }}</span>
                    <span class="ml-2 text-slate-500">{{ $batch->tanggal_kadaluarsa?->translatedFormat('d M Y') ?? 'tanpa tanggal' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    @if ($batch->status !== 'aktif')
                        <span class="badge-warning">Ditahan</span>
                    @elseif ($tingkat === 'kadaluarsa')
                        <span class="badge-danger">Kadaluarsa</span>
                    @elseif ($tingkat === 'segera_habis')
                        <span class="badge-warning">Sisa {{ $batch->sisaHari() }} hari</span>
                    @endif
                    <span class="tabular-nums text-slate-700">{{ $batch->stok }} {{ $obat->satuan }}</span>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">Belum ada batch. Stok bertambah lewat Barang Masuk.</p>
        @endforelse
    </div>

    <div class="mt-5 flex justify-end gap-2">
        <button type="button" class="btn-ghost" data-tutup-modal>Tutup</button>
        @can('obat.kelola')
            <button type="button" class="btn-primary"
                hx-get="{{ route('obat.edit', $obat) }}"
                hx-target="#modal-isi"
                hx-swap="innerHTML">
                Ubah Data
            </button>
        @endcan
    </div>
</x-modal-isi>
