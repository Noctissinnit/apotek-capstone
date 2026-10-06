<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SupplierController extends Controller
{
    /**
     * Semua supplier, untuk pilihan di form barang masuk dan retur.
     * Supplier adalah data bersama kedua apotek.
     */
    public function index(): AnonymousResourceCollection
    {
        return SupplierResource::collection(Supplier::orderBy('nama_supplier')->get());
    }
}
