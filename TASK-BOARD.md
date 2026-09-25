# Task Board — Sistem Inventaris & Kasir Apotek

**Acuan:** Implementation Plan Capstone Project - Kelompok 1
**Terakhir diperbarui:** 22 September 2026

---

## Ringkasan Progress

| Bagian | Task | Progress |
|---|---:|---|
| **Keseluruhan** | 132 | `█░░░░░░░░░░░░░░░░░░░` **5%** |
| W1 — Requirement Validation | 8 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W2 — Requirement Baseline | 8 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W3 — Analysis & Design | 10 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W4 — Technical Foundation | 11 | `█████████░░░░░░░░░░░` 45% |
| W5 — Auth, Pharmacy & Inventory | 12 | `███░░░░░░░░░░░░░░░░░` 17% |
| GAP — Temuan Gap Analysis | 5 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W6 — Cashier & Transaction | 15 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W7 — Transaction ↔ Stock | 10 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W8 — History & Reporting | 9 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W9 — Hardening & Feature Freeze | 8 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W10 — System Testing | 12 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W11 — UAT & Deployment | 10 | `░░░░░░░░░░░░░░░░░░░░` 0% |
| W12 — Final Release | 14 | `░░░░░░░░░░░░░░░░░░░░` 0% |

**Jumlah per status:** ✅ DONE 0 · 🔍 REVIEW 8 · 🔄 IN PROGRESS 2 · 📋 TODO 44 · 🗂️ BACKLOG 78

**Test scenario:** 4 / 35 PASS · 1 sebagian · 30 belum dijalankan

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
| W4-01 | Repository Setup | Alexander | | 📋 TODO | Folder belum jadi repo git |
| W4-02 | Git Branching Strategy | Alexander | | 📋 TODO | |
| W4-03 | Project Structure | Alexander | | 🔍 REVIEW | Laravel 12 + Spatie Permission + Tailwind v4 |
| W4-04 | Database Initialization | Alexander | | 🔍 REVIEW | Critical. Migrasi jalan, tapi perlu disesuaikan setelah ERD final |
| W4-05 | Database Connection | Alexander | | 🔍 REVIEW | MySQL `apotek_capstone` |
| W4-06 | Base Frontend Layout | Pieter + Roman | | 🔍 REVIEW | Sidebar sesuai permission, navbar, footer |
| W4-07 | UI Component Foundation | Roman + Samuel | | 🔄 IN PROGRESS | Baru kartu, tabel, alert. Belum ada komponen Blade yang bisa dipakai ulang |
| W4-08 | Authentication Skeleton | Alexander | | 🔍 REVIEW | Critical. Sudah jadi login lengkap |
| W4-09 | Dummy/Test Data | Samuel | | 🔍 REVIEW | Seeder user, obat, supplier, pembelian. Perlu data 2 apotek (GAP-04) |
| W4-10 | Coding Convention | Alexander + All | | 📋 TODO | |
| W4-11 | Issue/Bug Board | Samuel | | 📋 TODO | |

**Acceptance Gate W4**
- [ ] Semua anggota dapat menjalankan project — *menunggu W4-01*
- [x] Database dapat digunakan
- [ ] Repository dan branching berjalan
- [x] Base application dapat dibuka
- [x] Login skeleton tersedia

## Minggu 5 — Authentication, Pharmacy & Inventory

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W5-01 | User/Account Data | Alexander | | 🔄 IN PROGRESS | User + role admin/kasir sudah ada. Relasi ke apotek belum ada |
| W5-02 | Pharmacy Data | Alexander | | 📋 TODO | Bergantung pada GAP-01 |
| W5-03 | Account → Pharmacy Relationship | Alexander | | 📋 TODO | Critical |
| W5-04 | Login Implementation | Alexander + Pieter | | 🔍 REVIEW | Pengalihan ke dashboard sesuai role, pembatasan percobaan login |
| W5-05 | Session Protection | Alexander | | 🔍 REVIEW | Middleware auth dan role, session diganti saat login dan dihapus saat logout |
| W5-06 | Pharmacy Data Isolation Backend | Alexander | | 📋 TODO | Critical. Wajib di backend, bukan hanya frontend |
| W5-07 | Inventory List UI | Pieter | | 📋 TODO | Route `obat.index` sudah disiapkan di sidebar |
| W5-08 | Add/Edit Item UI | Roman + Samuel | | 📋 TODO | |
| W5-09 | CRUD Item Backend | Alexander | | 📋 TODO | Critical. Model `Obat` sudah ada, controller belum |
| W5-10 | Search Item | Pieter | | 📋 TODO | |
| W5-11 | Inventory Validation | Alexander | | 📋 TODO | |
| W5-12 | Inventory Test | All | | 📋 TODO | |

## Temuan Gap Analysis (Tambahan)

Task yang muncul dari pengecekan kode terhadap Implementation Plan.

| ID | Task | Owner | Dependency | Status | Catatan |
|---|---|---|---|---|---|
| GAP-01 | Tambah entitas Apotek ke ERD & migrasi (`apotek`, `users.apotek_id`) | Alexander | W3-05 | 📋 TODO | Syarat untuk W5-02, W5-03, W5-06 |
| GAP-02 | Putuskan model stok per apotek (`obat.apotek_id` atau tabel stok obat × apotek) | Alexander + All | W2-04, W3-05 | 📋 TODO | Syarat untuk TC-STK-05 |
| GAP-03 | Validasi ke mitra: modul supplier & pembelian tetap dikerjakan atau masuk backlog | Alexander | W1-08 | 📋 TODO | Belum ada di Core MVP, tapi tabelnya sudah dibuat |
| GAP-04 | Sesuaikan seeder dengan data 2 apotek (Badan Sehat & Salam Sehat) | Samuel | GAP-01 | 📋 TODO | |
| GAP-05 | Lengkapi test TC-AUTH-05 (halaman yang dilindungi tidak bisa dibuka setelah logout) | Alexander | — | 📋 TODO | |

## Minggu 6 — Cashier & Sales Transaction

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W6-01 | Cashier Page | Pieter + Roman | | 🗂️ BACKLOG | |
| W6-02 | Product Search in Cashier | Pieter | | 🗂️ BACKLOG | |
| W6-03 | Add Item to Cart | Pieter + Alexander | | 🗂️ BACKLOG | |
| W6-04 | Quantity Input | Pieter | | 🗂️ BACKLOG | |
| W6-05 | Quantity Validation | Alexander | | 🗂️ BACKLOG | |
| W6-06 | Multi-Item Cart | Pieter + Samuel | | 🗂️ BACKLOG | |
| W6-07 | Subtotal Calculation | Alexander + Pieter | | 🗂️ BACKLOG | |
| W6-08 | Tax/Transaction Formula | Alexander + All | | 🗂️ BACKLOG | |
| W6-09 | Transaction Header | Alexander | | 🗂️ BACKLOG | Critical. Tabel penjualan belum ada |
| W6-10 | Transaction Detail | Alexander | | 🗂️ BACKLOG | Critical |
| W6-11 | Transaction Number | Alexander | | 🗂️ BACKLOG | |
| W6-12 | Date/Time Transaction | Alexander | | 🗂️ BACKLOG | |
| W6-13 | Checkout UI | Roman + Pieter | | 🗂️ BACKLOG | Critical |
| W6-14 | Transaction Result | Pieter | | 🗂️ BACKLOG | |
| W6-15 | Transaction Testing | All | | 🗂️ BACKLOG | |

## Minggu 7 — Transaction ↔ Stock Integration

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W7-01 | Transaction Success Handler | Alexander | | 🗂️ BACKLOG | |
| W7-02 | Stock Deduction | Alexander | | 🗂️ BACKLOG | Critical |
| W7-03 | Transaction Failure Handling | Alexander | | 🗂️ BACKLOG | Critical |
| W7-04 | Stock Consistency Check | Alexander | | 🗂️ BACKLOG | |
| W7-05 | Multi-Item Stock Update | Alexander | | 🗂️ BACKLOG | |
| W7-06 | Pharmacy-Specific Transaction | Alexander | | 🗂️ BACKLOG | |
| W7-07 | Pharmacy-Specific Stock Update | Alexander | | 🗂️ BACKLOG | Critical |
| W7-08 | Cross-Pharmacy Access Testing | All | | 🗂️ BACKLOG | Critical |
| W7-09 | End-to-End Testing | All | | 🗂️ BACKLOG | Critical |
| W7-10 | Core Flow Review | Alexander + All | | 🗂️ BACKLOG | |

## Minggu 8 — History & Reporting

| ID | Task | Owner | Reviewer | Status | Catatan |
|---|---|---|---|---|---|
| W8-01 | Transaction History Backend | Alexander | | 🗂️ BACKLOG | |
| W8-02 | Transaction History UI | Pieter | | 🗂️ BACKLOG | |
| W8-03 | Transaction Detail View | Pieter | | 🗂️ BACKLOG | |
| W8-04 | Inventory Report | Alexander + Pieter | | 🗂️ BACKLOG | |
| W8-05 | Transaction Report | Alexander + Pieter | | 🗂️ BACKLOG | |
| W8-06 | Report Filter | Pieter | | 🗂️ BACKLOG | |
| W8-07 | Report UI/UX | Roman + Samuel | | 🗂️ BACKLOG | |
| W8-08 | Report Data Isolation | Alexander | | 🗂️ BACKLOG | |
| W8-09 | History/Report Testing | All | | 🗂️ BACKLOG | |

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

### Pharmacy Isolation — 0/6
| ID | Skenario | Status |
|---|---|---|
| TC-PHARM-01 | User A melihat data A | ⬜ |
| TC-PHARM-02 | User B melihat data B | ⬜ |
| TC-PHARM-03 | User A mencoba URL/data B | ⬜ |
| TC-PHARM-04 | User B mencoba URL/data A | ⬜ |
| TC-PHARM-05 | Transaction A tidak muncul pada User B | ⬜ |
| TC-PHARM-06 | Stock A tidak berubah akibat transaction B | ⬜ |

### Inventory — 0/5
| ID | Skenario | Status |
|---|---|---|
| TC-INV-01 | Add item | ⬜ |
| TC-INV-02 | Edit item | ⬜ |
| TC-INV-03 | Delete item | ⬜ |
| TC-INV-04 | Search item | ⬜ |
| TC-INV-05 | Invalid input | ⬜ |

### Cashier — 0/9
| ID | Skenario | Status |
|---|---|---|
| TC-SAL-01 | One item | ⬜ |
| TC-SAL-02 | Multiple items | ⬜ |
| TC-SAL-03 | Quantity > 1 | ⬜ |
| TC-SAL-04 | Quantity > stock | ⬜ |
| TC-SAL-05 | Invalid quantity | ⬜ |
| TC-SAL-06 | Item unavailable | ⬜ |
| TC-SAL-07 | Cancel transaction | ⬜ |
| TC-SAL-08 | Successful checkout | ⬜ |
| TC-SAL-09 | Duplicate submit | ⬜ |

### Stock — 0/5
| ID | Skenario | Status |
|---|---|---|
| TC-STK-01 | Stock decreases after success | ⬜ |
| TC-STK-02 | Stock unchanged after failed transaction | ⬜ |
| TC-STK-03 | Stock cannot become negative | ⬜ |
| TC-STK-04 | Multi-item stock update | ⬜ |
| TC-STK-05 | Pharmacy-specific stock update | ⬜ |

### Report — 0/5
| ID | Skenario | Status |
|---|---|---|
| TC-REP-01 | Transaction history | ⬜ |
| TC-REP-02 | Transaction detail | ⬜ |
| TC-REP-03 | Inventory report | ⬜ |
| TC-REP-04 | Transaction report | ⬜ |
| TC-REP-05 | Pharmacy-specific report | ⬜ |

---

## Milestone Gate

| Gate | Waktu | Syarat | Status |
|---|---|---|---|
| G1 | End W1 | Proses dan masalah tervalidasi | ⬜ |
| G2 | End W2 | Requirement + scope baseline | ⬜ |
| G3 | End W3 | UML + ERD + UI/UX siap | ⬜ |
| G4 | End W4 | Development environment siap | 🟡 3/5 syarat terpenuhi |
| G5 | End W7 | Core transaction-stock flow stabil | ⬜ |
| G6 | End W9 | Feature freeze | ⬜ |
| G7 | End W10 | System testing selesai | ⬜ |
| G8 | End W11 | Release candidate + UAT feedback | ⬜ |
| G9 | End W12 | Final release | ⬜ |
