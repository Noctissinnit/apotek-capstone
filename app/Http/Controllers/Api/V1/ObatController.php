<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\SimpanObat;
use App\Http\Controllers\Controller;
use App\Http\Resources\ObatResource;
use App\Models\Obat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
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

    /**
     * Kirim sebagai multipart/form-data bila menyertakan gambar.
     */
    public function store(Request $request, SimpanObat $simpan): JsonResponse
    {
        $data = $request->validate(SimpanObat::aturan());

        $obat = $simpan->buat($request->user(), $data, $request->file('gambar'));

        return (new ObatResource($obat->load('kategoriRelasi')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mengganti gambar butuh multipart/form-data, dan PHP tidak membaca berkas
     * pada request PUT. Karena itu kirim sebagai POST dengan isian _method=PUT.
     */
    public function update(Request $request, Obat $obat, SimpanObat $simpan): ObatResource
    {
        abort_unless($obat->bolehDiaksesOleh($request->user()), 404);

        $data = $request->validate([
            ...SimpanObat::aturan($obat),
            'hapus_gambar' => ['nullable', 'boolean'],
        ]);
        unset($data['hapus_gambar']);

        $simpan->ubah($request->user(), $obat, $data, $request->file('gambar'), $request->boolean('hapus_gambar'));

        return new ObatResource($obat->load('kategoriRelasi'));
    }

    public function destroy(Request $request, Obat $obat): Response
    {
        abort_unless($obat->bolehDiaksesOleh($request->user()), 404);

        $obat->delete();

        return response()->noContent();
    }
}
