<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BatchObatController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\KasirController;
use App\Http\Controllers\Api\V1\KategoriController;
use App\Http\Controllers\Api\V1\ObatController;
use App\Http\Controllers\Api\V1\PembelianController;
use App\Http\Controllers\Api\V1\PenyesuaianStokController;
use App\Http\Controllers\Api\V1\ReferensiController;
use App\Http\Controllers\Api\V1\RiwayatPenjualanController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PenyesuaianStokController as WebPenyesuaianStokController;
use Illuminate\Support\Facades\Route;

/*
 * API untuk frontend Next.js. Semua route di sini otomatis berawalan /api.
 *
 * Aturan akses tiap route harus sama dengan route padanannya di routes/web.php,
 * supaya data yang tertutup di halaman web juga tertutup lewat API.
 */
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::get('/referensi', ReferensiController::class)->name('referensi');

        Route::middleware('permission:obat.lihat')->group(function () {
            Route::get('/obat', [ObatController::class, 'index'])->name('obat.index');
            Route::get('/obat/{obat}', [ObatController::class, 'show'])->name('obat.show');

            // Di web, halaman Data Kategori khusus pengelola. Daftarnya dibuka untuk
            // semua yang boleh melihat obat karena dipakai sebagai saringan daftar obat.
            Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');

            Route::get('/batch', [BatchObatController::class, 'index'])->name('batch.index');
            Route::get('/batch/{batch}', [BatchObatController::class, 'show'])->name('batch.show');
        });

        Route::middleware('permission:obat.kelola')->group(function () {
            Route::post('/obat', [ObatController::class, 'store'])->name('obat.store');
            Route::match(['put', 'patch'], '/obat/{obat}', [ObatController::class, 'update'])->name('obat.update');
            Route::delete('/obat/{obat}', [ObatController::class, 'destroy'])->name('obat.destroy');
        });

        Route::middleware('permission:kategori.kelola')->group(function () {
            Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
            Route::match(['put', 'patch'], '/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
            Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
        });

        // Pilihan supplier di form barang masuk dan retur
        Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index')
            ->middleware('permission:pembelian.kelola|stok.penyesuaian');

        // Barang masuk dari supplier
        Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index')
            ->middleware('permission:pembelian.lihat');
        Route::get('/pembelian/{pembelian}', [PembelianController::class, 'show'])->name('pembelian.show')
            ->middleware('permission:pembelian.lihat');
        Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store')
            ->middleware('permission:pembelian.kelola');

        // Retur ke supplier dan pemusnahan stok
        Route::middleware('permission:stok.penyesuaian')->group(function () {
            Route::get('/penyesuaian', [PenyesuaianStokController::class, 'index'])->name('penyesuaian.index');
            Route::post('/penyesuaian', [PenyesuaianStokController::class, 'store'])->name('penyesuaian.store');
            Route::get('/penyesuaian/{penyesuaian}', [PenyesuaianStokController::class, 'show'])->name('penyesuaian.show');
            // berkas PDF-nya sama persis dengan yang diunduh dari halaman web
            Route::get('/penyesuaian/{penyesuaian}/pdf', [WebPenyesuaianStokController::class, 'pdf'])->name('penyesuaian.pdf');
        });

        Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin')
            ->middleware('role:admin_apotek_a|admin_apotek_b');

        Route::middleware('role:kasir_apotek_a|kasir_apotek_b')->group(function () {
            Route::get('/dashboard/kasir', [DashboardController::class, 'kasir'])->name('dashboard.kasir');
            Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');

            // Meja kasir. Cari obat lewat GET /obat (sudah berisi stok_siap_dijual).
            Route::prefix('kasir')->name('kasir.')->group(function () {
                Route::get('/keranjang', [KasirController::class, 'keranjang'])->name('keranjang');
                Route::post('/keranjang', [KasirController::class, 'tambah'])->name('keranjang.tambah');
                Route::delete('/keranjang', [KasirController::class, 'kosongkan'])->name('keranjang.kosongkan');
                Route::put('/keranjang/{obat}', [KasirController::class, 'atur'])->whereNumber('obat')->name('keranjang.atur');
                Route::delete('/keranjang/{obat}', [KasirController::class, 'hapus'])->whereNumber('obat')->name('keranjang.hapus');
                Route::post('/checkout', [KasirController::class, 'checkout'])->name('checkout');

                Route::get('/riwayat', [RiwayatPenjualanController::class, 'index'])->name('riwayat.index');
                // berkas PDF-nya sama persis dengan yang diunduh dari halaman web
                Route::get('/riwayat/laporan/pdf', [PenjualanController::class, 'exportRentangPdf'])->name('laporan.pdf');
                Route::get('/riwayat/{penjualan}', [RiwayatPenjualanController::class, 'show'])->name('riwayat.show');
                Route::get('/riwayat/{penjualan}/pdf', [PenjualanController::class, 'exportPdf'])->name('riwayat.pdf');
            });
        });
    });
});
