{{-- Isi modal untuk tambah dan ubah obat. Formnya dipakai bersama halaman penuh. --}}
<x-modal-isi
    :judul="$obat->exists ? 'Ubah Obat' : 'Tambah Obat'"
    :keterangan="$obat->exists ? $obat->nama_obat.' · '.$obat->kode_obat : 'Lengkapi data obat baru'"
>
    <form
        hx-post="{{ $obat->exists ? route('obat.update', $obat) : route('obat.store') }}"
        hx-target="#modal-isi"
        hx-swap="innerHTML"
        hx-encoding="multipart/form-data"
        action="{{ $obat->exists ? route('obat.update', $obat) : route('obat.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @if ($obat->exists)
            @method('PUT')
        @endif

        @include('admin.obat._form', [
            'submitLabel' => $obat->exists ? 'Simpan Perubahan' : 'Simpan',
            'dalamModal' => true,
        ])
    </form>
</x-modal-isi>
