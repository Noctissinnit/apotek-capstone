<?php

namespace App\Http\Resources;

use App\Models\DetailPenyesuaianStok;
use App\Models\PenyesuaianStok;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Dokumen retur atau pemusnahan stok.
 *
 * @mixin PenyesuaianStok
 */
class PenyesuaianStokResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_dokumen' => $this->no_dokumen,
            // retur | musnah
            'jenis' => $this->jenis,
            'judul' => $this->judulDokumen(),
            'tanggal' => $this->tanggal?->toDateString(),
            'supplier' => $this->whenLoaded('supplier', fn () => $this->supplier?->nama_supplier),
            'dicatat_oleh' => $this->whenLoaded('user', fn () => $this->user?->name),
            'total_nilai' => (float) $this->total_nilai,
            'keterangan' => $this->keterangan,
            'jumlah_baris' => $this->whenCounted('detail'),
            'pdf_url' => route('api.v1.penyesuaian.pdf', $this->resource),
            'detail' => $this->whenLoaded('detail', fn () => $this->detail->map(fn (DetailPenyesuaianStok $baris) => [
                'obat' => $baris->obat ? ['id' => $baris->obat->id, 'nama_obat' => $baris->obat->nama_obat, 'satuan' => $baris->obat->satuan] : null,
                'batch_obat_id' => $baris->batch_obat_id,
                'no_batch' => $baris->batch?->no_batch,
                'jumlah' => $baris->jumlah,
                'modal_per_satuan' => $baris->modal_per_satuan === null ? null : (float) $baris->modal_per_satuan,
                'nilai' => (float) $baris->nilai,
                'alasan' => $baris->alasan,
            ])),
        ];
    }
}
