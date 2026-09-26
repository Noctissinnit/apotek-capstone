<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $fillable = [
        'no_transaksi',
        'user_id',
        'nama_pelanggan',
        'tanggal_penjualan',
        'total',
        'metode_pembayaran',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penjualan' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class);
    }
}
