<?php

namespace App\Http\Resources;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Kategori
 */
class KategoriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_kategori,
            'nama' => $this->nama_kategori,
            // hanya obat milik apotek user yang login
            'jumlah_obat' => $this->whenCounted('obat'),
        ];
    }
}
