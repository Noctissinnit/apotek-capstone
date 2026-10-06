<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriResource;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class KategoriController extends Controller
{
    /**
     * Semua kategori, tanpa pagination: jumlahnya sedikit dan dipakai
     * sebagai pilihan saringan atau isian form obat.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return KategoriResource::collection(
            Kategori::withCount(['obat' => fn ($query) => $query->forUser($request->user())])
                ->orderBy('nama_kategori')
                ->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $kategori = Kategori::create($request->validate(Kategori::aturan()));

        return (new KategoriResource($kategori))->response()->setStatusCode(201);
    }

    public function update(Request $request, Kategori $kategori): KategoriResource
    {
        $kategori->update($request->validate(Kategori::aturan($kategori)));

        return new KategoriResource($kategori);
    }

    public function destroy(Kategori $kategori): Response|JsonResponse
    {
        if ($kategori->masihDipakai()) {
            return response()->json(['message' => 'Kategori yang masih digunakan obat tidak dapat dihapus.'], 409);
        }

        $kategori->delete();

        return response()->noContent();
    }
}
