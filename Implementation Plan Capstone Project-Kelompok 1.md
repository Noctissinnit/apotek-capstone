# Implementation Plan & Task Breakdown
## Optimasi dan Digitalisasi Penerapan Sistem Inventaris dan Kasir pada Apotek

**Mitra:** PT. Indah Berkat Usaha  
**Target:** Apotek Badan Sehat & Apotek Salam Sehat  
**Durasi:** ±12 minggu / 1 semester  
**Tim:** 4 mahasiswa  
**Acuan:** Proposal/SRS, PRD, dan Workflow Capstone 12 Minggu  
**Status:** Implementation Baseline

---

# 1. Tujuan

Dokumen ini menerjemahkan PRD dan workflow proyek menjadi **Work Breakdown Structure (WBS)**, implementation plan, serta task yang dapat langsung diassign dan dilacak selama ±12 minggu.

Prinsip utama: **MVP yang stabil lebih penting daripada banyaknya fitur.** Alur inti yang harus menjadi fokus adalah:

```text
Authentication
 ↓
Account → Pharmacy
 ↓
Pharmacy Data Isolation
 ↓
Inventory / Item
 ↓
Stock
 ↓
Cashier
 ↓
Transaction
 ↓
Stock Update
 ↓
History / Report
 ↓
Testing
 ↓
UAT
 ↓
Deployment
```

---

# 2. Baseline Implementasi

## 2.1 Core MVP

1. Login/logout dan session.
2. Account terasosiasi dengan satu apotek.
3. Pembatasan akses data berdasarkan apotek.
4. Data apotek.
5. CRUD obat/barang.
6. Pencarian barang.
7. Monitoring/pengelolaan stok.
8. Kasir dan keranjang transaksi.
9. Transaksi multi-item.
10. Transaksi obat eceran.
11. Validasi quantity terhadap stok.
12. Perhitungan subtotal dan total sesuai aturan bisnis final.
13. Penyimpanan transaksi dan detail transaksi.
14. Pengurangan stok setelah transaksi berhasil.
15. Riwayat transaksi.
16. Laporan yang disepakati.
17. Validation/error handling.
18. Security/access testing.
19. Deployment.

## 2.2 Conditional Backlog

- Member/customer.
- Rekomendasi customer/obat.
- Bulk upload data awal jika tervalidasi.
- Laporan tambahan.

Fitur conditional tidak boleh mengganggu core MVP.

## 2.3 Out of Scope

- Distribusi obat PT ke apotek lain.
- Produksi obat.
- Akuntansi perusahaan secara keseluruhan.
- Payroll/penggajian.
- Operasional PT di luar dua apotek.
- Validasi resep digital.
- Pemesanan otomatis supplier.
- Payment gateway.
- Integrasi BPJS.
- Integrasi eksternal tanpa API/izin yang diperlukan.
- Aplikasi mobile native sebagai bagian MVP.

---

# 3. Struktur Peran Tim

| ID | Anggota | Role Utama | Fokus |
|---|---|---|---|
| TM-01 | Alexander Arthur Bimo Satriaji | Project Manager + Backend Developer | Backend, database, integration, coordination |
| TM-02 | Pieter Alva Pradana | Frontend Developer + UML | Frontend, UML, frontend integration |
| TM-03 | Roman Adi Surya | Frontend Developer + UI/UX | UI/UX, frontend, usability |
| TM-04 | Samuel Latuihamallo | UI/UX Support | UI support, documentation, test/data support |
| ALL | Seluruh anggota | Testing + Review | Functional, integration, regression, UAT support |

Owner tidak berarti bekerja sendirian. Task kritis minimal memiliki **owner + reviewer**.

---

# 4. Status Task

| Status | Arti |
|---|---|
| BACKLOG | Belum masuk pengerjaan |
| TODO | Siap dikerjakan |
| IN PROGRESS | Sedang dikerjakan |
| REVIEW | Menunggu review |
| TESTING | Sedang diuji |
| BLOCKED | Terhambat dependency/masalah |
| DONE | Acceptance criteria terpenuhi |
| DEFERRED | Ditunda |
| REJECTED | Tidak sesuai scope |

---

# 5. Definition of Done

Task baru boleh **DONE** jika:

- [ ] Requirement/task jelas.
- [ ] Implementasi selesai.
- [ ] Tidak ada bagian utama yang masih dummy.
- [ ] Validasi input tersedia jika diperlukan.
- [ ] Error handling tersedia jika diperlukan.
- [ ] Terintegrasi dengan komponen terkait.
- [ ] Test case tersedia.
- [ ] Test berhasil.
- [ ] Code direview.
- [ ] Tidak menyebabkan regression.
- [ ] Dokumentasi diperbarui jika diperlukan.
- [ ] Acceptance criteria terpenuhi.

---

# 6. WBS Utama

```text
01 Project Initiation & Requirement Validation
02 Product/Requirement Baseline
03 System Analysis & Design
04 Technical Foundation
05 Authentication & Pharmacy Access
06 Inventory & Master Item
07 Stock Management
08 Cashier & Sales Transaction
09 Transaction-Stock Integration
10 History & Reporting
11 Optional/Conditional Features
12 System Testing
13 UAT & Stabilization
14 Deployment
15 Final Documentation & Presentation
```

---

# 7. Implementation Plan 12 Minggu

## Minggu 1 — Requirement Validation & Business Process

**Objective:** memahami kondisi aktual kedua apotek sebelum requirement dan desain teknis dikunci.

### Tasks

**W1-01 — Persiapan Observasi**  
Owner: Alexander | Support: All
- Review proposal, PRD, dan daftar pertanyaan.
- Tentukan informasi wajib.
- Siapkan template observasi/wawancara.

Output: observation checklist dan interview template.

**W1-02 — Observasi Inventaris**  
Owner: Samuel | Support: Roman
- Amati pencatatan barang/stok.
- Amati bagaimana stok bertambah/berkurang.
- Catat pelaksana, dokumen, dan masalah aktual.

Output: As-Is inventory flow.

**W1-03 — Observasi Kasir**  
Owner: Pieter | Support: Alexander
- Amati proses pembelian.
- Fokus pada transaksi eceran, quantity, harga, pembayaran, pencatatan, pembatalan/retur jika ada.

Output: As-Is cashier flow.

**W1-04 — Validasi Data & Access Scope**  
Owner: Alexander | Support: Pieter
- Konfirmasi akun, pengguna, hubungan akun-apotek, dan data yang harus dipisahkan.

Output: access requirement notes.

**W1-05 — Validasi Laporan**  
Owner: Samuel | Support: All
- Identifikasi laporan yang benar-benar digunakan, siapa pengguna, periode, dan informasi wajib.

Output: report requirement list.

**W1-06 — Validasi Member/Recommendation**  
Owner: Roman | Support: Alexander
- Konfirmasi tujuan, data, akses, proses, dan status MVP.

Output: MVP / Could Have / Deferred decision.

**W1-07 — As-Is Business Process**  
Owner: Pieter | Support: All
- Gabungkan flow inventory, stock, cashier, transaction, dan access.

**W1-08 — Requirement Gap List**  
Owner: Alexander | Support: All
- Pisahkan confirmed, assumption, unresolved, dan out of scope.

### Gate W1
Masalah, proses, dan kebutuhan awal telah tervalidasi.

---

## Minggu 2 — Requirement Baseline & Scope Lock

**Objective:** mengubah hasil observasi menjadi requirement yang jelas dan terukur.

### Tasks

**W2-01 — Functional Requirement Finalization**  
Owner: Alexander | Support: Pieter

**W2-02 — Non-Functional Requirement**  
Owner: Alexander | Support: Roman

**W2-03 — Use Case List**  
Owner: Pieter

Minimal: Login, Logout, CRUD Barang, Stok, Transaksi, Riwayat, Laporan.

**W2-04 — Business Rules**  
Owner: Alexander | Support: All
- Pharmacy scope.
- Stock rules.
- Transaction rules.
- Quantity.
- Total.
- Failure handling.

**W2-05 — MoSCoW Prioritization**  
Owner: Alexander | Support: All

**W2-06 — Acceptance Criteria**  
Owner: Pieter | Support: Samuel

**W2-07 — Traceability Matrix**  
Owner: Samuel | Support: Alexander

```text
Problem → Requirement → Feature → Task → Test Case
```

**W2-08 — SRS/PRD Baseline Review**  
Owner: All

### Gate W2
Requirement dan scope MVP dikunci. Perubahan berikutnya harus melalui change request.

---

## Minggu 3 — System Analysis & Design

**Objective:** menerjemahkan requirement menjadi desain yang siap diimplementasikan.

### Tasks

**W3-01 — Use Case Diagram**  
Owner: Pieter

**W3-02 — Activity Diagram**  
Owner: Pieter
- Login.
- Inventory.
- Transaksi.
- Update stock.
- Laporan.

**W3-03 — Sequence Diagram**  
Owner: Pieter | Support: Alexander
- Login.
- Transaksi.
- Update stock.

**W3-04 — Class Diagram**  
Owner: Pieter | Support: Alexander

**W3-05 — ERD**  
Owner: Alexander

Entitas konseptual minimal: Apotek, User/Account, Barang, Stok, Transaksi, Detail Transaksi.

**W3-06 — Database Design**  
Owner: Alexander
- PK/FK.
- Relationship.
- Pharmacy ownership.
- Constraints.
- Index yang diperlukan.

**W3-07 — User Flow**  
Owner: Roman

**W3-08 — Wireframe**  
Owner: Roman | Support: Samuel
- Login.
- Dashboard.
- Inventory.
- Form barang.
- Kasir.
- Keranjang.
- Transaction result.
- History.
- Report.

**W3-09 — UI/UX Review**  
Owner: Samuel | Support: Roman

**W3-10 — Architecture Design**  
Owner: Alexander

### Gate W3
Requirement → UML → ERD → UI/UX harus konsisten dan siap menjadi dasar coding.

---

## Minggu 4 — Technical Foundation

**Objective:** menyiapkan environment agar empat anggota dapat bekerja paralel.

### Tasks

- **W4-01 — Repository Setup** — Alexander
- **W4-02 — Git Branching Strategy** — Alexander
- **W4-03 — Project Structure** — Alexander
- **W4-04 — Database Initialization** — Alexander
- **W4-05 — Database Connection** — Alexander
- **W4-06 — Base Frontend Layout** — Pieter + Roman
- **W4-07 — UI Component Foundation** — Roman + Samuel
- **W4-08 — Authentication Skeleton** — Alexander
- **W4-09 — Dummy/Test Data** — Samuel
- **W4-10 — Coding Convention** — Alexander + All
- **W4-11 — Issue/Bug Board** — Samuel

### Acceptance Gate
- [ ] Semua anggota dapat menjalankan project.
- [ ] Database dapat digunakan.
- [ ] Repository dan branching berjalan.
- [ ] Base application dapat dibuka.
- [ ] Login skeleton tersedia.

---

## Minggu 5 — Authentication, Pharmacy & Inventory

**Objective:** menyelesaikan fondasi pengguna dan inventaris.

### Tasks

- **W5-01 — User/Account Data** — Alexander
- **W5-02 — Pharmacy Data** — Alexander
- **W5-03 — Account → Pharmacy Relationship** — Alexander
- **W5-04 — Login Implementation** — Alexander + Pieter
- **W5-05 — Session Protection** — Alexander
- **W5-06 — Pharmacy Data Isolation Backend** — Alexander
- **W5-07 — Inventory List UI** — Pieter
- **W5-08 — Add/Edit Item UI** — Roman + Samuel
- **W5-09 — CRUD Item Backend** — Alexander
- **W5-10 — Search Item** — Pieter
- **W5-11 — Inventory Validation** — Alexander
- **W5-12 — Inventory Test** — All

### Critical Note
Pharmacy isolation tidak boleh hanya diterapkan pada frontend. Backend harus memastikan user tidak dapat membaca/mengubah data apotek lain melalui URL atau parameter request.

### Output W5
```text
Login + Account→Pharmacy + Data Isolation + CRUD Barang + Search + Stock Display
```

---

## Minggu 6 — Cashier & Sales Transaction

**Objective:** membangun transaksi penjualan sebagai core business flow.

### Tasks

- **W6-01 — Cashier Page** — Pieter + Roman
- **W6-02 — Product Search in Cashier** — Pieter
- **W6-03 — Add Item to Cart** — Pieter + Alexander
- **W6-04 — Quantity Input** — Pieter
- **W6-05 — Quantity Validation** — Alexander
- **W6-06 — Multi-Item Cart** — Pieter + Samuel
- **W6-07 — Subtotal Calculation** — Alexander + Pieter
- **W6-08 — Tax/Transaction Formula** — Alexander + All
- **W6-09 — Transaction Header** — Alexander
- **W6-10 — Transaction Detail** — Alexander
- **W6-11 — Transaction Number** — Alexander
- **W6-12 — Date/Time Transaction** — Alexander
- **W6-13 — Checkout UI** — Roman + Pieter
- **W6-14 — Transaction Result** — Pieter
- **W6-15 — Transaction Testing** — All

Test minimal: satu item, multi-item, quantity > 1, quantity > stock, item tidak ditemukan, input invalid, pembatalan, transaksi berhasil.

### Gate W6
Transaksi dapat dibuat dan disimpan tanpa merusak data stok.

---

## Minggu 7 — Transaction ↔ Stock Integration

**Objective:** membuat core business flow menjadi konsisten end-to-end.

### Tasks

- **W7-01 — Transaction Success Handler** — Alexander
- **W7-02 — Stock Deduction** — Alexander
- **W7-03 — Transaction Failure Handling** — Alexander
- **W7-04 — Stock Consistency Check** — Alexander
- **W7-05 — Multi-Item Stock Update** — Alexander
- **W7-06 — Pharmacy-Specific Transaction** — Alexander
- **W7-07 — Pharmacy-Specific Stock Update** — Alexander
- **W7-08 — Cross-Pharmacy Access Testing** — All
- **W7-09 — End-to-End Testing** — All
- **W7-10 — Core Flow Review** — Alexander + All

### End-to-End Flow
```text
Login → Cari Barang → Kasir → Checkout → Transaction Saved → Stock Decreased → History
```

### Critical Gate W7
Core flow harus stabil sebelum fitur noninti ditambahkan.

---

## Minggu 8 — History & Reporting

**Objective:** menyediakan informasi operasional yang telah divalidasi kebutuhannya.

### Tasks

- **W8-01 — Transaction History Backend** — Alexander
- **W8-02 — Transaction History UI** — Pieter
- **W8-03 — Transaction Detail View** — Pieter
- **W8-04 — Inventory Report** — Alexander + Pieter
- **W8-05 — Transaction Report** — Alexander + Pieter
- **W8-06 — Report Filter** — Pieter
- **W8-07 — Report UI/UX** — Roman + Samuel
- **W8-08 — Report Data Isolation** — Alexander
- **W8-09 — History/Report Testing** — All

Output: Inventory + Cashier + Transaction + Stock + History + Report.

---

## Minggu 9 — Hardening, Optional Feature & Feature Freeze

**Objective:** menyelesaikan gap MVP dan hanya mengerjakan fitur tambahan yang tervalidasi.

### Tasks

- **W9-01 — MVP Gap Analysis** — Alexander + All
- **W9-02 — Requirement Traceability Review** — Samuel
- **W9-03 — Security Review** — Alexander + All
- **W9-04 — UX Review** — Roman + Samuel
- **W9-05 — Validation/Error Handling Review** — Alexander + Pieter
- **W9-06 — Bug Fix Sprint** — All
- **W9-07 — Member/Recommendation Decision** — Alexander + All
- **W9-08 — Feature Freeze** — Alexander

Security review minimal:
- authentication;
- authorization;
- session;
- SQL Injection prevention;
- direct URL access;
- parameter manipulation;
- pharmacy isolation;
- password handling.

### Gate W9
Semua Must Have telah selesai atau memiliki issue teridentifikasi dan rencana penyelesaian. Setelah feature freeze, tidak ada penambahan fitur baru tanpa change request.

---

## Minggu 10 — System Testing & UAT Preparation

**Objective:** menguji sistem terhadap requirement secara menyeluruh.

### Tasks

- **W10-01 — Test Plan Final** — Samuel + All
- **W10-02 — Functional Testing** — All
- **W10-03 — Integration Testing** — All
- **W10-04 — Authentication Testing** — Alexander + All
- **W10-05 — Pharmacy Isolation Testing** — Alexander + All
- **W10-06 — Inventory Testing** — Pieter + Samuel
- **W10-07 — Transaction Testing** — Pieter + Alexander
- **W10-08 — Stock Consistency Testing** — Alexander + All
- **W10-09 — Error Handling Testing** — Samuel + All
- **W10-10 — Regression Testing** — All
- **W10-11 — UAT Scenario Preparation** — Samuel + Pieter
- **W10-12 — UAT Environment Preparation** — Alexander

Output: test cases, test results, bug list, UAT scenarios, UAT environment.

---

## Minggu 11 — UAT, Bug Fixing & Deployment

**Objective:** memperoleh feedback mitra dan menghasilkan release candidate.

### Tasks

- **W11-01 — UAT** — All
- **W11-02 — Record UAT Feedback** — Samuel
- **W11-03 — Critical Bug Fix** — Alexander + All
- **W11-04 — Frontend Bug Fix** — Pieter
- **W11-05 — UI/UX Fix** — Roman + Samuel
- **W11-06 — Regression Testing** — All
- **W11-07 — Production/Hosting Setup** — Alexander
- **W11-08 — Deployment Trial** — Alexander + Samuel
- **W11-09 — Deployment Documentation** — Samuel + Alexander
- **W11-10 — User Guide Draft** — Samuel + Roman

UAT feedback dikategorikan sebagai bug, clarification, usability issue, feature request, atau out of scope.

### Gate W11
Release Candidate tersedia dan dapat dijalankan pada environment target.

---

## Minggu 12 — Final Release, Documentation & Presentation

**Objective:** menutup pekerjaan produk dan akademik.

### Tasks

- **W12-01 — Final Regression Test** — All
- **W12-02 — Final Acceptance Checklist** — Alexander
- **W12-03 — Final Deployment** — Alexander
- **W12-04 — Database Backup** — Alexander
- **W12-05 — Source Code Finalization** — All
- **W12-06 — Technical Documentation** — Alexander + Pieter
- **W12-07 — User Documentation** — Samuel + Roman
- **W12-08 — Final UML/ERD Update** — Pieter
- **W12-09 — Final UI/UX Documentation** — Roman
- **W12-10 — Test Report** — Samuel + All
- **W12-11 — Presentation Slides** — All
- **W12-12 — Demo Script** — Pieter + Roman
- **W12-13 — Final Presentation Rehearsal** — All
- **W12-14 — Final Project Archive** — Alexander

Archive minimal:
```text
Source Code
Database
PRD
SRS
UML
ERD
UI/UX
Test Report
UAT
Deployment Documentation
User Guide
Presentation
Demo Data
Backup
```

### Final Gate W12
Produk final dapat dijalankan, diuji, didemonstrasikan, dan didokumentasikan.

---

# 8. Task Dependency Map

```text
OBSERVATION
 ↓
REQUIREMENT
 ↓
SRS/PRD BASELINE
 ↓
UML + ERD + UIUX
 ↓
PROJECT FOUNDATION
 ↓
AUTH + PHARMACY
 ↓
INVENTORY
 ↓
STOCK
 ↓
CASHIER
 ↓
TRANSACTION
 ↓
TRANSACTION ↔ STOCK
 ↓
HISTORY / REPORT
 ↓
HARDENING
 ↓
SYSTEM TEST
 ↓
UAT
 ↓
BUG FIX
 ↓
DEPLOYMENT
 ↓
FINAL RELEASE
```

| Task | Dependency |
|---|---|
| Database implementation | ERD approved |
| Backend inventory | Database foundation |
| Frontend inventory | Wireframe/UI |
| Cashier | Inventory + item data |
| Transaction | Cashier + database |
| Stock deduction | Transaction |
| Report | Transaction/history |
| UAT | Stable release candidate |
| Deployment | System test + environment |
| Final documentation | Final implementation |

---

# 9. Task Board dan Template

Gunakan board:

```text
BACKLOG → TODO → IN PROGRESS → REVIEW → TESTING → DONE
                                      ↘ BLOCKED
```

Template task:

```text
ID:
Title:
Module:
Priority:
Owner:
Reviewer:
Week:
Dependency:
Requirement Reference:
Description:
Acceptance Criteria:
Test Case:
Deliverable:
Status:
```

### Contoh Task

```text
ID:
W7-02

Title:
Implement Stock Deduction After Successful Transaction

Module:
Stock / Transaction

Priority:
Must Have

Owner:
Alexander

Reviewer:
Pieter

Week:
7

Dependency:
W6-09 Transaction Header
W6-10 Transaction Detail

Description:
Mengurangi stok berdasarkan quantity transaksi setelah transaksi berhasil.

Acceptance Criteria:
- Stok berkurang sesuai quantity.
- Semua item transaksi diproses.
- Stok tidak berkurang jika transaksi gagal.
- Stok tidak menjadi negatif.

Test Case:
TC-STK-01
TC-STK-02
TC-STK-03

Deliverable:
Backend stock update.

Status:
TODO
```

---

# 10. Critical Task List

| ID | Critical Task |
|---|---|
| W2-01 | Functional Requirement Finalization |
| W2-04 | Business Rules |
| W3-05 | ERD |
| W3-06 | Database Design |
| W4-04 | Database Initialization |
| W4-08 | Authentication Foundation |
| W5-03 | Account → Pharmacy |
| W5-06 | Pharmacy Data Isolation |
| W5-09 | CRUD Item |
| W6-09 | Transaction Header |
| W6-10 | Transaction Detail |
| W6-13 | Checkout |
| W7-02 | Stock Deduction |
| W7-03 | Transaction Failure Handling |
| W7-07 | Pharmacy-Specific Stock Update |
| W7-08 | Cross-Pharmacy Access Test |
| W7-09 | End-to-End Test |
| W9-03 | Security Review |
| W10-05 | Pharmacy Isolation Testing |
| W10-08 | Stock Consistency Testing |
| W11-01 | UAT |
| W11-03 | Critical Bug Fix |
| W11-08 | Deployment Trial |
| W12-01 | Final Regression |
| W12-03 | Final Deployment |

---

# 11. Testing Matrix

| Modul | Functional | Integration | System | UAT |
|---|---:|---:|---:|---:|
| Login | ✓ | ✓ | ✓ | ✓ |
| Account → Pharmacy | ✓ | ✓ | ✓ | ✓ |
| Data Isolation | ✓ | ✓ | ✓ | ✓ |
| Inventory | ✓ | ✓ | ✓ | ✓ |
| Stock | ✓ | ✓ | ✓ | ✓ |
| Cashier | ✓ | ✓ | ✓ | ✓ |
| Transaction | ✓ | ✓ | ✓ | ✓ |
| Stock Update | ✓ | ✓ | ✓ | ✓ |
| History | ✓ | ✓ | ✓ | ✓ |
| Report | ✓ | ✓ | ✓ | ✓ |
| Error Handling | ✓ | ✓ | ✓ | ✓ |

## Minimum Test Scenarios

### Authentication
- TC-AUTH-01 Login valid.
- TC-AUTH-02 Login invalid.
- TC-AUTH-03 Access without login.
- TC-AUTH-04 Logout.
- TC-AUTH-05 Session after logout.

### Pharmacy Isolation
- TC-PHARM-01 User A melihat data A.
- TC-PHARM-02 User B melihat data B.
- TC-PHARM-03 User A mencoba URL/data B.
- TC-PHARM-04 User B mencoba URL/data A.
- TC-PHARM-05 Transaction A tidak muncul pada User B.
- TC-PHARM-06 Stock A tidak berubah akibat transaction B.

### Inventory
- TC-INV-01 Add item.
- TC-INV-02 Edit item.
- TC-INV-03 Delete item.
- TC-INV-04 Search item.
- TC-INV-05 Invalid input.

### Cashier
- TC-SAL-01 One item.
- TC-SAL-02 Multiple items.
- TC-SAL-03 Quantity > 1.
- TC-SAL-04 Quantity > stock.
- TC-SAL-05 Invalid quantity.
- TC-SAL-06 Item unavailable.
- TC-SAL-07 Cancel transaction.
- TC-SAL-08 Successful checkout.
- TC-SAL-09 Duplicate submit.

### Stock
- TC-STK-01 Stock decreases after success.
- TC-STK-02 Stock unchanged after failed transaction.
- TC-STK-03 Stock cannot become negative.
- TC-STK-04 Multi-item stock update.
- TC-STK-05 Pharmacy-specific stock update.

### Report
- TC-REP-01 Transaction history.
- TC-REP-02 Transaction detail.
- TC-REP-03 Inventory report.
- TC-REP-04 Transaction report.
- TC-REP-05 Pharmacy-specific report.

---

# 12. Bug Severity

| Severity | Definition | Action |
|---|---|---|
| Critical | Core flow/security/data sangat rusak | Fix immediately |
| High | Fitur penting gagal dan mengganggu MVP | Fix before release |
| Medium | Fungsi masih berjalan tetapi bermasalah signifikan | Fix before final if possible |
| Low | Minor UI/documentation issue | Fix if time permits |

Contoh Critical:

- User A dapat melihat data User B.
- Transaction berhasil tetapi stok tidak berkurang.
- Transaction gagal tetapi stok berkurang.
- Data transaksi/stock corrupt.
- User dapat mengakses halaman terlindungi tanpa autentikasi.

---

# 13. Change Request Procedure

Setelah Week 2:

```text
New Request
 ↓
Identify Source
 ↓
Check PRD/SRS
 ↓
Impact Analysis
 ↓
Estimate Time
 ↓
Check MVP Impact
 ↓
Decision
```

Pertanyaan evaluasi:

| Pertanyaan | Ya/Tidak |
|---|---|
| Dibutuhkan mitra? | |
| Sudah divalidasi? | |
| Termasuk scope? | |
| Must Have? | |
| Memerlukan database change? | |
| Memerlukan API eksternal? | |
| Menambah risiko? | |
| Mengganggu milestone? | |
| Mengorbankan fitur Must Have? | |

Jika fitur baru mengancam MVP, masukkan ke backlog.

---

# 14. Milestone & Gate

| Gate | Waktu | Syarat |
|---|---|---|
| G1 | End W1 | Proses dan masalah tervalidasi |
| G2 | End W2 | Requirement + scope baseline |
| G3 | End W3 | UML + ERD + UI/UX siap |
| G4 | End W4 | Development environment siap |
| G5 | End W7 | Core transaction-stock flow stabil |
| G6 | End W9 | Feature freeze |
| G7 | End W10 | System testing selesai |
| G8 | End W11 | Release candidate + UAT feedback |
| G9 | End W12 | Final release |

---

# 15. Weekly Team Allocation

| Minggu | Alexander | Pieter | Roman | Samuel |
|---|---|---|---|---|
| 1 | PM + requirement | Process/cashier | UI + member | Observation/data |
| 2 | Requirement | UML/use case | UX | Traceability |
| 3 | Architecture + DB | UML | UI/UX | UX review/data |
| 4 | Backend foundation | Frontend foundation | UI components | Data/issues |
| 5 | Auth + backend | Inventory frontend | Inventory UI | Testing/support |
| 6 | Transaction backend | Cashier frontend | Checkout UI | Test/data |
| 7 | Integration | Integration UI | UX fix | Integration test |
| 8 | Report backend | Report frontend | Report UX | Report test |
| 9 | Hardening | Bug fixing | UX hardening | QA/documentation |
| 10 | Security/integration test | Functional test | UI test | Test coordination |
| 11 | Deployment/backend fix | Frontend fix | UI/UX fix | UAT/documentation |
| 12 | Release | UML/demo | UI/demo | Documentation/test report |

---

# 16. Weekly Execution Pattern

### Awal minggu — PLAN
- Review target.
- Assign task.
- Check dependency.
- Update board.

### Tengah minggu — IMPLEMENT
- Coding/design/documentation.
- Review/merge.
- Update progress.

### Akhir minggu — INTEGRATE & TEST
- Integrate.
- Test.
- Fix bug.
- Review requirement.

### Akhir siklus — DOCUMENT
- Update PRD/SRS/UML bila ada perubahan resmi.
- Update test result.
- Update task board.
- Catat blocker.

---

# 17. Risiko Implementasi

| Risiko | Indikator Awal | Mitigasi |
|---|---|---|
| Requirement belum jelas | Banyak unresolved pada W2 | Validasi sebelum design |
| Scope creep | Banyak task baru setelah W2 | Change request |
| Backend bottleneck | Modul menunggu satu anggota | Parallel frontend + contract yang jelas |
| UI terlambat | Frontend menunggu backend | Mock/dummy data |
| Database berubah terus | ERD belum stabil | Review ERD sebelum W4 |
| Data isolation salah | Query tidak mengikuti pharmacy scope | Security test khusus |
| Stock tidak konsisten | Transaction failure test gagal | Integration test |
| UAT terlambat | Mitra belum siap W10 | Siapkan skenario lebih awal |
| Deployment gagal | Belum pernah trial | Deployment trial W11 |
| Fitur tambahan mengganggu MVP | Could Have dikerjakan terlalu awal | Feature freeze W9 |

---

# 18. Definition of Ready

Task hanya masuk **IN PROGRESS** jika:

- [ ] Task ID tersedia.
- [ ] Description jelas.
- [ ] Owner tersedia.
- [ ] Priority tersedia.
- [ ] Dependency diketahui.
- [ ] Acceptance criteria tersedia.
- [ ] Requirement reference tersedia.
- [ ] Data/UI/API yang diperlukan cukup jelas.

---

# 19. Definition of Release

Release final hanya dilakukan jika:

```text
Must Have = 100%
Critical Tests = PASS
Cross-Pharmacy Isolation = PASS
Transaction Integrity = PASS
Stock Integrity = PASS
Critical Bugs = 0
UAT = Completed
Deployment = Verified
Documentation = Completed
Backup = Available
```

---

# 20. Final Deliverable Checklist

## Product
- [ ] Web application.
- [ ] Authentication.
- [ ] Pharmacy account scope.
- [ ] Inventory.
- [ ] Stock.
- [ ] Cashier.
- [ ] Retail transaction.
- [ ] Transaction history.
- [ ] Reports in agreed scope.
- [ ] Validation.
- [ ] Error handling.
- [ ] Security controls.
- [ ] Deployment.

## Engineering
- [ ] Source code.
- [ ] Database.
- [ ] ERD.
- [ ] UML.
- [ ] Architecture documentation.
- [ ] Git repository.
- [ ] Environment/deployment documentation.

## QA
- [ ] Test plan.
- [ ] Test cases.
- [ ] Test results.
- [ ] Bug log.
- [ ] Regression result.
- [ ] UAT evidence.

## Academic
- [ ] PRD.
- [ ] SRS.
- [ ] UML.
- [ ] ERD.
- [ ] UI/UX.
- [ ] Test documentation.
- [ ] Deployment documentation.
- [ ] Presentation slides.
- [ ] Demo script.

---

# 21. Critical Path

```text
W1 Requirement Validation
 ↓
W2 Scope Baseline
 ↓
W3 ERD + UML + UI
 ↓
W4 Foundation
 ↓
W5 Authentication + Inventory
 ↓
W6 Cashier + Transaction
 ↓
W7 Stock Integration
 ↓
W8 History/Report
 ↓
W9 Hardening
 ↓
W10 Testing
 ↓
W11 UAT + Deployment
 ↓
W12 Final Release
```

## Final Implementation Principle

Dengan tim 4 orang dan waktu ±12 minggu, ukuran keberhasilan proyek bukan jumlah fitur, tetapi apakah tim berhasil menghasilkan MVP yang **sesuai requirement, benar secara alur bisnis, aman dalam pemisahan data antar-apotek, teruji, dapat digunakan, dapat di-deploy, dan dapat dipertanggungjawabkan secara akademik**.
