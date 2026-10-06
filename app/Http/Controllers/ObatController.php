<?php

namespace App\Http\Controllers;

use App\Actions\SimpanObat;
use App\Models\Kategori;
use App\Models\Obat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ObatController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $sortOption = $request->string('sort_option')->toString();
        $sort = $request->string('sort_by')->toString() ?: $request->string('sort')->toString();
        $direction = $request->string('direction')->lower()->toString();

        if ($sortOption !== '') {
            [$sort, $direction] = array_pad(explode('|', $sortOption, 2), 2, 'asc');
        }

        $sort = array_key_exists($sort, Obat::KOLOM_URUT) ? $sort : 'kode';
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        $obat = Obat::query()
            ->forUser($user)
            ->with('kategoriRelasi')
            ->saring([
                'search' => $request->string('search')->toString(),
                'kategori_id' => $request->input('kategori_id'),
                'satuan' => $request->string('satuan')->toString(),
                'sort' => $sort,
                'direction' => $direction,
            ])
            ->paginate(10)
            ->withQueryString();

        $kategori = Kategori::orderBy('nama_kategori')->get();
        $satuan = Obat::query()->forUser($user)->whereNotNull('satuan')->distinct()->orderBy('satuan')->pluck('satuan');

        return view('admin.obat.index', compact('obat', 'sort', 'direction', 'kategori', 'satuan'));
    }

    public function create(Request $request): View
    {
        $data = ['kategori' => Kategori::orderBy('nama_kategori')->get()];

        // permintaan dari modal cukup dibalas potongan isinya, bukan halaman penuh
        return $request->header('HX-Request')
            ? view('admin.obat.modal-form', $data + ['obat' => new Obat])
            : view('admin.obat.create', $data);
    }

    public function store(Request $request, SimpanObat $simpan): Response|RedirectResponse
    {
        try {
            $data = $request->validate(SimpanObat::aturan());
        } catch (ValidationException $gagal) {
            return $this->tanggapiIsianSalah($request, $gagal, new Obat);
        }

        $simpan->buat($request->user(), $data, $request->file('gambar'));

        return $this->kembaliKeDaftar($request, 'Data obat berhasil ditambahkan.');
    }

    public function show(Request $request, Obat $obat): View
    {
        $this->ensureSameApotek($obat);

        return view($request->header('HX-Request') ? 'admin.obat.modal-detail' : 'admin.obat.show', compact('obat'));
    }

    public function edit(Request $request, Obat $obat): View
    {
        $this->ensureSameApotek($obat);

        $data = ['obat' => $obat, 'kategori' => Kategori::orderBy('nama_kategori')->get()];

        return $request->header('HX-Request')
            ? view('admin.obat.modal-form', $data)
            : view('admin.obat.edit', $data);
    }

    public function update(Request $request, Obat $obat, SimpanObat $simpan): Response|RedirectResponse
    {
        $this->ensureSameApotek($obat);

        try {
            $data = $request->validate(SimpanObat::aturan($obat));
        } catch (ValidationException $gagal) {
            return $this->tanggapiIsianSalah($request, $gagal, $obat);
        }

        $simpan->ubah($request->user(), $obat, $data, $request->file('gambar'), $request->boolean('hapus_gambar'));

        return $this->kembaliKeDaftar($request, 'Data obat berhasil diperbarui.');
    }

    public function destroy(Obat $obat): RedirectResponse
    {
        $this->ensureSameApotek($obat);
        $obat->delete();

        return to_route('obat.index')->with('success', 'Data obat berhasil dihapus.');
    }

    /**
     * Setelah tersimpan, kembali ke daftar obat. Kalau permintaannya datang
     * dari modal, cukup beri tanda agar modal ditutup dan daftarnya disegarkan.
     */
    private function kembaliKeDaftar(Request $request, string $pesan): Response|RedirectResponse
    {
        // Permintaan dari modal tidak boleh dijawab dengan pengalihan: htmx akan
        // mengikutinya lalu memasukkan seluruh halaman ke dalam modal. Cukup
        // balas tanpa isi dan minta htmx memuat ulang halamannya.
        if ($request->header('HX-Request')) {
            session()->flash('success', $pesan);

            return response()->noContent()->header('HX-Refresh', 'true');
        }

        return to_route('obat.index')->with('success', $pesan);
    }

    /**
     * Isian yang salah dari modal dijawab dengan formnya lagi beserta pesan
     * kesalahan, bukan pengalihan, supaya modal tetap terbuka.
     */
    private function tanggapiIsianSalah(Request $request, ValidationException $gagal, Obat $obat): Response
    {
        if (! $request->header('HX-Request')) {
            throw $gagal;
        }

        // supaya isian yang sudah diketik tidak hilang saat form ditampilkan ulang
        $request->flash();

        return response()->view('admin.obat.modal-form', [
            'obat' => $obat,
            'kategori' => Kategori::orderBy('nama_kategori')->get(),
            'errors' => (new ViewErrorBag)->put('default', $gagal->validator->errors()),
        ], 422);
    }

    private function ensureSameApotek(Obat $obat): void
    {
        abort_unless($obat->bolehDiaksesOleh(request()->user()), 404);
    }
}
