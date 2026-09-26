<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riwayat_penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('nama_pelanggan')->nullable();
            $table->dateTime('tanggal_penjualan');
            $table->decimal('total', 14, 2)->default(0);
            $table->string('metode_pembayaran', 30)->default('Tunai');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tanggal_penjualan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_penjualan');
    }
};
