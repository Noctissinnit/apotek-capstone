<?php

namespace App\Actions;

use App\Models\BatchObat;
use App\Models\DetailPenyesuaianStok;
use App\Models\PenyesuaianStok;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Retur dan pemusnahan stok (CR-01-D). Dipakai bersama halaman web dan API.
 *
 * Keduanya mengurangi stok batch. Bedanya pada dokumen yang dicetak dan
 * cara nilainya dilaporkan: barang retur biasanya diganti supplier,
 * barang musnah dihitung sebagai kerugian penuh.
 */
class SimpanPenyesuaianStok
{
    /**
     * Aturan kepala dokumen. Aturan baris berbeda antara web (pakai kotak
     * centang) dan API (hanya baris yang dipilih), jadi ditulis di pemanggilnya.
     *
     * @return array<string, mixed>
     */
    public static function aturanDokumen(): array
    {
        return [
            'no_dokumen' => ['required', 'string', 'max:50', 'unique:penyesuaian_stok,no_dokumen'],
            'jenis' => ['required', Rule::in([PenyesuaianStok::JENIS_RETUR, PenyesuaianStok::JENIS_MUSNAH])],
            'tanggal' => ['required', 'date'],
            'supplier_id' => ['nullable', 'integer', 'exists:supplier,id', 'required_if:jenis,'.PenyesuaianStok::JENIS_RETUR],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function pesan(): array
    {
        return [
            'supplier_id.required_if' => 'Retur harus menyebutkan supplier tujuan.',
        ];
    }

    public static function nomorDokumenBerikutnya(string $jenis): string
    {
        $awalan = $jenis === PenyesuaianStok::JENIS_RETUR ? 'RTR' : 'BAP';
        $urutan = PenyesuaianStok::where('jenis', $jenis)->whereDate('created_at', today())->count() + 1;

        return $awalan.'-'.now()->format('Ymd').'-'.str_pad((string) $urutan, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $dokumen  hasil validasi dengan aturanDokumen()
     * @param  iterable<array{batch_obat_id: int|string, jumlah: int|string, alasan: string}>  $baris  hanya batch yang dipilih
     *
     * @throws ValidationException
     */
    public function __invoke(User $user, array $dokumen, iterable $baris): PenyesuaianStok
    {
        return DB::transaction(function () use ($user, $dokumen, $baris): PenyesuaianStok {
            $penyesuaian = PenyesuaianStok::create([
                'no_dokumen' => $dokumen['no_dokumen'],
                'jenis' => $dokumen['jenis'],
                'tanggal' => $dokumen['tanggal'],
                'user_id' => $user->id,
                'apotek' => $user->apotek,
                'supplier_id' => $dokumen['jenis'] === PenyesuaianStok::JENIS_RETUR ? ($dokumen['supplier_id'] ?? null) : null,
                'total_nilai' => 0,
                'keterangan' => $dokumen['keterangan'] ?? null,
            ]);

            $totalNilai = 0;

            foreach ($baris as $item) {
                $batch = BatchObat::query()
                    ->forUser($user)
                    ->lockForUpdate()
                    ->find($item['batch_obat_id']);

                if (! $batch) {
                    throw ValidationException::withMessages([
                        'item' => 'Ada batch yang bukan milik apotek Anda.',
                    ]);
                }

                $jumlah = (int) $item['jumlah'];

                if ($jumlah > $batch->stok) {
                    throw ValidationException::withMessages([
                        'item' => "Jumlah untuk batch {$batch->no_batch} melebihi stok yang ada ({$batch->stok}).",
                    ]);
                }

                $nilai = round((float) $batch->modal_per_satuan * $jumlah, 2);
                $totalNilai += $nilai;

                DetailPenyesuaianStok::create([
                    'penyesuaian_stok_id' => $penyesuaian->id,
                    'batch_obat_id' => $batch->id,
                    'obat_id' => $batch->obat_id,
                    'jumlah' => $jumlah,
                    'modal_per_satuan' => $batch->modal_per_satuan,
                    'nilai' => $nilai,
                    'alasan' => $item['alasan'],
                ]);

                $batch->decrement('stok', $jumlah);
                $batch->obat()->first()?->decrement('stok', $jumlah);
            }

            $penyesuaian->update(['total_nilai' => $totalNilai]);

            return $penyesuaian;
        });
    }
}
