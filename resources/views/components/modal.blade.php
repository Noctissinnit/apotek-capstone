{{--
    Jendela tumpang yang dipakai bersama seluruh halaman.

    Isinya diambil dari server lewat htmx, jadi halaman di belakangnya tidak
    perlu dimuat ulang. Halaman penuh tetap dipertahankan sebagai cadangan:
    kalau JavaScript mati, tautan dan tombolnya berjalan seperti biasa.
--}}
<dialog id="modal-utama" class="dialog-panel backdrop:bg-slate-900/40" aria-labelledby="modal-judul">
    <div id="modal-isi" class="max-h-[85vh] overflow-y-auto">
        {{-- diisi htmx --}}
    </div>
</dialog>
