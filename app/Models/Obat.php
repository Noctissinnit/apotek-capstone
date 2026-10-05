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
