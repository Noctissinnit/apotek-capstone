# API v1 untuk frontend Next.js

Semua endpoint berawalan `/api/v1`. Login memakai **cookie session Sanctum (mode SPA)**,
bukan token. Next.js dan Laravel harus berada di domain induk yang sama, misalnya
`app.apotek.id` (Next.js) dan `api.apotek.id` (Laravel).

## Pengaturan `.env` Laravel

| Kunci | Lokal | Produksi (contoh) |
|---|---|---|
| `FRONTEND_URL` | `http://localhost:3000` | `https://app.apotek.id` |
| `SANCTUM_STATEFUL_DOMAINS` | `localhost:3000,localhost,127.0.0.1` | `app.apotek.id` |
| `SESSION_DOMAIN` | `null` | `.apotek.id` |

Setelah mengubah `.env`, jalankan `php artisan config:clear`.

## Alur login

1. `GET /sanctum/csrf-cookie` untuk mengisi cookie `XSRF-TOKEN`.
2. `POST /api/v1/login` dengan isi `{ "email", "password", "remember"? }`.
3. Request berikutnya otomatis membawa cookie session.

Setiap request **wajib** memakai:

- `credentials: "include"` (fetch) atau `withCredentials: true` (axios)
- header `Accept: application/json`
- header `X-XSRF-TOKEN` berisi nilai cookie `XSRF-TOKEN` (hasil `decodeURIComponent`) untuk POST/PUT/PATCH/DELETE. Axios mengisinya sendiri bila `withXSRFToken: true`.

```ts
// contoh dengan axios
const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL, // http://localhost:8000
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
});

await api.get('/sanctum/csrf-cookie');
const { data } = await api.post('/api/v1/login', { email, password });
```

## Endpoint

| Metode | Path | Akses | Keterangan |
|---|---|---|---|
| POST | `/api/v1/login` | umum | Membalas data user (bentuk sama dengan `/me`) |
| POST | `/api/v1/logout` | login | |
| GET | `/api/v1/me` | login | User yang sedang login |
| GET | `/api/v1/referensi` | login | Pilihan tetap: satuan, urutan obat, status batch, metode pembayaran, ambang kadaluarsa |
| GET | `/api/v1/obat` | `obat.lihat` | Daftar obat (dibagi per halaman) |
| GET | `/api/v1/obat/{id}` | `obat.lihat` | Satu obat beserta seluruh batch-nya, `meta.stok_siap_dijual` |
| GET | `/api/v1/kategori` | `obat.lihat` | Semua kategori + `jumlah_obat` milik apotek sendiri (tanpa pagination) |
| GET | `/api/v1/batch` | `obat.lihat` | Daftar batch, urut dari yang paling dekat kadaluarsa |
| GET | `/api/v1/batch/{id}` | `obat.lihat` | Satu batch + `riwayat` keluar masuk + `meta` (stok awal, total masuk/keluar) |
| GET | `/api/v1/dashboard/admin` | role admin | Ringkasan dashboard admin |
| GET | `/api/v1/dashboard/kasir` | role kasir | Ringkasan dashboard kasir |
| GET | `/api/v1/monitoring` | role kasir | Stok menipis dan batch per tingkat peringatan kadaluarsa |

Semua data otomatis dibatasi pada apotek user yang login. Obat atau batch milik
apotek lain dibalas 404.

### Parameter daftar

`/api/v1/obat`

| Parameter | Isi |
|---|---|
| `search` | Cari di kode, nama, atau nama kategori |
| `kategori_id` | Id kategori |
| `satuan` | Salah satu dari `referensi.satuan` |
| `sort` | `kode` (bawaan), `nama`, `stok`, `kategori`, `satuan`, `harga_jual` |
| `direction` | `asc` (bawaan) atau `desc` |
| `page`, `per_page` | Halaman dan jumlah per halaman (bawaan 10, maksimal 100) |

`/api/v1/batch`

| Parameter | Isi |
|---|---|
| `cari` | Cari di nomor batch, nama, atau kode obat |
| `status` | `siap`, `kadaluarsa`, `ditahan`, atau `habis` |
| `page`, `per_page` | Sama seperti di atas |

Nilai yang tidak dikenal (misalnya `sort=harga_beli`) dibalas 422, bukan diabaikan.

Daftar yang dibagi per halaman memakai bentuk bawaan Laravel:

```json
{
  "data": [ ... ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 2, "per_page": 10, "total": 13 }
}
```

### Data yang hanya untuk pengelola

`harga_beli` (obat) dan `modal_per_satuan` (batch) hanya dikirim kepada user dengan
izin `obat.kelola` (admin). Untuk kasir, kedua kolom itu tidak ada sama sekali di JSON.

### Kolom batch yang membantu tampilan

| Kolom | Isi |
|---|---|
| `sisa_hari` | Hari menuju kadaluarsa, minus berarti sudah lewat, `null` bila tanpa tanggal |
| `tingkat_kadaluarsa` | `kadaluarsa`, `segera_habis`, `siapkan_retur`, atau `aman` |
| `boleh_dijual` | `true` bila aktif, stoknya ada, dan belum kadaluarsa |

Contoh balasan `/me`:

```json
{
  "data": {
    "id": 3,
    "name": "Kasir A",
    "email": "kasir@apotek.test",
    "apotek": "Apotek A",
    "jenis": "kasir",
    "peran": ["kasir_apotek_a"],
    "izin": ["dashboard.lihat", "obat.lihat"]
  }
}
```

`izin` dipakai untuk menyusun menu dan menyembunyikan tombol. Penjagaan sebenarnya
tetap di server: endpoint yang tidak diizinkan dibalas 403.

## Bentuk error

Semua error berbentuk JSON:

| Status | Arti | Isi |
|---|---|---|
| 401 | Belum login atau session habis | `{ "message": "Silakan masuk terlebih dahulu." }` |
| 403 | Tidak punya izin | `{ "message": "Anda tidak memiliki akses ke data ini." }` |
| 404 | Tidak ada, atau milik apotek lain | `{ "message": "Data tidak ditemukan." }` |
| 419 | Token CSRF kedaluwarsa | Panggil ulang `/sanctum/csrf-cookie`, lalu ulangi |
| 422 | Isian tidak valid | `{ "message": "...", "errors": { "email": ["..."] } }` |
