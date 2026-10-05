<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemakaianBatch extends Model
{
    protected $table = 'pemakaian_batch';

    protected $fillable = [
        'detail_penjualan_id',
        'batch_obat_id',
        'jumlah',
        'modal_per_satuan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'modal_per_satuan' => 'decimal:2',
        ];
    }

    public function detailPenjualan(): BelongsTo
    {
        return $this->belongsTo(DetailPenjualan::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BatchObat::class, 'batch_obat_id');
    }
}
