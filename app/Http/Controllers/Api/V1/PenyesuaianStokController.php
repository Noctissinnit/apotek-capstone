<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\SimpanPenyesuaianStok;
use App\Http\Controllers\Controller;
use App\Http\Resources\PenyesuaianStokResource;
use App\Models\BatchObat;
use App\Models\PenyesuaianStok;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * Retur ke supplier dan pemusnahan stok. Berkas PDF-nya dilayani
 * langsung oleh controller web, lewat route api.v1.penyesuaian.pdf.
 */
class PenyesuaianStokController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $saringan = $request->validate([
            'jenis' => ['nullable', Rule::in([PenyesuaianStok::JENIS_RETUR, PenyesuaianStok::JENIS_MUSNAH])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $user = $request->user();

        $dokumen = PenyesuaianStok::query()
            ->forUser($user)
            ->with(['user', 'supplier'])
            ->withCount('detail')
            ->when($saringan['jenis'] ?? null, fn ($query, $jenis) => $query->where('jenis', $jenis))
            ->latest('tanggal')
            ->latest('id')
            ->paginate($saringan['per_page'] ?? 10)
            ->withQueryString();

        return PenyesuaianStokResource::collection($dokumen)->additional([
            'ringkasan' => [
                'nilai_bulan_ini' => PenyesuaianStok::nilaiBulanIni($user),
                'jumlah_batch_kadaluarsa' => BatchObat::query()->forUser($user)->kadaluarsa()->count(),
            ],
        ]);
    }

    public function show(Request $request, PenyesuaianStok $penyesuaian): PenyesuaianStokResource
    {
        abort_unless($penyesuaian->apotek === $request->user()->apotek, 404);

        return new PenyesuaianStokResource($penyesuaian->load(['user', 'supplier', 'detail.obat', 'detail.batch']));
    }

    /**
     * Kirim hanya batch yang dipilih. no_dokumen boleh dikosongkan,
     * nanti dibuatkan otomatis (RTR-... untuk retur, BAP-... untuk pemusnahan).
     */
    public function store(Request $request, SimpanPenyesuaianStok $simpan): JsonResponse
    {
        $data = $request->validate([
            ...SimpanPenyesuaianStok::aturanDokumen(),
            'no_dokumen' => ['nullable', 'string', 'max:50', 'unique:penyesuaian_stok,no_dokumen'],
            'item' => ['required', 'array', 'min:1'],
            'item.*.batch_obat_id' => ['required', 'integer', 'distinct', 'exists:batch_obat,id'],
            'item.*.jumlah' => ['required', 'integer', 'min:1'],
            'item.*.alasan' => ['required', Rule::in(PenyesuaianStok::ALASAN)],
        ], [
            ...SimpanPenyesuaianStok::pesan(),
            'item.required' => 'Pilih minimal satu batch dan isi jumlahnya.',
            'item.*.batch_obat_id.distinct' => 'Batch yang sama tidak boleh ditulis dua kali.',
        ]);

        $data['no_dokumen'] ??= SimpanPenyesuaianStok::nomorDokumenBerikutnya($data['jenis']);

        $dokumen = $simpan($request->user(), $data, $data['item']);

        return (new PenyesuaianStokResource($dokumen->load(['user', 'supplier', 'detail.obat', 'detail.batch'])))
            ->response()
            ->setStatusCode(201);
    }
}
