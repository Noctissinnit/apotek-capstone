<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori';

    protected $primaryKey = 'id_kategori';

    protected $fillable = ['nama_kategori'];

    /**
     * Aturan isian kategori, dipakai bersama halaman web dan API.
     *
     * @return array<string, mixed>
     */
    public static function aturan(?self $kategori = null): array
    {
        return [
            'nama_kategori' => ['required', 'string', 'max:50', Rule::unique('kategori', 'nama_kategori')->ignore($kategori?->id_kategori, 'id_kategori')],
        ];
    }

    /**
     * Kategori yang masih dipakai obat, termasuk obat apotek lain, tidak boleh dihapus.
     */
    public function masihDipakai(): bool
    {
        return $this->obat()->exists();
    }

    public function obat(): HasMany
    {
        return $this->hasMany(Obat::class, 'kategori_id', 'id_kategori');
    }
}
