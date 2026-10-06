{{--
    Kerangka isi modal: kepala dengan judul dan tombol tutup, lalu isinya.

    Dipakai oleh potongan tampilan yang dimuat ke dalam modal, supaya
    kepala dan jarak tepinya sama di semua halaman.
--}}
@props(['judul', 'keterangan' => null])

<div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3">
    <div class="min-w-0">
        <h2 id="modal-judul" class="text-sm font-semibold text-slate-900">{{ $judul }}</h2>
        @if ($keterangan)
            <p class="mt-0.5 text-sm text-slate-500">{{ $keterangan }}</p>
        @endif
    </div>
    <button type="button" class="-m-1 rounded p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" data-tutup-modal aria-label="Tutup">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

<div class="p-4">
    {{ $slot }}
</div>
