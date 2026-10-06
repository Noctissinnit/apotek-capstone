<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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

    /** Baris faktur barang masuk yang membentuk batch ini. */
    public function barangMasuk(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'batch_obat_id');
    }

    /** Pemakaian batch ini pada transaksi penjualan. */
    public function pemakaian(): HasMany
    {
        return $this->hasMany(PemakaianBatch::class, 'batch_obat_id');
    }

    /** Pengeluaran lewat retur ke supplier atau pemusnahan. */
    public function penyesuaian(): HasMany
    {
        return $this->hasMany(DetailPenyesuaianStok::class, 'batch_obat_id');
    }

    /**
     * Riwayat keluar masuk batch ini, diurutkan dari yang paling lama.
     * Dipakai halaman penelusuran untuk menunjukkan asal dan tujuan barang.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function riwayat(): Collection
    {
        $masuk = $this->barangMasuk()->with('pembelian.supplier')->get()->map(fn (DetailPembelian $baris) => [
            'tanggal' => $baris->pembelian?->tanggal_pembelian ?? $baris->created_at,
            'jenis' => 'masuk',
            'keterangan' => 'Barang masuk dari '.($baris->pembelian?->supplier?->nama_supplier ?? 'supplier'),
            'dokumen' => $baris->pembelian?->no_faktur,
            'jumlah' => $baris->jumlah_satuan,
        ]);

        $terjual = $this->pemakaian()->with('detailPenjualan.penjualan')->get()->map(fn (PemakaianBatch $baris) => [
            'tanggal' => $baris->detailPenjualan?->penjualan?->tanggal_penjualan ?? $baris->created_at,
            'jenis' => 'jual',
            'keterangan' => 'Terjual di kasir',
            'dokumen' => $baris->detailPenjualan?->penjualan?->no_faktur,
            'jumlah' => -$baris->jumlah,
        ]);

        $keluarLain = $this->penyesuaian()->with('penyesuaian')->get()->map(function (DetailPenyesuaianStok $baris) {
            $dokumen = $baris->penyesuaian;

            return [
                'tanggal' => $dokumen?->tanggal ?? $baris->created_at,
                'jenis' => $dokumen?->isRetur() ? 'retur' : 'musnah',
                'keterangan' => ($dokumen?->isRetur() ? 'Diretur ke supplier' : 'Dimusnahkan').' · alasan '.$baris->alasan,
                'dokumen' => $dokumen?->no_dokumen,
                'jumlah' => -$baris->jumlah,
            ];
        });

        return $masuk->concat($terjual)->concat($keluarLain)->sortBy('tanggal')->values();
    }

    /**
     * Riwayat beserta jumlahnya: total masuk, total keluar, dan stok awal.
     *
     * Batch hasil migrasi data lama bisa tidak punya catatan barang masuk.
     * Selisihnya ditampilkan sebagai stok awal supaya angkanya tetap jujur.
     *
     * @return array{riwayat: Collection<int, array<string, mixed>>, total_masuk: int, total_keluar: int, stok_awal: int}
     */
    public function ringkasanRiwayat(): array
    {
        $riwayat = $this->riwayat();
        $totalMasuk = (int) $riwayat->where('jumlah', '>', 0)->sum('jumlah');
        $totalKeluar = (int) abs($riwayat->where('jumlah', '<', 0)->sum('jumlah'));

        return [
            'riwayat' => $riwayat,
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'stok_awal' => $this->stok + $totalKeluar - $totalMasuk,
        ];
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
     * Batch yang boleh dijual, tanpa urutan. Dipakai juga di dalam subquery
     * penjumlahan stok, yang tidak boleh berisi ORDER BY.
     *
     * @param  Builder<BatchObat>  $query
     * @return Builder<BatchObat>
     */
    public function scopeBolehDijual(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AKTIF)
            ->where('stok', '>', 0)
            ->where(function (Builder $query) {
                $query->whereNull('tanggal_kadaluarsa')
                    ->orWhereDate('tanggal_kadaluarsa', '>=', now()->toDateString());
            });
    }

    /**
     * Batch yang siap dijual, diurutkan dari yang paling dekat kadaluarsa.
     *
     * @param  Builder<BatchObat>  $query
     * @return Builder<BatchObat>
     */
    public function scopeSiapDijual(Builder $query): Builder
    {
        return $query->bolehDijual()
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

    /** Pilihan saringan status di daftar batch. */
    public const SARINGAN_STATUS = ['siap', 'kadaluarsa', 'ditahan', 'habis'];

    /**
     * Pencarian dan saringan daftar batch, urut dari yang paling dekat kadaluarsa.
     * Dipakai bersama halaman Lacak Batch dan API.
     *
     * @param  Builder<BatchObat>  $query
     * @return Builder<BatchObat>
     */
    public function scopeSaring(Builder $query, string $cari = '', string $status = ''): Builder
    {
        $cari = trim($cari);

        return $query
            ->when($cari !== '', fn ($query) => $query->where(function ($query) use ($cari) {
                $query->where('no_batch', 'like', "%{$cari}%")
                    ->orWhereHas('obat', fn ($obat) => $obat
                        ->where('nama_obat', 'like', "%{$cari}%")
                        ->orWhere('kode_obat', 'like', "%{$cari}%"));
            }))
            ->when($status === 'kadaluarsa', fn ($query) => $query->kadaluarsa())
            ->when($status === 'ditahan', fn ($query) => $query->where('status', self::STATUS_DITAHAN))
            ->when($status === 'habis', fn ($query) => $query->where('stok', 0))
            ->when($status === 'siap', fn ($query) => $query->siapDijual())
            ->orderByRaw('tanggal_kadaluarsa is null')
            ->orderBy('tanggal_kadaluarsa');
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
