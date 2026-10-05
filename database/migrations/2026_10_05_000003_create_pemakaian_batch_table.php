<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu baris penjualan bisa diambil dari lebih dari satu batch, misalnya
 * batch lama tinggal 3 tablet lalu sisanya diambil dari batch berikutnya.
 * Tabel ini mencatat berapa yang diambil dari tiap batch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemakaian_batch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_penjualan_id')->constrained('detail_penjualan')->cascadeOnDelete();
            $table->foreignId('batch_obat_id')->constrained('batch_obat')->restrictOnDelete();
            $table->unsignedInteger('jumlah');

            // modal saat barang keluar, dipakai untuk menghitung laba
            $table->decimal('modal_per_satuan', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemakaian_batch');
    }
};
