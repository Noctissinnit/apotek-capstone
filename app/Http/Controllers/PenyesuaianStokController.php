<?php

namespace App\Http\Controllers;

use App\Models\BatchObat;
use App\Models\DetailPenyesuaianStok;
use App\Models\PenyesuaianStok;
use App\Models\Supplier;
use Dompdf\Dompdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
        $awalBulan = now()->startOfMonth();

        $nilaiBulanIni = PenyesuaianStok::query()
            ->forUser($user)
            ->whereDate('tanggal', '>=', $awalBulan)
            ->selectRaw('jenis, sum(total_nilai) as jumlah')
            ->groupBy('jenis')
            ->pluck('jumlah', 'jenis');

        return view('admin.penyesuaian.index', [
            'dokumen' => PenyesuaianStok::query()
                ->forUser($user)
                ->with(['user', 'supplier'])
                ->withCount('detail')
                ->latest('tanggal')
                ->latest('id')
                ->paginate(10)
                ->withQueryString(),
            'nilaiRetur' => (float) ($nilaiBulanIni[PenyesuaianStok::JENIS_RETUR] ?? 0),
            'nilaiMusnah' => (float) ($nilaiBulanIni[PenyesuaianStok::JENIS_MUSNAH] ?? 0),
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
            'nomorUsulan' => $this->nomorDokumenBerikutnya($request->input('jenis', PenyesuaianStok::JENIS_MUSNAH)),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $request->user();

        // baris tanpa centang diabaikan
        $dipilih = collect($data['item'])->filter(fn (array $baris) => ! empty($baris['pilih']) && (int) $baris['jumlah'] > 0);

        if ($dipilih->isEmpty()) {
            throw ValidationException::withMessages([
                'item' => 'Pilih minimal satu batch dan isi jumlahnya.',
            ]);
        }

        $dokumen = DB::transaction(function () use ($data, $dipilih, $user): PenyesuaianStok {
            $dokumen = PenyesuaianStok::create([
                'no_dokumen' => $data['no_dokumen'],
                'jenis' => $data['jenis'],
                'tanggal' => $data['tanggal'],
                'user_id' => $user->id,
                'apotek' => $user->apotek,
                'supplier_id' => $data['jenis'] === PenyesuaianStok::JENIS_RETUR ? ($data['supplier_id'] ?? null) : null,
                'total_nilai' => 0,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $totalNilai = 0;

            foreach ($dipilih as $baris) {
                $batch = BatchObat::query()
                    ->forUser($user)
                    ->lockForUpdate()
                    ->find($baris['batch_obat_id']);

                if (! $batch) {
                    throw ValidationException::withMessages([
                        'item' => 'Ada batch yang bukan milik apotek Anda.',
                    ]);
                }

                $jumlah = (int) $baris['jumlah'];

                if ($jumlah > $batch->stok) {
                    throw ValidationException::withMessages([
                        'item' => "Jumlah untuk batch {$batch->no_batch} melebihi stok yang ada ({$batch->stok}).",
                    ]);
                }

                $nilai = round((float) $batch->modal_per_satuan * $jumlah, 2);
                $totalNilai += $nilai;

                DetailPenyesuaianStok::create([
                    'penyesuaian_stok_id' => $dokumen->id,
                    'batch_obat_id' => $batch->id,
                    'obat_id' => $batch->obat_id,
                    'jumlah' => $jumlah,
                    'modal_per_satuan' => $batch->modal_per_satuan,
                    'nilai' => $nilai,
                    'alasan' => $baris['alasan'],
                ]);

                $batch->decrement('stok', $jumlah);
                $batch->obat()->first()?->decrement('stok', $jumlah);
            }

            $dokumen->update(['total_nilai' => $totalNilai]);

            return $dokumen;
        });

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

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'no_dokumen' => ['required', 'string', 'max:50', 'unique:penyesuaian_stok,no_dokumen'],
            'jenis' => ['required', Rule::in([PenyesuaianStok::JENIS_RETUR, PenyesuaianStok::JENIS_MUSNAH])],
            'tanggal' => ['required', 'date'],
            'supplier_id' => ['nullable', 'integer', 'exists:supplier,id', Rule::requiredIf($request->input('jenis') === PenyesuaianStok::JENIS_RETUR)],
            'keterangan' => ['nullable', 'string'],

            'item' => ['required', 'array', 'min:1'],
            'item.*.pilih' => ['nullable'],
            'item.*.batch_obat_id' => ['required', 'integer', 'exists:batch_obat,id'],
            'item.*.jumlah' => ['nullable', 'integer', 'min:0'],
            'item.*.alasan' => ['required', Rule::in(PenyesuaianStok::ALASAN)],
        ], [
            'supplier_id.required' => 'Retur harus menyebutkan supplier tujuan.',
        ]);
    }

    private function nomorDokumenBerikutnya(string $jenis): string
    {
        $awalan = $jenis === PenyesuaianStok::JENIS_RETUR ? 'RTR' : 'BAP';
        $urutan = PenyesuaianStok::where('jenis', $jenis)->whereDate('created_at', today())->count() + 1;

        return $awalan.'-'.now()->format('Ymd').'-'.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
    }
}
