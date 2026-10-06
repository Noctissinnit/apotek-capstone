<?php

namespace App\Http\Controllers;

use App\Actions\SimpanBarangMasuk;
use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Barang masuk dari supplier (CR-01-B). Aturan dan cara simpannya ada di
 * App\Actions\SimpanBarangMasuk, dipakai bersama API.
 */
class PembelianController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.pembelian.index', [
            'pembelian' => Pembelian::query()
                ->forUser($request->user())
                ->with(['supplier', 'user'])
                ->withCount('detail')
                ->latest('tanggal_pembelian')
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.pembelian.create', [
            'supplier' => Supplier::orderBy('nama_supplier')->get(),
            'obat' => Obat::query()->forUser($request->user())->orderBy('nama_obat')->get(),
            'nomorUsulan' => SimpanBarangMasuk::nomorFakturBerikutnya(),
        ]);
    }

    public function store(Request $request, SimpanBarangMasuk $simpan): RedirectResponse
    {
        $data = $request->validate(SimpanBarangMasuk::aturan(), SimpanBarangMasuk::pesan());

        $pembelian = $simpan($request->user(), $data);

        return to_route('pembelian.show', $pembelian)
            ->with('success', "Faktur {$pembelian->no_faktur} tersimpan. Stok sudah bertambah.");
    }

    public function show(Request $request, Pembelian $pembelian): View
    {
        abort_unless($pembelian->apotek === $request->user()->apotek, 404);

        $pembelian->load(['supplier', 'user', 'detail.obat', 'detail.batch']);

        return view('admin.pembelian.show', compact('pembelian'));
    }
}
