import './bootstrap';
import 'htmx.org';

// ---------------------------------------------------------------------------
// Jendela tumpang: isinya diambil dari server, halaman tidak dimuat ulang
// ---------------------------------------------------------------------------
const modal = document.getElementById('modal-utama');

if (modal) {
    const isi = document.getElementById('modal-isi');

    const tutup = () => {
        if (!modal.open) return;
        modal.close();
        isi.innerHTML = '';
    };

    // dibuka setelah isinya selesai dimuat, supaya tidak sempat tampil kosong
    document.body.addEventListener('htmx:afterSwap', (event) => {
        if (event.detail.target !== isi) return;

        if (!modal.open) modal.showModal();
        isi.querySelector('input, select, textarea')?.focus();
    });

    // server membalas dengan header ini setelah data tersimpan
    document.body.addEventListener('htmx:afterRequest', (event) => {
        if (event.detail.xhr?.getResponseHeader('X-Tutup-Modal')) {
            tutup();
            window.location.reload();
        }
    });

    modal.addEventListener('click', (event) => {
        // klik di area gelap di luar kotak
        if (event.target === modal) tutup();
    });

    modal.addEventListener('close', () => (isi.innerHTML = ''));

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-tutup-modal]')) tutup();
    });
}
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

// ---------------------------------------------------------------------------
// Form barang masuk: tambah/hapus baris, dan hitung konversi box ke satuan
// ---------------------------------------------------------------------------
const daftarItem = document.querySelector('[data-daftar-item]');

if (daftarItem) {
    const wadah = daftarItem.querySelector('[data-wadah-baris]');
    const isiPerBox = JSON.parse(document.getElementById('data-isi-per-box')?.textContent ?? '{}');
    const rupiah = (angka) => 'Rp ' + Math.round(angka).toLocaleString('id-ID');

    // Nomor urut dan nama input disusun ulang supaya tetap rapi setelah ada baris dihapus
    const susunUlang = () => {
        wadah.querySelectorAll('[data-baris]').forEach((baris, urutan) => {
            baris.querySelector('[data-nomor-baris]').textContent = urutan + 1;
            baris.querySelectorAll('[name^="item["]').forEach((input) => {
                input.name = input.name.replace(/item\[\d*\]/, `item[${urutan}]`);
            });
        });

        const bisaDihapus = wadah.querySelectorAll('[data-baris]').length > 1;
        wadah.querySelectorAll('[data-hapus-baris]').forEach((tombol) => {
            tombol.disabled = !bisaDihapus;
            tombol.classList.toggle('invisible', !bisaDihapus);
        });
    };

    const hitung = () => {
        let total = 0;

        wadah.querySelectorAll('[data-baris]').forEach((baris) => {
            const obat = isiPerBox[baris.querySelector('[data-pilih-obat]').value];
            const satuan = baris.querySelector('[data-satuan-input]').value;
            const jumlah = Number(baris.querySelector('[data-jumlah]').value) || 0;
            const harga = Number(baris.querySelector('[data-harga]').value) || 0;
            const diskon = Number(baris.querySelector('[data-diskon]').value) || 0;
            const ringkasan = baris.querySelector('[data-ringkasan]');

            const isi = satuan === 'box' ? (obat?.isi ?? 1) : 1;
            const subtotal = jumlah * harga * (1 - diskon / 100);
            total += subtotal;

            if (!obat || jumlah < 1) {
                ringkasan.innerHTML = '&nbsp;';
                return;
            }

            const namaSatuan = obat.satuan ?? 'satuan';
            ringkasan.textContent =
                satuan === 'box'
                    ? `${jumlah * isi} ${namaSatuan} · modal ${rupiah((harga * (1 - diskon / 100)) / isi)}/${namaSatuan}`
                    : `${jumlah} ${namaSatuan} · ${rupiah(subtotal)}`;
        });

        daftarItem.querySelector('[data-total-faktur]').textContent = rupiah(total);
    };

    daftarItem.addEventListener('click', (event) => {
        if (event.target.closest('[data-tambah-baris]')) {
            const baru = wadah.querySelector('[data-baris]').cloneNode(true);
            baru.querySelectorAll('input').forEach((input) => (input.value = ''));
            baru.querySelectorAll('select').forEach((select) => (select.selectedIndex = 0));
            wadah.append(baru);
            susunUlang();
            hitung();
            baru.querySelector('[data-pilih-obat]').focus();
        }

        if (event.target.closest('[data-hapus-baris]')) {
            if (wadah.querySelectorAll('[data-baris]').length < 2) return;
            event.target.closest('[data-baris]').remove();
            susunUlang();
            hitung();
        }
    });

    daftarItem.addEventListener('input', hitung);
    daftarItem.addEventListener('change', hitung);

    susunUlang();
    hitung();
}

// Form retur/pemusnahan: kolom supplier hanya relevan untuk retur
const formPenyesuaian = document.querySelector('[data-form-penyesuaian]');

if (formPenyesuaian) {
    const bagianSupplier = formPenyesuaian.querySelector('[data-bagian-supplier]');

    const sesuaikan = () => {
        const retur = formPenyesuaian.querySelector('[data-pilih-jenis]:checked')?.value === 'retur';
        bagianSupplier.classList.toggle('hidden', !retur);
        bagianSupplier.querySelector('select').required = retur;
    };

    formPenyesuaian.addEventListener('change', (event) => {
        if (event.target.closest('[data-pilih-jenis]')) sesuaikan();
    });

    sesuaikan();
}

// Keterangan satuan di form obat ikut berubah saat satuan dipilih
document.addEventListener('change', (event) => {
    const pilihan = event.target.closest('[data-satuan-obat]');
    if (!pilihan) return;

    document.querySelectorAll('[data-label-satuan]').forEach((label) => {
        label.textContent = pilihan.value || 'satuan';
    });
});

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

// Uang diterima dan kembalian, hanya untuk pembayaran tunai
const bagianTunai = document.querySelector('[data-bagian-tunai]');

if (bagianTunai) {
    const total = Number(bagianTunai.dataset.total) || 0;
    const input = bagianTunai.querySelector('[data-uang-diterima]');
    const tampilanKembalian = bagianTunai.querySelector('[data-kembalian]');
    const rupiah = (angka) => 'Rp ' + Math.round(angka).toLocaleString('id-ID');

    const hitungKembalian = () => {
        const diterima = Number(input.value) || 0;
        const kembali = diterima - total;

        tampilanKembalian.textContent = kembali >= 0 ? rupiah(kembali) : 'Kurang ' + rupiah(Math.abs(kembali));
        tampilanKembalian.classList.toggle('text-red-600', kembali < 0);
        tampilanKembalian.classList.toggle('text-slate-900', kembali >= 0);
    };

    const bagianReferensi = document.querySelector('[data-bagian-referensi]');
    const bagianQris = document.querySelector('[data-bagian-qris]');

    const sesuaikanTampilan = () => {
        const dipilih = document.querySelector('input[name="metode_pembayaran"]:checked')?.value;
        const tunai = dipilih === 'Tunai';

        bagianTunai.classList.toggle('hidden', !tunai);
        input.required = tunai;

        // nomor referensi kebalikannya: hanya untuk pembayaran non-tunai
        bagianReferensi?.classList.toggle('hidden', tunai);

        // kode QR hanya relevan untuk QRIS
        bagianQris?.classList.toggle('hidden', dipilih !== 'QRIS');

        if (tunai) hitungKembalian();
    };

    document.addEventListener('change', (event) => {
        if (event.target.name === 'metode_pembayaran') sesuaikanTampilan();
    });

    input.addEventListener('input', hitungKembalian);

    bagianTunai.addEventListener('click', (event) => {
        const tombol = event.target.closest('[data-uang-cepat]');
        if (!tombol) return;

        input.value = tombol.dataset.uangCepat;
        hitungKembalian();
        input.focus();
    });

    sesuaikanTampilan();
}

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
                ${
                    transaksi.kembalian
                        ? `<div class="mt-2 rounded-xl bg-amber-50 p-3">
                               <div class="flex justify-between text-xs text-slate-600">
                                   <span>Uang diterima</span><span>${transaksi.uang_diterima}</span>
                               </div>
                               <div class="mt-1 flex items-baseline justify-between">
                                   <span class="text-sm font-medium text-slate-700">Kembalian</span>
                                   <span class="text-xl font-bold text-amber-700">${transaksi.kembalian}</span>
                               </div>
                           </div>`
                        : ''
                }
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
