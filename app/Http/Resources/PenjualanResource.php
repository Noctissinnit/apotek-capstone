<?php

namespace App\Http\Resources;

use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transaksi penjualan beserta rincian obatnya bila sudah dimuat.
 *
 * @mixin Penjualan
 */
class PenjualanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $uang = fn ($nilai) => $nilai === null ? null : (float) $nilai;

        return [
            'id' => $this->id,
            'no_faktur' => $this->no_faktur,
            'tanggal_penjualan' => $this->tanggal_penjualan?->toIso8601String(),
            'kasir' => $this->whenLoaded('user', fn () => $this->user?->name),
            'metode_pembayaran' => $this->metode_pembayaran,
            'no_referensi' => $this->no_referensi,
            'total' => (float) $this->total,
            // hanya terisi untuk pembayaran tunai
            'uang_diterima' => $uang($this->uang_diterima),
            'kembalian' => $uang($this->kembalian),
            'struk_url' => route('api.v1.kasir.riwayat.pdf', $this->resource),
            'item' => $this->whenLoaded('detail', fn () => $this->detail->map(fn (DetailPenjualan $baris) => [
                'obat_id' => $baris->obat_id,
                'nama_obat' => $baris->obat?->nama_obat,
                'satuan' => $baris->obat?->satuan,
                'jumlah' => $baris->jumlah,
                'harga_jual' => (float) $baris->harga_jual,
                'subtotal' => (float) $baris->subtotal,
            ])),
        ];
    }
}
