<?php

namespace App\Http\Resources;

use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Faktur barang masuk. Saat ini bentuk ringkas untuk dashboard;
 * rinciannya ditambah bersama endpoint pembelian.
 *
 * @mixin Pembelian
 */
class PembelianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_faktur' => $this->no_faktur,
            'tanggal_pembelian' => $this->tanggal_pembelian?->toDateString(),
            'supplier' => $this->whenLoaded('supplier', fn () => $this->supplier?->nama_supplier),
            'total' => (float) $this->total,
        ];
    }
}
