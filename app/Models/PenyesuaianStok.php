<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenyesuaianStok extends Model
{
    /** Dikembalikan ke supplier, biasanya diganti barang atau uang. */
    public const JENIS_RETUR = 'retur';

    /** Dimusnahkan secara fisik, nilainya hilang sepenuhnya. */
    public const JENIS_MUSNAH = 'musnah';

    public const ALASAN = ['kadaluarsa', 'rusak', 'hilang', 'lainnya'];

    protected $table = 'penyesuaian_stok';

    protected $fillable = [
        'no_dokumen',
        'jenis',
        'tanggal',
        'user_id',
        'apotek',
        'supplier_id',
        'total_nilai',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total_nilai' => 'decimal:2',
        ];
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenyesuaianStok::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function isRetur(): bool
    {
        return $this->jenis === self::JENIS_RETUR;
    }

    /**
     * Judul dokumen cetak sesuai jenisnya.
     */
    public function judulDokumen(): string
    {
        return $this->isRetur() ? 'Nota Retur ke Supplier' : 'Berita Acara Pemusnahan Obat';
    }

    /**
     * @param  Builder<PenyesuaianStok>  $query
     * @return Builder<PenyesuaianStok>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where($query->getModel()->getTable().'.apotek', $user->apotek);
    }

    /**
     * Nilai barang yang keluar lewat retur dan pemusnahan sejak awal bulan ini.
     *
     * @return array{retur: float, musnah: float}
     */
    public static function nilaiBulanIni(User $user): array
    {
        $nilai = self::query()
            ->forUser($user)
            ->whereDate('tanggal', '>=', now()->startOfMonth())
            ->selectRaw('jenis, sum(total_nilai) as jumlah')
            ->groupBy('jenis')
            ->pluck('jumlah', 'jenis');

        return [
            self::JENIS_RETUR => (float) ($nilai[self::JENIS_RETUR] ?? 0),
            self::JENIS_MUSNAH => (float) ($nilai[self::JENIS_MUSNAH] ?? 0),
        ];
    }
}
