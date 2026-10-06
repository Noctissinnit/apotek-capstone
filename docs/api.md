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
