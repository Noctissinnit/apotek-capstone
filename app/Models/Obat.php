<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Obat extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Pilihan satuan terkecil yang dijual ke pembeli.
     * Ditambah di sini kalau mitra punya bentuk sediaan lain.
     */
    public const SATUAN = [
        'Tablet',
        'Kapsul',
        'Strip',
        'Botol',
        'Tube',
        'Sachet',
        'Ampul',
        'Pot',
        'Pcs',
        'Box',
        'Dus',
    ];

    protected $table = 'obat';

    protected $fillable = [
        'kode_obat',
        'nama_obat',
        'gambar',
        'apotek',
        'kategori_id',
        'satuan',
        'isi_per_box',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimum',
        'tanggal_kadaluarsa',
        'keterangan',
    ];

    /**
     * Alamat gambar obat untuk ditampilkan di halaman, null bila belum ada gambar.
     */
    public function urlGambar(): ?string
    {
        return $this->gambar ? asset('uploads/'.$this->gambar) : null;
    }

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'stok' => 'integer',
            'stok_minimum' => 'integer',
            'isi_per_box' => 'integer',
            'tanggal_kadaluarsa' => 'date',
        ];
    }

    public function detailPembelian(): HasMany
    {
        return $this->hasMany(DetailPembelian::class);
    }

    public function batch(): HasMany
    {
        return $this->hasMany(BatchObat::class);
    }

    /**
     * Total stok menurut catatan batch, dalam satuan terkecil.
     * Setelah modul barang masuk selesai, angka ini yang jadi patokan stok.
     */
    public function stokDariBatch(): int
    {
        return (int) $this->batch()->sum('stok');
    }

    /**
     * Stok yang benar-benar boleh dijual: tidak kadaluarsa dan tidak ditahan.
     * Angkanya bisa lebih kecil dari kolom stok, misalnya saat ada batch
     * kadaluarsa yang masih menunggu diretur atau dimusnahkan.
     */
    public function stokSiapDijual(): int
    {
        return (int) $this->batch()->siapDijual()->sum('stok');
    }

    /**
     * Batch paling dekat kadaluarsa yang masih boleh dijual.
     */
    public function batchTerdekatKadaluarsa(): ?BatchObat
    {
        return $this->batch()->siapDijual()->first();
    }

    public function kategoriRelasi(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id_kategori');
    }

    public function scopeStokMenipis(Builder $query): Builder
    {
        return $query->whereColumn('stok', '<=', 'stok_minimum');
    }

    /**
     * Obat hanya boleh dibuka user dari apotek yang sama. Obat lama tanpa
     * apotek masih boleh dibuka admin, sama seperti aturan scopeForUser.
     */
    public function bolehDiaksesOleh(User $user): bool
    {
        return $this->apotek === $user->apotek
            || ($this->apotek === null && $user->isAdmin());
    }

    /** Kolom yang boleh dipakai mengurutkan daftar obat, kunci = nama di URL. */
    public const KOLOM_URUT = [
        'kode' => 'kode_obat',
        'nama' => 'nama_obat',
        'stok' => 'stok',
        'kategori' => 'kategori.nama_kategori',
        'satuan' => 'satuan',
        'harga_jual' => 'harga_jual',
    ];

    /**
     * Pencarian, saringan, dan urutan daftar obat.
     * Dipakai bersama halaman Data Obat dan API, supaya hasilnya selalu sama.
     *
     * @param  array{search?: ?string, kategori_id?: mixed, satuan?: ?string, sort?: string, direction?: string}  $saringan
     */
    public function scopeSaring(Builder $query, array $saringan): Builder
    {
        $cari = trim((string) ($saringan['search'] ?? ''));
        $urut = $saringan['sort'] ?? 'kode';
        $arah = $saringan['direction'] ?? 'asc';

        return $query
            ->when(filled($saringan['kategori_id'] ?? null), fn ($query) => $query->where('obat.kategori_id', (int) $saringan['kategori_id']))
            ->when(filled($saringan['satuan'] ?? null), fn ($query) => $query->where('satuan', $saringan['satuan']))
            ->when($cari !== '', fn ($query) => $query->where(function ($query) use ($cari) {
                $query->where('kode_obat', 'like', "%{$cari}%")
                    ->orWhere('nama_obat', 'like', "%{$cari}%")
                    ->orWhereHas('kategoriRelasi', fn ($query) => $query->where('nama_kategori', 'like', "%{$cari}%"));
            }))
            ->when($urut === 'kategori', fn ($query) => $query->leftJoin('kategori', 'obat.kategori_id', '=', 'kategori.id_kategori')->select('obat.*'))
            ->orderBy(self::KOLOM_URUT[$urut] ?? self::KOLOM_URUT['kode'], $arah === 'desc' ? 'desc' : 'asc');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where($query->getModel()->getTable() . '.apotek', $user->apotek);

            if ($user->isAdmin()) {
                $query->orWhereNull($query->getModel()->getTable() . '.apotek');
            }
        });
    }
}
