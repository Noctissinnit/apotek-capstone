<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stok keluar di luar penjualan: diretur ke supplier atau dimusnahkan.
 * Keduanya mengurangi stok batch, bedanya pada dokumen dan nilai kerugian:
 * barang retur biasanya diganti supplier, barang musnah hilang sepenuhnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyesuaian_stok', function (Blueprint $table) {
            $table->id();
            $table->string('no_dokumen', 50)->unique();

            // 'retur' atau 'musnah'
            $table->string('jenis', 20)->index();

            $table->date('tanggal');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('apotek')->nullable()->index();

            // diisi untuk retur: dikembalikan ke supplier mana
            $table->foreignId('supplier_id')->nullable()->constrained('supplier')->nullOnDelete();

            // total nilai modal barang yang keluar
            $table->decimal('total_nilai', 14, 2)->default(0);

            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('detail_penyesuaian_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyesuaian_stok_id')->constrained('penyesuaian_stok')->cascadeOnDelete();
            $table->foreignId('batch_obat_id')->constrained('batch_obat')->restrictOnDelete();
            $table->foreignId('obat_id')->constrained('obat')->restrictOnDelete();

            $table->unsignedInteger('jumlah');
            $table->decimal('modal_per_satuan', 12, 2)->nullable();
            $table->decimal('nilai', 14, 2)->default(0);

            // kadaluarsa, rusak, hilang, atau lainnya
            $table->string('alasan', 30)->default('kadaluarsa');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penyesuaian_stok');
        Schema::dropIfExists('penyesuaian_stok');
    }
};
