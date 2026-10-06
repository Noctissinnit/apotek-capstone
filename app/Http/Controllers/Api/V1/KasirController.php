<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\ProsesCheckout;
use App\Http\Controllers\Controller;
use App\Http\Resources\PenjualanResource;
use App\Models\User;
use App\Support\Keranjang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Keranjang dan pembayaran di meja kasir. Keranjangnya disimpan di session
 * login, jadi sama dengan keranjang di layar kasir web untuk user yang sama.
 */
class KasirController extends Controller
{
    public function __construct(private Keranjang $keranjang) {}

    public function keranjang(Request $request): JsonResponse
    {
        return $this->balasKeranjang($request->user());
    }

    /**
     * Tambah obat ke keranjang. Bila obatnya sudah ada, jumlahnya ditambahkan.
     */
    public function tambah(Request $request): JsonResponse
    {
        $data = $request->validate([
            'obat_id' => ['required', 'integer', 'exists:obat,id'],
            'jumlah' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        $this->keranjang->tambah($request->user(), $data['obat_id'], $data['jumlah'] ?? 1);

        return $this->balasKeranjang($request->user());
    }

    /**
     * Ganti jumlah satu obat di keranjang.
     */
    public function atur(Request $request, int $obat): JsonResponse
    {
        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $this->keranjang->atur($request->user(), $obat, $data['jumlah']);

        return $this->balasKeranjang($request->user());
    }

    public function hapus(Request $request, int $obat): JsonResponse
    {
        $this->keranjang->hapus($obat);

        return $this->balasKeranjang($request->user());
    }

    public function kosongkan(Request $request): JsonResponse
    {
        $this->keranjang->kosongkan();

        return $this->balasKeranjang($request->user());
    }

    /**
     * Bayar seluruh isi keranjang. Stok dikurangi dari batch yang paling dekat
     * kadaluarsa lebih dulu, lalu keranjang dikosongkan.
     */
    public function checkout(Request $request, ProsesCheckout $proses): JsonResponse
    {
        $data = $request->validate(ProsesCheckout::aturan(), ProsesCheckout::pesan());

        $penjualan = $proses($request->user(), $this->keranjang->isi(), $data);

        $this->keranjang->kosongkan();

        return (new PenjualanResource($penjualan->load(['user', 'detail.obat'])))
            ->response()
            ->setStatusCode(201);
    }

    private function balasKeranjang(User $user): JsonResponse
    {
        $rincian = $this->keranjang->rincian($user);

        return response()->json(['data' => [
            'item' => $rincian->map(fn (array $baris) => [
                'obat_id' => $baris['obat']->id,
                'kode_obat' => $baris['obat']->kode_obat,
                'nama_obat' => $baris['obat']->nama_obat,
                'satuan' => $baris['obat']->satuan,
                'gambar_url' => $baris['obat']->urlGambar(),
                'jumlah' => $baris['jumlah'],
                'harga_jual' => $baris['harga_cents'] / 100,
                'subtotal' => $baris['subtotal_cents'] / 100,
            ]),
            'jumlah_item' => $rincian->sum('jumlah'),
            'total' => $rincian->sum('subtotal_cents') / 100,
            // status: siap (ada svg) | belum_diatur | tidak_terbaca; null bila keranjang kosong
            'qris' => $this->keranjang->qris($user, $rincian),
        ]]);
    }
}
