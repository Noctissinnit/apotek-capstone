<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchObat extends Model
{
    use HasFactory;

    /** Batch boleh dijual. */
    public const STATUS_AKTIF = 'aktif';

    /** Sengaja tidak dijual: rusak, menunggu retur, atau ditarik supplier. */
    public const STATUS_DITAHAN = 'ditahan';

    protected $table = 'batch_obat';

    protected $fillable = [
        'obat_id',
        'no_batch',
        'tanggal_kadaluarsa',
        'stok',
        'modal_per_satuan',
        'status',
        'catatan_status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kadaluarsa' => 'date',
            'stok' => 'integer',
            'modal_per_satuan' => 'decimal:2',
        ];
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class)->withTrashed();
    }

    public function sudahKadaluarsa(): bool
    {
        return $this->tanggal_kadaluarsa !== null && $this->tanggal_kadaluarsa->isPast();
    }

    public function bolehDijual(): bool
    {
        return $this->status === self::STATUS_AKTIF
            && $this->stok > 0
            && ! $this->sudahKadaluarsa();
    }

    /**
     * Tingkat peringatan kadaluarsa, dipakai untuk menandai batch di layar.
     * Nilainya: kadaluarsa, segera_habis, siapkan_retur, atau aman.
     */
    public function tingkatKadaluarsa(): string
    {
        $sisa = $this->sisaHari();

        if ($sisa === null) {
            return 'aman';
        }

        $ambang = config('apotek.peringatan_kadaluarsa');

        return match (true) {
            $sisa < 0 => 'kadaluarsa',
            $sisa <= $ambang['segera_habis'] => 'segera_habis',
            $sisa <= $ambang['siapkan_retur'] => 'siapkan_retur',
            default => 'aman',
        };
    }

    /**
     * Sisa hari menuju kadaluarsa. Minus berarti sudah lewat.
     */
    public function sisaHari(): ?int
    {
        return $this->tanggal_kadaluarsa
            ? (int) now()->startOfDay()->diffInDays($this->tanggal_kadaluarsa, false)
            : null;
    }

    /**
     * Batch yang siap dijual, diurutkan dari yang paling dekat kadaluarsa.
     *
     * @param  Builder<BatchObat>  $query
     * @return Builder<BatchObat>
     */
    public function scopeSiapDijual(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF)
            ->where('stok', '>', 0)
            ->where(function (Builder $query) {
                $query->whereNull('tanggal_kadaluarsa')
                    ->orWhereDate('tanggal_kadaluarsa', '>=', now()->toDateString());
            })
            ->orderByRaw('tanggal_kadaluarsa is null')  // yang punya tanggal didahulukan
            ->orderBy('tanggal_kadaluarsa')
            ->orderBy('id');
    }

    /**
     * Batch yang stoknya masih ada tetapi tanggalnya sudah lewat.
     *
     * @param  Builder<BatchObat>  $query
     * @return Builder<BatchObat>
     */
    public function scopeKadaluarsa(Builder $query): Builder
    {
        return $query->where('stok', '>', 0)
            ->whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<', now()->toDateString());
    }

    /**
     * Batasi hanya batch milik apotek user, mengikuti apotek obatnya.
     *
     * @param  Builder<BatchObat>  $query
     * @return Builder<BatchObat>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->whereHas('obat', fn (Builder $obat) => $obat->forUser($user));
    }
}
