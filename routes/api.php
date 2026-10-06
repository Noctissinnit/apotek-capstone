<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BatchObatController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\KategoriController;
use App\Http\Controllers\Api\V1\ObatController;
use App\Http\Controllers\Api\V1\ReferensiController;
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

        Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin')
            ->middleware('role:admin_apotek_a|admin_apotek_b');

        Route::middleware('role:kasir_apotek_a|kasir_apotek_b')->group(function () {
            Route::get('/dashboard/kasir', [DashboardController::class, 'kasir'])->name('dashboard.kasir');
            Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');
        });
    });
});
