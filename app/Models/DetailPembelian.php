<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPembelian extends Model
{
    protected $table = 'detail_pembelian';

    protected $fillable = [
        'pembelian_id',
        'obat_id',
        'jumlah',
        'harga_beli',
        'subtotal',
        'no_batch',
        'tanggal_kadaluarsa',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_beli' => 'decimal:2',
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
}
