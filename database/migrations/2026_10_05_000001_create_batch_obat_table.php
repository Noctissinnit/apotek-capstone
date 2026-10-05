<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu obat dapat memiliki beberapa batch dengan tanggal kadaluarsa berbeda.
 * Stok disimpan per batch dalam satuan terkecil, sehingga penjualan dapat
 * mengambil dari batch yang paling dekat kadaluarsa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obat')->restrictOnDelete();
            $table->string('no_batch', 50);
            $table->date('tanggal_kadaluarsa')->nullable()->index();
            $table->unsignedInteger('stok')->default(0);

            // modal satu satuan terkecil setelah diskon, dipakai untuk laporan nilai
            $table->decimal('modal_per_satuan', 12, 2)->nullable();

            // aktif  = boleh dijual
            // ditahan = sengaja tidak dijual (rusak, mau diretur, ditarik supplier)
            $table->string('status', 20)->default('aktif')->index();
            $table->string('catatan_status')->nullable();

            $table->timestamps();

            // nomor batch tidak boleh kembar untuk obat yang sama
            $table->unique(['obat_id', 'no_batch']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_obat');
    }
};
