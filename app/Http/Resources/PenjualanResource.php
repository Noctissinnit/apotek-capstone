<?php

namespace App\Http\Resources;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transaksi penjualan. Saat ini bentuk ringkas untuk dashboard kasir;
 * rinciannya ditambah bersama endpoint riwayat transaksi.
 *
 * @mixin Penjualan
 */
class PenjualanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_faktur' => $this->no_faktur,
            'tanggal_penjualan' => $this->tanggal_penjualan?->toIso8601String(),
            'metode_pembayaran' => $this->metode_pembayaran,
            'total' => (float) $this->total,
            'item' => $this->whenLoaded('detail', fn () => $this->detail->map(fn ($baris) => [
                'nama_obat' => $baris->obat?->nama_obat,
                'jumlah' => $baris->jumlah,
            ])),
        ];
    }
}
