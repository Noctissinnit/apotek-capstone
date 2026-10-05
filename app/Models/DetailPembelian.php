<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPembelian extends Model
{
    /** Jumlah dan harga diketik dalam box. */
    public const SATUAN_BOX = 'box';

    /** Jumlah dan harga diketik dalam satuan terkecil, misalnya tablet. */
    public const SATUAN_TERKECIL = 'satuan';

    protected $table = 'detail_pembelian';

    protected $fillable = [
        'pembelian_id',
        'obat_id',
        'batch_obat_id',
        'satuan_input',
        'isi_per_box',
        'jumlah',
        'jumlah_satuan',
        'harga_beli',
        'diskon_persen',
        'modal_per_satuan',
        'subtotal',
        'no_batch',
        'tanggal_kadaluarsa',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'jumlah_satuan' => 'integer',
            'isi_per_box' => 'integer',
            'harga_beli' => 'decimal:2',
            'diskon_persen' => 'decimal:2',
            'modal_per_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tanggal_kadaluarsa' => 'date',
        ];
    }

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class);
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class)->withTrashed();
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BatchObat::class, 'batch_obat_id');
    }

    /**
     * Keterangan jumlah untuk ditampilkan, misalnya "5 Box (500 Tablet)".
     */
    public function keteranganJumlah(): string
    {
        $satuanKecil = $this->obat?->satuan ?? 'satuan';

        if ($this->satuan_input === self::SATUAN_BOX) {
            return $this->jumlah.' Box ('.$this->jumlah_satuan.' '.$satuanKecil.')';
        }

        return $this->jumlah_satuan.' '.$satuanKecil;
    }
}
