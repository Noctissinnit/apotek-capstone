import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// Pratinjau gambar sebelum diunggah
document.addEventListener('change', (event) => {
    const input = event.target.closest('input[type="file"][data-pratinjau]');
    if (!input) return;

    const pratinjau = document.getElementById(input.dataset.pratinjau);
    const kosong = document.getElementById('gambar-kosong');
    const berkas = input.files?.[0];
    if (!pratinjau || !berkas) return;

    pratinjau.src = URL.createObjectURL(berkas);
    pratinjau.classList.remove('hidden');
    kosong?.classList.add('hidden');
});

// Konfirmasi sebelum pembayaran diproses, supaya kasir tidak salah tekan
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-konfirmasi-bayar]');
    if (!form || form.dataset.sudahDikonfirmasi === 'ya') return;

    event.preventDefault();

    const metode = form.querySelector('input[name="metode_pembayaran"]:checked')?.value ?? 'Tunai';

    Swal.fire({
        icon: 'question',
        title: 'Sudah yakin?',
        html: `
            <div class="text-left text-sm">
                <div class="flex justify-between border-b border-slate-100 py-1.5">
                    <span class="text-slate-500">Jumlah item</span>
                    <span class="font-medium text-slate-800">${form.dataset.jumlahItem}</span>
                </div>
                <div class="flex justify-between border-b border-slate-100 py-1.5">
                    <span class="text-slate-500">Cara bayar</span>
                    <span class="font-medium text-slate-800">${metode}</span>
                </div>
                <div class="flex items-baseline justify-between py-1.5">
                    <span class="text-slate-500">Total</span>
                    <span class="text-xl font-bold text-slate-900">${form.dataset.total}</span>
                </div>
                <p class="mt-2 text-xs text-slate-500">Stok obat akan langsung berkurang setelah ini.</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ya, proses',
        cancelButtonText: 'Periksa lagi',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'btn-primary',
            cancelButton: 'btn-ghost',
        },
        buttonsStyling: false,
    }).then((hasil) => {
        if (!hasil.isConfirmed) return;

        // tandai agar kiriman kedua tidak ditahan lagi, lalu kunci tombolnya
        form.dataset.sudahDikonfirmasi = 'ya';
        form.querySelectorAll('button[type="submit"]').forEach((tombol) => {
            tombol.disabled = true;
            tombol.textContent = 'Memproses...';
        });
        form.submit();
    });
});

// Notifikasi setelah pembayaran selesai.
// Datanya dikirim layout lewat <script type="application/json" id="transaksi-sukses">.
const dataTransaksi = document.getElementById('transaksi-sukses');

if (dataTransaksi) {
    const transaksi = JSON.parse(dataTransaksi.textContent);

    Swal.fire({
        icon: 'success',
        title: 'Pembayaran Berhasil',
        html: `
            <div class="text-left text-sm">
                <div class="mb-3 rounded-xl bg-slate-50 p-3">
                    <p class="text-xs text-slate-500">No. Faktur</p>
                    <p class="font-semibold text-slate-900">${transaksi.no_faktur}</p>
                </div>
                <div class="flex justify-between border-b border-slate-100 py-1.5">
                    <span class="text-slate-500">Jumlah item</span>
                    <span class="font-medium text-slate-800">${transaksi.jumlah_item}</span>
                </div>
                <div class="flex justify-between border-b border-slate-100 py-1.5">
                    <span class="text-slate-500">Cara bayar</span>
                    <span class="font-medium text-slate-800">${transaksi.metode_pembayaran}</span>
                </div>
                <div class="flex items-baseline justify-between py-1.5">
                    <span class="text-slate-500">Total</span>
                    <span class="text-xl font-bold text-emerald-700">${transaksi.total}</span>
                </div>
            </div>
        `,
        showDenyButton: true,
        confirmButtonText: 'Transaksi Baru',
        denyButtonText: 'Unduh Struk',
        reverseButtons: true,
        focusConfirm: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'btn-primary',
            denyButton: 'btn-ghost',
        },
        buttonsStyling: false,
    }).then((hasil) => {
        if (hasil.isConfirmed) {
            window.location.href = transaksi.url_transaksi_baru;
        } else if (hasil.isDenied) {
            window.open(transaksi.url_struk, '_blank');
        }
    });
}

// Toggle sidebar di layar kecil
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-sidebar-toggle]');
    if (!toggle) return;

    document.getElementById('sidebar')?.classList.toggle('-translate-x-full');
    document.getElementById('sidebar-backdrop')?.classList.toggle('hidden');
});

// Tombol tampilkan/sembunyikan password pada form
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-toggle-password]');
    if (!toggle) return;

    const input = document.getElementById(toggle.dataset.togglePassword);
    if (!input) return;

    const terlihat = input.type === 'text';
    input.type = terlihat ? 'password' : 'text';
    toggle.setAttribute('aria-pressed', String(!terlihat));
    toggle.setAttribute('aria-label', terlihat ? 'Tampilkan password' : 'Sembunyikan password');
});
