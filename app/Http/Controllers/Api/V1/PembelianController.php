<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\SimpanBarangMasuk;
use App\Http\Controllers\Controller;
use App\Http\Resources\PembelianResource;
use App\Models\Pembelian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Barang masuk dari supplier.
 */
class PembelianController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $saringan = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return PembelianResource::collection(
            Pembelian::query()
                ->forUser($request->user())
                ->with(['supplier', 'user'])
                ->withCount('detail')
                ->latest('tanggal_pembelian')
                ->latest('id')
                ->paginate($saringan['per_page'] ?? 10)
                ->withQueryString()
        );
    }

    public function show(Request $request, Pembelian $pembelian): PembelianResource
    {
        abort_unless($pembelian->apotek === $request->user()->apotek, 404);

        return new PembelianResource($pembelian->load(['supplier', 'user', 'detail.obat']));
    }

    /**
     * no_faktur boleh dikosongkan, nanti dibuatkan otomatis (FK-YYYYMMDD-NNN).
     */
    public function store(Request $request, SimpanBarangMasuk $simpan): JsonResponse
    {
        $data = $request->validate([
            ...SimpanBarangMasuk::aturan(),
            'no_faktur' => ['nullable', 'string', 'max:50', 'unique:pembelian,no_faktur'],
        ], SimpanBarangMasuk::pesan());

        $data['no_faktur'] ??= SimpanBarangMasuk::nomorFakturBerikutnya();

        $pembelian = $simpan($request->user(), $data);

        return (new PembelianResource($pembelian->load(['supplier', 'user', 'detail.obat'])))
            ->response()
            ->setStatusCode(201);
    }
}
