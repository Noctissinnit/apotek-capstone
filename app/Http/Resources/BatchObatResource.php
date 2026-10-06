<?php

namespace App\Http\Resources;

use App\Models\BatchObat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BatchObat
 */
class BatchObatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_batch' => $this->no_batch,
            'obat' => $this->whenLoaded('obat', fn () => [
                'id' => $this->obat->id,
                'kode_obat' => $this->obat->kode_obat,
                'nama_obat' => $this->obat->nama_obat,
                'satuan' => $this->obat->satuan,
            ]),
            'tanggal_kadaluarsa' => $this->tanggal_kadaluarsa?->toDateString(),
            'sisa_hari' => $this->sisaHari(),
            // kadaluarsa | segera_habis | siapkan_retur | aman
            'tingkat_kadaluarsa' => $this->tingkatKadaluarsa(),
            'stok' => $this->stok,
            'status' => $this->status,
            'catatan_status' => $this->catatan_status,
            'boleh_dijual' => $this->bolehDijual(),
            'modal_per_satuan' => $this->when(
                $request->user()?->can('obat.kelola'),
                fn () => $this->modal_per_satuan === null ? null : (float) $this->modal_per_satuan,
            ),
        ];
    }
}
