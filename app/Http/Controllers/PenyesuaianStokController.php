<?php

namespace App\Http\Controllers;

use App\Actions\SimpanPenyesuaianStok;
use App\Models\BatchObat;
use App\Models\PenyesuaianStok;
use App\Models\Supplier;
use Dompdf\Dompdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Retur dan pemusnahan stok (CR-01-D).
 *
 * Keduanya mengurangi stok batch. Bedanya pada dokumen yang dicetak dan
 * cara nilainya dilaporkan: barang retur biasanya diganti supplier,
 * barang musnah dihitung sebagai kerugian penuh.
 */
class PenyesuaianStokController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $nilaiBulanIni = PenyesuaianStok::nilaiBulanIni($user);

        return view('admin.penyesuaian.index', [
            'dokumen' => PenyesuaianStok::query()
                ->forUser($user)
                ->with(['user', 'supplier'])
                ->withCount('detail')
                ->latest('tanggal')
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),
            'nilaiRetur' => $nilaiBulanIni[PenyesuaianStok::JENIS_RETUR],
            'nilaiMusnah' => $nilaiBulanIni[PenyesuaianStok::JENIS_MUSNAH],
            'jumlahBatchKadaluarsa' => BatchObat::query()->forUser($user)->kadaluarsa()->count(),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('admin.penyesuaian.create', [
            // batch kadaluarsa ditampilkan lebih dulu karena itu yang biasanya ditindaklanjuti
            'batchKadaluarsa' => BatchObat::query()->forUser($user)->kadaluarsa()->with('obat')->orderBy('tanggal_kadaluarsa')->get(),
            'batchLain' => BatchObat::query()->forUser($user)->siapDijual()->with('obat')->get(),
            'supplier' => Supplier::orderBy('nama_supplier')->get(),
            'nomorUsulan' => SimpanPenyesuaianStok::nomorDokumenBerikutnya($request->input('jenis', PenyesuaianStok::JENIS_MUSNAH)),
        ]);
    }

    public function store(Request $request, SimpanPenyesuaianStok $simpan): RedirectResponse
    {
        $data = $request->validate([
            ...SimpanPenyesuaianStok::aturanDokumen(),
            'item' => ['required', 'array', 'min:1'],
            'item.*.pilih' => ['nullable'],
            'item.*.batch_obat_id' => ['required', 'integer', 'exists:batch_obat,id'],
            'item.*.jumlah' => ['nullable', 'integer', 'min:0'],
            'item.*.alasan' => ['required', Rule::in(PenyesuaianStok::ALASAN)],
        ], SimpanPenyesuaianStok::pesan());

        // baris tanpa centang diabaikan
        $dipilih = collect($data['item'])->filter(fn (array $baris) => ! empty($baris['pilih']) && (int) $baris['jumlah'] > 0);

        if ($dipilih->isEmpty()) {
            throw ValidationException::withMessages([
                'item' => 'Pilih minimal satu batch dan isi jumlahnya.',
            ]);
        }

        $dokumen = $simpan($request->user(), $data, $dipilih);

        $kata = $dokumen->isRetur() ? 'Retur' : 'Pemusnahan';

        return to_route('penyesuaian.show', $dokumen)
            ->with('success', "{$kata} {$dokumen->no_dokumen} tersimpan. Stok sudah dikurangi.");
    }

    public function show(Request $request, PenyesuaianStok $penyesuaian): View
    {
        abort_unless($penyesuaian->apotek === $request->user()->apotek, 404);

        $penyesuaian->load(['user', 'supplier', 'detail.obat', 'detail.batch']);

        return view('admin.penyesuaian.show', ['dokumen' => $penyesuaian]);
    }

    public function pdf(Request $request, PenyesuaianStok $penyesuaian): Response
    {
        abort_unless($penyesuaian->apotek === $request->user()->apotek, 404);

        $penyesuaian->load(['user', 'supplier', 'detail.obat', 'detail.batch']);

        $dompdf = new Dompdf;
        $dompdf->loadHtml(view('admin.penyesuaian.pdf', ['dokumen' => $penyesuaian])->render(), 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $namaBerkas = ($penyesuaian->isRetur() ? 'nota-retur-' : 'berita-acara-pemusnahan-').$penyesuaian->no_dokumen.'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$namaBerkas.'"',
        ]);
    }
}
