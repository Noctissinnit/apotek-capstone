<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenyesuaianStok extends Model
{
    protected $table = 'detail_penyesuaian_stok';

    protected $fillable = [
        'penyesuaian_stok_id',
        'batch_obat_id',
        'obat_id',
        'jumlah',
        'modal_per_satuan',
        'nilai',
        'alasan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'modal_per_satuan' => 'decimal:2',
            'nilai' => 'decimal:2',
        ];
    }

    public function penyesuaian(): BelongsTo
    {
        return $this->belongsTo(PenyesuaianStok::class, 'penyesuaian_stok_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BatchObat::class, 'batch_obat_id');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class)->withTrashed();
    }
}
