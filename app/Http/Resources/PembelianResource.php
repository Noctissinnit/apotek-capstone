<?php

namespace App\Http\Resources;

use App\Models\DetailPembelian;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Faktur barang masuk. Rincian baris hanya dikirim bila sudah dimuat (detail faktur).
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
            'dicatat_oleh' => $this->whenLoaded('user', fn () => $this->user?->name),
            'total' => (float) $this->total,
            'keterangan' => $this->keterangan,
            'jumlah_baris' => $this->whenCounted('detail'),
            'detail' => $this->whenLoaded('detail', fn () => $this->detail->map(fn (DetailPembelian $baris) => [
                'obat' => $baris->obat ? ['id' => $baris->obat->id, 'nama_obat' => $baris->obat->nama_obat, 'satuan' => $baris->obat->satuan] : null,
                'batch_obat_id' => $baris->batch_obat_id,
                'no_batch' => $baris->no_batch,
                'tanggal_kadaluarsa' => $baris->tanggal_kadaluarsa?->toDateString(),
                // box | satuan, menentukan arti jumlah dan harga_beli
                'satuan_input' => $baris->satuan_input,
                'isi_per_box' => $baris->isi_per_box,
                'jumlah' => $baris->jumlah,
                'jumlah_satuan' => $baris->jumlah_satuan,
                'harga_beli' => (float) $baris->harga_beli,
                'diskon_persen' => $baris->diskon_persen === null ? null : (float) $baris->diskon_persen,
                'modal_per_satuan' => $baris->modal_per_satuan === null ? null : (float) $baris->modal_per_satuan,
                'subtotal' => (float) $baris->subtotal,
            ])),
        ];
    }
}
