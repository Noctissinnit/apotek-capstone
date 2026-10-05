# Change Request

Catatan perubahan requirement setelah scope dikunci di Minggu 2.
Prosedurnya mengikuti Implementation Plan bagian 13.

---

## CR-01 — Pencatatan per batch, satuan beli, dan penyelesaian stok kadaluarsa

| | |
|---|---|
| **Tanggal diajukan** | 5 Oktober 2026 |
| **Sumber permintaan** | Mitra (hasil tanya jawab menyusul observasi) |
| **Diajukan oleh** | Alexander |
| **Status** | DISETUJUI |

### Latar belakang

Saat memeriksa data obat, mitra memberikan daftar kolom yang mereka pakai:

```
barang, satuan beli, konversi, harga beli, diskon, ED, No Batch
```

Daftar itu ternyata kolom dari **faktur pembelian**, bukan data master obat. Dari situ
muncul tiga kebutuhan yang tidak tercantum di rencana awal, dan mitra mengkonfirmasi
bahwa mereka memang **melacak obat per batch**.

### Apa yang berubah

**1. Satuan beli dan konversi**

Apotek membeli dalam box, tetapi menjual dalam satuan terkecil (tablet, botol, tube).
Sistem sekarang hanya mengenal satu satuan per obat, sehingga "beli 5 box" akan
menambah stok 5, bukan 500.

```
Beli       : 5 Box
Konversi   : 1 Box = 100 Tablet
Harga beli : Rp 45.000 / Box, diskon 10%

Seharusnya:
  stok bertambah   : 500 Tablet
  modal per tablet : Rp 405
```

**2. Batch dan tanggal kadaluarsa**

Satu obat dapat memiliki beberapa batch dengan tanggal kadaluarsa berbeda. Sekarang
tanggal kadaluarsa hanya satu per obat, sehingga angka "mendekati kadaluarsa" bisa keliru.

**3. Penyelesaian stok kadaluarsa**

Obat yang lewat tanggal kadaluarsa diselesaikan dengan dua cara, dan keduanya
mengurangi stok:

- **Diretur** ke supplier, disertai Nota Retur
- **Dimusnahkan** secara fisik, disertai Berita Acara Pemusnahan

### Keputusan rancangan

Hasil pembahasan dengan mitra:

| Hal | Keputusan |
|---|---|
| Satuan jual | Satuan terkecil per obat: Tablet, Botol, atau Tube |
| Konversi | Selalu per box, disimpan sebagai "isi per box" di master obat |
| Diskon | Dalam persen, diketik manual, boleh dikosongkan |
| Harga jual | Ditentukan admin secara manual, tidak mengikuti harga beli |
| Pengurangan stok saat jual | Otomatis dari batch yang paling dekat kadaluarsa |
| Kendali admin | Admin dapat menandai batch "jangan dijual dulu" (rusak, mau diretur, ditarik) |
| Obat lewat ED | Otomatis tidak dapat dijual, menunggu tindakan admin |
| Hak akses retur & pemusnahan | Admin saja |
| Dokumen | Nota Retur dan Berita Acara Pemusnahan, dapat dicetak PDF |
| Laporan nilai | Nilai retur dan nilai pemusnahan dipisah, karena retur biasanya diganti supplier |

### Evaluasi (Implementation Plan bagian 13)

| Pertanyaan | Jawaban |
|---|---|
| Dibutuhkan mitra? | Ya |
| Sudah divalidasi? | Ya, langsung dari mitra |
| Termasuk scope? | Ya. Core MVP butir 5, 7, dan 14 menyebut pengelolaan stok dan pengurangan stok |
| Must Have? | Ya. Tanpa konversi, angka stok dan modal salah terus |
| Memerlukan database change? | Ya. Tabel batch baru, kolom isi per box, tabel penyesuaian stok |
| Memerlukan API eksternal? | Tidak |
| Menambah risiko? | Ya. Menyentuh alur checkout yang sudah lolos Gate G5 |
| Mengganggu milestone? | Ya. Praktis menjadi seluruh sisa pekerjaan pengembangan |
| Mengorbankan fitur Must Have? | Tidak. Yang ditunda hanya perbaikan tampilan (modal), bukan fitur wajib |

### Dampak ke jadwal

| Tahap | Isi | Perkiraan |
|---|---|---|
| A | Isi per box di master obat + tabel batch | 1 hari |
| B | Modul barang masuk: konversi, diskon, buat batch, tambah stok | 2–3 hari |
| C | Stok berkurang otomatis dari batch terdekat ED + indikator kadaluarsa | 1–2 hari |
| D | Retur & pemusnahan + dokumen PDF + laporan nilai | 1,5–2 hari |

**Total 6–8 hari kerja.**

Yang ditunda sebagai gantinya:

- Tampilan modal untuk tambah, edit, dan detail obat (mempercantik, bukan kebutuhan mitra)
- Pemilihan batch oleh kasir saat menjual, diganti pengurangan otomatis

### Catatan risiko

1. **Modul barang masuk belum ada sama sekali.** Yang tersedia baru tabel pembelian
   dan data contoh. Belum ada halaman, menu, maupun penambahan stok. Jadi tahap B
   adalah membangun modul baru, bukan menambah kolom.
2. **Alur checkout akan disentuh** pada tahap C. Alur ini baru saja stabil dan lolos
   Gate G5, jadi test yang ada harus tetap hijau setiap langkah.
3. **Kasir perlu memasukkan jumlah dalam satuan terkecil.** Pembeli yang minta satu
   strip berarti 10 tablet. Ini rawan salah ketik saat ramai, dan perlu diperhatikan
   saat UAT.

### Yang masih menunggu jawaban mitra

- Batas waktu retur: berapa lama sebelum tanggal kadaluarsa supplier masih menerima
  retur? Angka ini menentukan ambang peringatan "siapkan retur"
- Apakah hasil retur perlu dicatat (diganti barang, diganti uang, atau ditolak).
  Sementara ini cukup dicatat keluarnya saja
