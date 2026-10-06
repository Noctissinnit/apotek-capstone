<?php

use App\Http\Controllers\Api\V1\AuthController;
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
    });
});
