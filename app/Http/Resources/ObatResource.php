<?php

namespace App\Http\Resources;

use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Obat
 */
class ObatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_obat' => $this->kode_obat,
            'nama_obat' => $this->nama_obat,
            'gambar_url' => $this->urlGambar(),
            'apotek' => $this->apotek,
            'kategori' => $this->whenLoaded('kategoriRelasi', fn () => $this->kategoriRelasi
                ? ['id' => $this->kategoriRelasi->id_kategori, 'nama' => $this->kategoriRelasi->nama_kategori]
                : null),
            'satuan' => $this->satuan,
            'isi_per_box' => $this->isi_per_box,
            // harga beli adalah data pengelola, kasir cukup tahu harga jual
            'harga_beli' => $this->when($request->user()?->can('obat.kelola'), fn () => (float) $this->harga_beli),
            'harga_jual' => (float) $this->harga_jual,
            'stok' => $this->stok,
            'stok_minimum' => $this->stok_minimum,
            'stok_menipis' => $this->stok <= $this->stok_minimum,
            'tanggal_kadaluarsa' => $this->tanggal_kadaluarsa?->toDateString(),
            'keterangan' => $this->keterangan,
            'batch' => BatchObatResource::collection($this->whenLoaded('batch')),
        ];
    }
}
