# Task Board — Sistem Inventaris & Kasir Apotek

**Acuan:** Implementation Plan Capstone Project - Kelompok 1
**Terakhir diperbarui:** 30 September 2026 (setelah penutupan bug pemisahan data apotek)

---

## Ringkasan Progress

| Bagian | Task | Progress |
|---|---:|---|
| **Keseluruhan** | 140 | `████████░░░░░░░░░░░░` **40%** |
| W1 — Requirement Validation | 8 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W2 — Requirement Baseline | 8 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W3 — Analysis & Design | 10 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W4 — Technical Foundation | 11 | `███████████████░░░░░` 77% |
| W5 — Auth, Pharmacy & Inventory | 12 | `███████████████████░` 94% |
| GAP — Temuan Gap Analysis | 7 | `██████░░░░░░░░░░░░░░` 29% |
| EX — Fitur Tambahan | 3 | `████████████████████` 100% |
| BUG — Bug (semua sudah ditutup) | 3 | `████████████████████` 100% |
| W6 — Cashier & Transaction | 15 | `████████████████░░░░` 80% |
| W7 — Transaction ↔ Stock | 10 | `█████████████████░░░` 83% |
| W8 — History & Reporting | 9 | `██████████████████░░` 92% |
| W9 — Hardening & Feature Freeze | 8 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W10 — System Testing | 12 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W11 — UAT & Deployment | 10 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W12 — Final Release | 14 | `░░░░░░░░░░░░░░░░░░░░` 0% |

**Jumlah per status:** ✅ DONE 49 · 🔍 REVIEW 5 · 🔄 IN PROGRESS 7 · 📋 TODO 35 · 🗂️ BACKLOG 44

**Test scenario:** 25 / 35 PASS · 4 sebagian · 0 gagal · 6 belum dijalankan

**Test otomatis:** 55 test, 315 assertion, seluruhnya lulus (`php artisan test`)

> ✅ **Tidak ada bug Critical yang terbuka.** Tiga bug pemisahan data apotek sudah
> ditutup pada 30 September 2026, dan Gate G5 (alur inti transaksi-stok) terpenuhi.

---

## Keterangan Status

| Penanda | Status | Arti | Bobot progress |
|---|---|---|---:|
| 🗂️ | BACKLOG | Belum masuk pengerjaan | 0% |
| 📋 | TODO | Siap dikerjakan | 0% |
| 🔄 | IN PROGRESS | Sedang dikerjakan | 50% |
| 🔍 | REVIEW | Menunggu review | 75% |
| 🧪 | TESTING | Sedang diuji | 75% |
| ⛔ | BLOCKED | Terhambat dependency/masalah | 0% |
| ✅ | DONE | Acceptance criteria dan Definition of Done terpenuhi | 100% |
| ⏸️ | DEFERRED | Ditunda | tidak dihitung |
| ❌ | REJECTED | Tidak sesuai scope | tidak dihitung |

**Rumus progress:** jumlah bobot semua task ÷ jumlah task (task DEFERRED dan REJECTED tidak dihitung).

**Bar progress:** 20 kotak, 1 kotak = 5%.

### Cara Update

1. Ubah penanda status task yang dikerjakan.
2. Isi kolom **Reviewer** saat task masuk 🔍 REVIEW.
3. Task baru boleh ✅ DONE jika memenuhi Definition of Done (bagian 5 Implementation Plan), termasuk **sudah direview anggota lain**.
4. Hitung ulang progress di bagian Ringkasan, lalu ubah tanggal "Terakhir diperbarui".

---

## Minggu 1 — Requirement Validation & Business Process

> Status W1–W3 belum diketahui dari kode. **Tim perlu mengisi sesuai kondisi sebenarnya.**

| ID | Task | Owner | Support | Status | Catatan |
|---|---|---|---|---|---|
| W1-01 | Persiapan Observasi | Alexander | All | 📋 TODO | |
| W1-02 | Observasi Inventaris | Samuel | Roman | 📋 TODO | |
| W1-03 | Observasi Kasir | Pieter | Alexander | 📋 TODO | |
| W1-04 | Validasi Data & Access Scope | Alexander | Pieter | 📋 TODO | Menentukan hubungan akun–apotek |
| W1-05 | Validasi Laporan | Samuel | All | 📋 TODO | |
| W1-06 | Validasi Member/Recommendation | Roman | Alexander | 📋 TODO | |
| W1-07 | As-Is Business Process | Pieter | All | 📋 TODO | |
| W1-08 | Requirement Gap List | Alexander | All | 📋 TODO | |

## Minggu 2 — Requirement Baseline & Scope Lock

| ID | Task | Owner | Support | Status | Catatan |
|---|---|---|---|---|---|
| W2-01 | Functional Requirement Finalization | Alexander | Pieter | 📋 TODO | Critical |
| W2-02 | Non-Functional Requirement | Alexander | Roman | 📋 TODO | |
| W2-03 | Use Case List | Pieter | — | 📋 TODO | |
| W2-04 | Business Rules | Alexander | All | 📋 TODO | Critical |
| W2-05 | MoSCoW Prioritization | Alexander | All | 📋 TODO | |
| W2-06 | Acceptance Criteria | Pieter | Samuel | 📋 TODO | |
| W2-07 | Traceability Matrix | Samuel | Alexander | 📋 TODO | |
| W2-08 | SRS/PRD Baseline Review | All | — | 📋 TODO | |

## Minggu 3 — System Analysis & Design

| ID | Task | Owner | Support | Status | Catatan |
|---|---|---|---|---|---|
| W3-01 | Use Case Diagram | Pieter | — | 📋 TODO | |
| W3-02 | Activity Diagram | Pieter | — | 📋 TODO | |
| W3-03 | Sequence Diagram | Pieter | Alexander | 📋 TODO | |
| W3-04 | Class Diagram | Pieter | Alexander | 📋 TODO | |
| W3-05 | ERD | Alexander | — | 📋 TODO | Critical. Belum ada entitas Apotek dan Transaksi Penjualan (lihat GAP-01, GAP-02) |
| W3-06 | Database Design | Alexander | — | 📋 TODO | Critical |
| W3-07 | User Flow | Roman | — | 📋 TODO | |
| W3-08 | Wireframe | Roman | Samuel | 📋 TODO | |
| W3-09 | UI/UX Review | Samuel | Roman | 📋 TODO | |
| W3-10 | Architecture Design | Alexander | — | 📋 TODO | |

## Minggu 4 — Technical Foundation

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W4-01 | Repository Setup | Alexander | | ✅ DONE | Repo GitHub `Noctissinnit/apotek-capstone`, branch main/development/feature |
| W4-02 | Git Branching Strategy | Alexander | | 🔄 IN PROGRESS | Alur branch jalan, tapi penamaan masih campur (`CRUD_Obat` vs `feature/kasir`) dan belum ada aturan tertulis |
| W4-03 | Project Structure | Alexander | | ✅ DONE | Laravel 12 + Spatie Permission + Tailwind v4, dipakai seluruh anggota |
| W4-04 | Database Initialization | Alexander | | ✅ DONE | Critical. 11 migrasi jalan. Perlu penyesuaian saat entitas Apotek masuk (GAP-01) |
| W4-05 | Database Connection | Alexander | | ✅ DONE | MySQL `apotek_capstone` |
| W4-06 | Base Frontend Layout | Pieter + Roman | | ✅ DONE | Layout, sidebar sesuai hak akses, navbar, flash message, responsif |
| W4-07 | UI Component Foundation | Roman + Samuel | | ✅ DONE | Class komponen di `app.css`: `form-*`, `stat-card`, `panel-*`, `badge-*`, `btn-*`, `table-*`, `empty-state` |
| W4-08 | Authentication Skeleton | Alexander | | ✅ DONE | Critical. Login penuh + 11 test |
| W4-09 | Dummy/Test Data | Samuel | | ✅ DONE | Seeder role, user, kategori, obat, supplier, pembelian. Perlu data 2 apotek nanti (GAP-04) |
| W4-10 | Coding Convention | Alexander + All | | 📋 TODO | Belum ada dokumen. Termasuk aturan wajib `npm run build` sebelum push (EX-03) |
| W4-11 | Issue/Bug Board | Samuel | | 📋 TODO | |

**Acceptance Gate W4 — TERPENUHI**
- [x] Semua anggota dapat menjalankan project
- [x] Database dapat digunakan
- [x] Repository dan branching berjalan — *aturan penamaan branch belum ditulis (W4-02)*
- [x] Base application dapat dibuka
- [x] Login skeleton tersedia

## Minggu 5 — Authentication, Pharmacy & Inventory

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W5-01 | User/Account Data | Alexander | | ✅ DONE | CRUD user + alamat/kontak + 4 role (admin/kasir × Apotek A/B) |
| W5-02 | Pharmacy Data | Alexander | | 🔄 IN PROGRESS | Apotek disimpan sebagai teks di kolom `users.apotek` dan `obat.apotek`, belum ada tabel `apotek` tersendiri (lihat GAP-01) |
| W5-03 | Account → Pharmacy Relationship | Alexander | | ✅ DONE | Critical. Tiap user punya apotek + role sesuai apoteknya; login menolak bila role dan apotek tidak cocok. Teruji |
| W5-04 | Login Implementation | Alexander + Pieter | | ✅ DONE | Pengalihan sesuai role, batas 5 kali percobaan, logout. Teruji |
| W5-05 | Session Protection | Alexander | | ✅ DONE | Middleware `auth`/`role`/`permission`, session diganti saat login dan dihapus saat logout |
| W5-06 | Pharmacy Data Isolation Backend | Alexander | | ✅ DONE | Critical. Obat, kasir, riwayat, dan laporan semuanya disaring per apotek. Teruji lewat `ObatTest` dan `IsolasiApotekTest` |
| W5-07 | Inventory List UI | Pieter | | ✅ DONE | Tabel obat + urut semua kolom + saring kategori/satuan + 10 data per halaman. Teruji |
| W5-08 | Add/Edit Item UI | Roman + Samuel | | ✅ DONE | Form tambah, ubah, detail, hapus |
| W5-09 | CRUD Item Backend | Alexander | | ✅ DONE | Critical. `ObatController` + `KategoriController`. Teruji |
| W5-10 | Search Item | Pieter | | 🔍 REVIEW | Cari kode/nama/kategori jalan, penyaringan kategori & satuan sudah teruji, **pencarian teks belum ada test** (TC-INV-04) |
| W5-11 | Inventory Validation | Alexander | | ✅ DONE | Validasi form + kode obat unik. Teruji |
| W5-12 | Inventory Test | All | | ✅ DONE | `ObatTest` 10 test, `KategoriTest` 3 test, termasuk test pemisahan data antar apotek |

## Temuan Gap Analysis (Tambahan)

Task yang muncul dari pengecekan kode terhadap Implementation Plan.

| ID | Task | Owner | Dependency | Status | Catatan |
|---|---|---|---|---|---|
| GAP-01 | Jadikan apotek sebagai tabel tersendiri, bukan kolom teks | Alexander | W3-05 | 📋 TODO | Sekarang `users.apotek` dan `obat.apotek` berisi teks "Apotek A"/"Apotek B". Cukup untuk MVP, tapi rawan salah ketik dan sulit menambah apotek ketiga |
| GAP-02 | Putuskan model stok per apotek | Alexander + All | W2-04 | ✅ DONE | Diputuskan: tiap baris obat milik satu apotek, stok ikut di baris itu |
| GAP-03 | Validasi ke mitra: modul supplier & pembelian tetap dikerjakan atau masuk backlog | Alexander | W1-08 | 📋 TODO | Belum ada di Core MVP, tapi tabelnya sudah dibuat |
| GAP-04 | Sesuaikan seeder dengan data 2 apotek | Samuel | GAP-01 | ✅ DONE | 4 akun (admin & kasir tiap apotek), 12 obat dibagi 6-6. Nama apotek masih "Apotek A"/"Apotek B", belum nama asli mitra |
| GAP-05 | Lengkapi test TC-AUTH-05 (halaman terlindungi tidak bisa dibuka setelah logout) | Alexander | — | 📋 TODO | |
| GAP-06 | Hapus atau perbaiki `resources/views/kasir/obat/index.blade.php` | Pieter | — | 📋 TODO | View rusak: memanggil route `kasir.obat.index` yang tidak ada, dan tidak dipakai controller mana pun |
| GAP-07 | Bersihkan baris `Co-Authored-By: Claude` dari 9 commit lama | Alexander | W9-08 | 📋 TODO | Sudah terlanjur ada di GitHub pada `main`, `development`, dan 6 branch fitur. Perlu tulis ulang sejarah + force-push, jadi **dikerjakan setelah semua branch digabung dan tidak ada yang sedang bekerja**. Buat tag cadangan dulu, lalu seluruh anggota re-clone. Commit baru sudah tidak ditandai lagi |

## Bug (Riwayat Perbaikan)

| ID | Bug | Severity | Owner | Status | Catatan |
|---|---|---|---|---|---|
| BUG-01 | Kasir dapat melihat dan menjual obat milik apotek lain | **Critical** | Alexander | ✅ DONE | Daftar obat, keranjang, dan checkout kini disaring per apotek. Penyaringan diulang saat checkout agar keranjang lama tetap ditolak. Teruji (`IsolasiApotekTest`) |
| BUG-02 | Riwayat penjualan belum disaring per apotek | **Critical** | Alexander | ✅ DONE | Riwayat, total harian, laporan PDF rentang, struk per transaksi, dan dashboard kasir semuanya disaring per apotek. Teruji |
| BUG-03 | Transaksi penjualan tidak mencatat apotek | High | Alexander | ✅ DONE | Kolom `apotek` ditambahkan ke tabel `penjualan` dan diisi saat checkout. Transaksi lama diisi otomatis mengikuti apotek kasirnya |

## Fitur Tambahan di Luar Rencana Awal

Sudah dikerjakan tetapi tidak ada di daftar task Implementation Plan.

| ID | Task | Owner | Status | Catatan |
|---|---|---|---|---|
| EX-01 | CRUD Kategori Obat | Alexander | ✅ DONE | Tabel `kategori` + relasi ke obat, kategori yang masih dipakai tidak bisa dihapus. Teruji (`KategoriTest`) |
| EX-02 | Menu sidebar mengikuti role & permission | Alexander | ✅ DONE | Menu yang tidak boleh diakses disembunyikan. Teruji (`SidebarTest`), termasuk test yang membuka semua menu untuk memastikan tidak ada yang berujung 403 |
| EX-03 | Deploy tanpa npm | Alexander | ✅ DONE | Hasil build CSS/JS ikut disimpan di repo, hosting cukup upload. Langkah hosting ditulis di `README.md`. Mendukung W11-07 dan W11-09 |

## Minggu 6 — Cashier & Sales Transaction

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W6-01 | Cashier Page | Pieter + Roman | | ✅ DONE | Layar POS dua kolom: daftar obat + keranjang lengkap dengan total dan tombol bayar |
| W6-02 | Product Search in Cashier | Pieter | | 🔍 REVIEW | Cari nama/kode obat sudah jalan, belum ada test |
| W6-03 | Add Item to Cart | Pieter + Alexander | | ✅ DONE | Keranjang disimpan di session. Teruji |
| W6-04 | Quantity Input | Pieter | | 🔄 IN PROGRESS | Backend sudah menerima jumlah, tetapi di layar kasir menambah hanya bisa satuan (tekan Tambah berulang). Belum ada kolom jumlah dan tombol ubah jumlah |
| W6-05 | Quantity Validation | Alexander | | ✅ DONE | Penambahan ditolak bila melebihi stok. Teruji |
| W6-06 | Multi-Item Cart | Pieter + Samuel | | 🔍 REVIEW | Keranjang menampung banyak jenis obat, tetapi belum ada test checkout dengan lebih dari satu jenis obat (TC-SAL-02) |
| W6-07 | Subtotal Calculation | Alexander + Pieter | | ✅ DONE | Hitungan memakai satuan sen agar pembulatan tidak meleset |
| W6-08 | Tax/Transaction Formula | Alexander + All | | 📋 TODO | Belum ada pajak maupun diskon. Perlu keputusan mitra dulu |
| W6-09 | Transaction Header | Alexander | | ✅ DONE | Critical. Tabel `penjualan`. Teruji |
| W6-10 | Transaction Detail | Alexander | | ✅ DONE | Critical. Tabel `detail_penjualan`. Teruji |
| W6-11 | Transaction Number | Alexander | | ✅ DONE | Format `TRX-<tanggal>-<jam>-<5 huruf acak>` |
| W6-12 | Date/Time Transaction | Alexander | | ✅ DONE | |
| W6-13 | Checkout UI | Roman + Pieter | | ✅ DONE | Critical. Ringkasan dan tombol Proses Pembayaran di layar transaksi maupun halaman keranjang |
| W6-14 | Transaction Result | Pieter | | 🔄 IN PROGRESS | Setelah bayar baru muncul pesan berisi no faktur dan total. Struk tersedia sebagai PDF di halaman riwayat, tetapi belum ada halaman hasil transaksi tersendiri |
| W6-15 | Transaction Testing | All | | 🔄 IN PROGRESS | 4 test di `KasirTransaksiTest`. Belum diuji: pembatalan transaksi, kirim ganda, obat tidak ditemukan |

## Minggu 7 — Transaction ↔ Stock Integration

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W7-01 | Transaction Success Handler | Alexander | | ✅ DONE | Simpan transaksi, kosongkan keranjang, tampilkan pesan hasil |
| W7-02 | Stock Deduction | Alexander | | ✅ DONE | Critical. Stok berkurang saat checkout. Teruji |
| W7-03 | Transaction Failure Handling | Alexander | | ✅ DONE | Critical. Semua dibungkus satu transaksi database, stok tidak berubah bila gagal. Teruji |
| W7-04 | Stock Consistency Check | Alexander | | ✅ DONE | Baris obat dikunci (`lockForUpdate`) dan stok diperiksa ulang sebelum disimpan |
| W7-05 | Multi-Item Stock Update | Alexander | | 🔍 REVIEW | Sudah jalan, belum ada test khusus untuk transaksi banyak jenis obat |
| W7-06 | Pharmacy-Specific Transaction | Alexander | | ✅ DONE | Transaksi mencatat apoteknya sendiri, dan obat apotek lain ditolak saat checkout |
| W7-07 | Pharmacy-Specific Stock Update | Alexander | | ✅ DONE | Critical. Stok yang berkurang dipastikan milik apotek kasir. Teruji |
| W7-08 | Cross-Pharmacy Access Testing | All | | ✅ DONE | Critical. 7 test di `IsolasiApotekTest`, termasuk percobaan lewat URL langsung |
| W7-09 | End-to-End Testing | All | | 🔄 IN PROGRESS | Alur login sampai stok berkurang sudah teruji. Belum diuji utuh bersama laporan |
| W7-10 | Core Flow Review | Alexander + All | | 📋 TODO | Tunggu BUG-01 s/d BUG-03 selesai |

## Minggu 8 — History & Reporting

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W8-01 | Transaction History Backend | Alexander | | ✅ DONE | `PenjualanController`, 5 transaksi per halaman. Teruji |
| W8-02 | Transaction History UI | Pieter | | ✅ DONE | Tabel riwayat + total hari ini + tampilan kartu di layar kecil |
| W8-03 | Transaction Detail View | Pieter | | ✅ DONE | Rincian obat per transaksi + unduh PDF per transaksi. Teruji |
| W8-04 | Inventory Report | Alexander + Pieter | | 🔄 IN PROGRESS | Halaman Monitoring Stok sudah ada (stok menipis + mendekati kadaluarsa), tetapi belum bisa diunduh sebagai laporan |
| W8-05 | Transaction Report | Alexander + Pieter | | ✅ DONE | Laporan PDF gabungan per rentang tanggal. Teruji |
| W8-06 | Report Filter | Pieter | | ✅ DONE | Saring berdasarkan rentang tanggal, rentang tidak wajar ditolak. Teruji |
| W8-07 | Report UI/UX | Roman + Samuel | | 🔍 REVIEW | Tampilan PDF sudah jadi, belum ditinjau bersama |
| W8-08 | Report Data Isolation | Alexander | | ✅ DONE | Riwayat, total harian, dan PDF hanya memuat data apotek sendiri. Teruji |
| W8-09 | History/Report Testing | All | | ✅ DONE | 7 test di `PenjualanTest` |

## Minggu 9 — Hardening, Optional Feature & Feature Freeze

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W9-01 | MVP Gap Analysis | Alexander + All | | 🗂️ BACKLOG | |
| W9-02 | Requirement Traceability Review | Samuel | | 🗂️ BACKLOG | |
| W9-03 | Security Review | Alexander + All | | 🗂️ BACKLOG | Critical |
| W9-04 | UX Review | Roman + Samuel | | 🗂️ BACKLOG | |
| W9-05 | Validation/Error Handling Review | Alexander + Pieter | | 🗂️ BACKLOG | |
| W9-06 | Bug Fix Sprint | All | | 🗂️ BACKLOG | |
| W9-07 | Member/Recommendation Decision | Alexander + All | | 🗂️ BACKLOG | |
| W9-08 | Feature Freeze | Alexander | | 🗂️ BACKLOG | |

## Minggu 10 — System Testing & UAT Preparation

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W10-01 | Test Plan Final | Samuel + All | | 🗂️ BACKLOG | |
| W10-02 | Functional Testing | All | | 🗂️ BACKLOG | |
| W10-03 | Integration Testing | All | | 🗂️ BACKLOG | |
| W10-04 | Authentication Testing | Alexander + All | | 🗂️ BACKLOG | Sebagian sudah ada di `tests/Feature/LoginTest.php` |
| W10-05 | Pharmacy Isolation Testing | Alexander + All | | 🗂️ BACKLOG | Critical |
| W10-06 | Inventory Testing | Pieter + Samuel | | 🗂️ BACKLOG | |
| W10-07 | Transaction Testing | Pieter + Alexander | | 🗂️ BACKLOG | |
| W10-08 | Stock Consistency Testing | Alexander + All | | 🗂️ BACKLOG | Critical |
| W10-09 | Error Handling Testing | Samuel + All | | 🗂️ BACKLOG | |
| W10-10 | Regression Testing | All | | 🗂️ BACKLOG | |
| W10-11 | UAT Scenario Preparation | Samuel + Pieter | | 🗂️ BACKLOG | |
| W10-12 | UAT Environment Preparation | Alexander | | 🗂️ BACKLOG | |

## Minggu 11 — UAT, Bug Fixing & Deployment

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W11-01 | UAT | All | | 🗂️ BACKLOG | Critical |
| W11-02 | Record UAT Feedback | Samuel | | 🗂️ BACKLOG | |
| W11-03 | Critical Bug Fix | Alexander + All | | 🗂️ BACKLOG | Critical |
| W11-04 | Frontend Bug Fix | Pieter | | 🗂️ BACKLOG | |
| W11-05 | UI/UX Fix | Roman + Samuel | | 🗂️ BACKLOG | |
| W11-06 | Regression Testing | All | | 🗂️ BACKLOG | |
| W11-07 | Production/Hosting Setup | Alexander | | 🗂️ BACKLOG | |
| W11-08 | Deployment Trial | Alexander + Samuel | | 🗂️ BACKLOG | Critical |
| W11-09 | Deployment Documentation | Samuel + Alexander | | 🗂️ BACKLOG | |
| W11-10 | User Guide Draft | Samuel + Roman | | 🗂️ BACKLOG | |

## Minggu 12 — Final Release, Documentation & Presentation

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W12-01 | Final Regression Test | All | | 🗂️ BACKLOG | Critical |
| W12-02 | Final Acceptance Checklist | Alexander | | 🗂️ BACKLOG | |
| W12-03 | Final Deployment | Alexander | | 🗂️ BACKLOG | Critical |
| W12-04 | Database Backup | Alexander | | 🗂️ BACKLOG | |
| W12-05 | Source Code Finalization | All | | 🗂️ BACKLOG | |
| W12-06 | Technical Documentation | Alexander + Pieter | | 🗂️ BACKLOG | |
| W12-07 | User Documentation | Samuel + Roman | | 🗂️ BACKLOG | |
| W12-08 | Final UML/ERD Update | Pieter | | 🗂️ BACKLOG | |
| W12-09 | Final UI/UX Documentation | Roman | | 🗂️ BACKLOG | |
| W12-10 | Test Report | Samuel + All | | 🗂️ BACKLOG | |
| W12-11 | Presentation Slides | All | | 🗂️ BACKLOG | |
| W12-12 | Demo Script | Pieter + Roman | | 🗂️ BACKLOG | |
| W12-13 | Final Presentation Rehearsal | All | | 🗂️ BACKLOG | |
| W12-14 | Final Project Archive | Alexander | | 🗂️ BACKLOG | |

---

## Status Test Scenario

Penanda: ✅ PASS · ❌ FAIL · 🟡 SEBAGIAN · ⬜ BELUM DIJALANKAN

### Authentication — 4/5
| ID | Skenario | Status | Bukti |
|---|---|---|---|
| TC-AUTH-01 | Login valid | ✅ PASS | `LoginTest`: admin & kasir login diarahkan ke dashboard |
| TC-AUTH-02 | Login invalid | ✅ PASS | `LoginTest`: password salah ditolak |
| TC-AUTH-03 | Access without login | ✅ PASS | `LoginTest`: tamu diarahkan ke login |
| TC-AUTH-04 | Logout | ✅ PASS | `LoginTest::test_logout` |
| TC-AUTH-05 | Session after logout | 🟡 SEBAGIAN | Baru cek user keluar, belum cek akses halaman setelah logout (GAP-05) |

### Pharmacy Isolation — 6/6
| ID | Skenario | Status | Bukti |
|---|---|---|---|
| TC-PHARM-01 | User A melihat data A | ✅ PASS | `ObatTest::test_user_hanya_dapat_melihat_obat_dari_apoteknya` |
| TC-PHARM-02 | User B melihat data B | ✅ PASS | test yang sama |
| TC-PHARM-03 | User A mencoba URL/data B | ✅ PASS | membuka obat milik apotek lain menghasilkan 404 |
| TC-PHARM-04 | User B mencoba URL/data A | ✅ PASS | sama |
| TC-PHARM-05 | Transaction A tidak muncul pada User B | ✅ PASS | `IsolasiApotekTest::test_riwayat_hanya_menampilkan_transaksi_apoteknya` |
| TC-PHARM-06 | Stock A tidak berubah akibat transaction B | ✅ PASS | `IsolasiApotekTest::test_checkout_menolak_obat_apotek_lain_dan_stoknya_tidak_berubah` |

### Inventory — 3/5
| ID | Skenario | Status | Bukti |
|---|---|---|---|
| TC-INV-01 | Add item | ✅ PASS | `ObatTest::test_admin_dapat_menjalankan_crud_obat` |
| TC-INV-02 | Edit item | ✅ PASS | test yang sama |
| TC-INV-03 | Delete item | ✅ PASS | test yang sama |
| TC-INV-04 | Search item | 🟡 SEBAGIAN | penyaringan kategori & satuan teruji, pencarian teks belum (W5-10) |
| TC-INV-05 | Invalid input | 🟡 SEBAGIAN | baru kode obat duplikat yang diuji |

> Di luar daftar Implementation Plan, sudah ada juga test untuk CRUD kategori
> (`KategoriTest`) dan untuk menu sidebar sesuai hak akses (`SidebarTest`).

### Cashier — 4/9
| ID | Skenario | Status | Bukti |
|---|---|---|---|
| TC-SAL-01 | One item | ✅ PASS | `KasirTransaksiTest` |
| TC-SAL-02 | Multiple items | ⬜ | belum diuji |
| TC-SAL-03 | Quantity > 1 | ✅ PASS | checkout 2 item |
| TC-SAL-04 | Quantity > stock | ✅ PASS | penambahan ditolak, stok tidak berubah |
| TC-SAL-05 | Invalid quantity | ⬜ | belum diuji |
| TC-SAL-06 | Item unavailable | ⬜ | belum diuji |
| TC-SAL-07 | Cancel transaction | ⬜ | fitur batal transaksi belum ada |
| TC-SAL-08 | Successful checkout | ✅ PASS | transaksi tersimpan |
| TC-SAL-09 | Duplicate submit | ⬜ | belum diuji |

### Stock — 4/5
| ID | Skenario | Status | Bukti |
|---|---|---|---|
| TC-STK-01 | Stock decreases after success | ✅ PASS | `KasirTransaksiTest` |
| TC-STK-02 | Stock unchanged after failed transaction | ✅ PASS | `KasirTransaksiTest` |
| TC-STK-03 | Stock cannot become negative | ✅ PASS | penambahan melebihi stok ditolak |
| TC-STK-04 | Multi-item stock update | ⬜ | belum diuji |
| TC-STK-05 | Pharmacy-specific stock update | ✅ PASS | `IsolasiApotekTest` |

### Report — 4/5
| ID | Skenario | Status | Bukti |
|---|---|---|---|
| TC-REP-01 | Transaction history | ✅ PASS | `PenjualanTest` |
| TC-REP-02 | Transaction detail | ✅ PASS | rincian + PDF per transaksi |
| TC-REP-03 | Inventory report | 🟡 SEBAGIAN | halaman Monitoring Stok ada, belum bisa diunduh (W8-04) |
| TC-REP-04 | Transaction report | ✅ PASS | PDF rentang tanggal |
| TC-REP-05 | Pharmacy-specific report | ✅ PASS | riwayat, total harian, dan PDF disaring per apotek |
---

## Milestone Gate

| Gate | Waktu | Syarat | Status |
|---|---|---|---|
| G1 | End W1 | Proses dan masalah tervalidasi | ⬜ |
| G2 | End W2 | Requirement + scope baseline | ⬜ |
| G3 | End W3 | UML + ERD + UI/UX siap | ⬜ |
| G4 | End W4 | Development environment siap | ✅ TERPENUHI |
| G5 | End W7 | Core transaction-stock flow stabil | ✅ TERPENUHI — alur jual sampai stok berkurang jalan, teruji, dan sudah terpisah per apotek. Sisa W7-10 hanya review bersama |
| G6 | End W9 | Feature freeze | ⬜ |
| G7 | End W10 | System testing selesai | ⬜ |
| G8 | End W11 | Release candidate + UAT feedback | ⬜ |
| G9 | End W12 | Final release | ⬜ |
