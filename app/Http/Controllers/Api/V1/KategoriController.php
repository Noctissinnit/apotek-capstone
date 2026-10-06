<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriResource;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
}
