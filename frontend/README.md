# Frontend Apotek (Next.js)

Antarmuka Next.js untuk Sistem Informasi Apotek. Seluruh data diambil dari
API Laravel di root repo ini. Panduan endpoint ada di [`../docs/api.md`](../docs/api.md).

## Menjalankan secara lokal

1. Jalankan API Laravel dari root repo:

   ```bash
   php artisan serve            # http://localhost:8000
   ```

2. Siapkan dan jalankan frontend:

   ```bash
   cd frontend
   cp .env.example .env.local   # isi NEXT_PUBLIC_API_URL bila alamat API berbeda
   npm install
   npm run dev                  # http://localhost:3000
   ```

3. Buka http://localhost:3000 dan masuk dengan akun yang sama seperti di aplikasi web.

Bawaan `.env` Laravel sudah mengizinkan `http://localhost:3000`
(`FRONTEND_URL` dan `SANCTUM_STATEFUL_DOMAINS`), jadi tidak perlu diubah untuk lokal.

## Susunan

| Lokasi | Isi |
|---|---|
| `lib/api.ts` | Pemanggil API: cookie Sanctum, header CSRF, dan `ApiError` (pesan per kolom untuk 422) |
| `lib/auth.tsx` | `AuthProvider` dan `useAuth()`: user yang login, `login`, `logout`, `boleh(izin)` |
| `lib/use-api.ts` | `useApi()` untuk mengambil data GET dengan status memuat dan error |
| `lib/types.ts` | Bentuk data dari API |
| `app/login` | Halaman masuk |
| `app/(aplikasi)/layout.tsx` | Penjaga login, sidebar, dan navbar untuk semua halaman setelah masuk |
| `app/(aplikasi)/dashboard` | Dashboard admin atau kasir sesuai user |
| `app/(aplikasi)/obat` | Data Obat: contoh pola tabel dengan pencarian, urutan, dan pagination |
| `components/sidebar.tsx` | Menu, disaring menurut izin user |

## Menambah halaman

1. Buat `app/(aplikasi)/<nama>/page.tsx` dengan `"use client"`, ambil data dengan `useApi()`.
2. Tambahkan menunya di `components/sidebar.tsx`, lengkap dengan izin yang sama
   seperti middleware route API-nya.
3. Untuk mengubah data, panggil `api(path, { method: "POST", body })`. Kesalahan isian
   dibalas `ApiError` dengan `error.kolom("nama_kolom")`.

## Catatan

- Login diperiksa di browser karena cookie session milik domain Laravel. Penjagaan
  sebenarnya tetap di API: tanpa login semua endpoint membalas 401.
- Next.js versi ini punya perubahan besar dibanding versi lama. Baca panduan di
  `node_modules/next/dist/docs/` sebelum memakai fitur yang belum ada di sini.
