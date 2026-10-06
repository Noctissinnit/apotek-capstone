<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ObatResource;
use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ObatController extends Controller
{
    /**
     * Daftar obat milik apotek user, dengan pencarian, saringan, dan urutan
     * yang sama seperti halaman Data Obat.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $saringan = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'kategori_id' => ['nullable', 'integer'],
            'satuan' => ['nullable', 'string', 'max:50'],
            'sort' => ['nullable', Rule::in(array_keys(Obat::KOLOM_URUT))],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $obat = Obat::query()
            ->forUser($request->user())
            ->with('kategoriRelasi')
            ->saring($saringan)
            ->paginate($saringan['per_page'] ?? 10)
            ->withQueryString();

        return ObatResource::collection($obat);
    }

    /**
     * Satu obat beserta seluruh batch-nya, urut dari yang paling dekat kadaluarsa.
     */
    public function show(Request $request, Obat $obat): ObatResource
    {
        abort_unless($obat->bolehDiaksesOleh($request->user()), 404);

        $obat->load([
            'kategoriRelasi',
            'batch' => fn ($query) => $query->orderByRaw('tanggal_kadaluarsa is null')->orderBy('tanggal_kadaluarsa'),
        ]);

        return (new ObatResource($obat))->additional([
            'meta' => ['stok_siap_dijual' => $obat->stokSiapDijual()],
        ]);
    }
}
