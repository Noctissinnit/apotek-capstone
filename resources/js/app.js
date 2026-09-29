import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

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
