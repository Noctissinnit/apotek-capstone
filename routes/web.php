<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirTransaksiController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Pengarah ke dashboard sesuai role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('obat', ObatController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('permission:obat.kelola');
    Route::resource('obat', ObatController::class)->only(['index', 'show'])
        ->middleware('permission:obat.lihat');
    Route::resource('kategori', KategoriController::class)->except(['show'])
        ->middleware('permission:kategori.kelola');

    Route::middleware('role:admin_apotek_a|admin_apotek_b')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        Route::resource('users', UserController::class)
            ->names('user')
            ->except('show')
            ->middleware('permission:user.kelola');
    });

    Route::middleware('role:kasir_apotek_a|kasir_apotek_b')->prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'kasir'])->name('dashboard');
        Route::get('/transaksi', [KasirTransaksiController::class, 'index'])->name('transaksi');
        Route::get('/keranjang', [KasirTransaksiController::class, 'cart'])->name('keranjang');
        Route::post('/keranjang', [KasirTransaksiController::class, 'add'])->name('keranjang.add');
        Route::delete('/keranjang/{obat}', [KasirTransaksiController::class, 'remove'])->name('keranjang.remove');
        Route::post('/checkout', [KasirTransaksiController::class, 'checkout'])->name('checkout');

        Route::get('/riwayat', [PenjualanController::class, 'index'])->name('riwayat');
        Route::get('/riwayat/laporan/pdf', [PenjualanController::class, 'exportRentangPdf'])->name('riwayat.pdf-rentang');
        Route::get('/riwayat/{penjualan}/pdf', [PenjualanController::class, 'exportPdf'])->name('riwayat.pdf');
        Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');
    });
});
