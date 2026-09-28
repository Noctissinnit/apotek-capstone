<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';

    protected $fillable = [
        'no_faktur',
        'user_id',
        'tanggal_penjualan',
        'total',
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
