<?php

namespace App\Actions;

use App\Models\BatchObat;
use App\Models\DetailPembelian;
use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Barang masuk dari supplier (CR-01-B). Dipakai bersama halaman web dan API.
 *
 * Satu baris faktur = satu batch. Obat yang sama boleh ditulis beberapa baris
 * bila kirimannya terdiri dari batch berbeda.
 */
class SimpanBarangMasuk
{
    /**
     * @return array<string, mixed>
     */
    public static function aturan(): array
    {
        return [
            'no_faktur' => ['required', 'string', 'max:50', 'unique:pembelian,no_faktur'],
            'supplier_id' => ['required', 'integer', 'exists:supplier,id'],
            'tanggal_pembelian' => ['required', 'date'],
            'keterangan' => ['nullable', 'string'],

            'item' => ['required', 'array', 'min:1'],
            'item.*.obat_id' => ['required', 'integer', 'exists:obat,id'],
            'item.*.satuan_input' => ['required', Rule::in([DetailPembelian::SATUAN_BOX, DetailPembelian::SATUAN_TERKECIL])],
            'item.*.jumlah' => ['required', 'integer', 'min:1'],
            'item.*.harga_beli' => ['required', 'numeric', 'min:0'],
            'item.*.diskon_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item.*.no_batch' => ['required', 'string', 'max:50'],
            'item.*.tanggal_kadaluarsa' => ['nullable', 'date', 'after:today'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function pesan(): array
    {
        return [
            'item.required' => 'Faktur harus berisi minimal satu obat.',
            'item.*.no_batch.required' => 'Nomor batch wajib diisi.',
            'item.*.tanggal_kadaluarsa.after' => 'Tanggal kadaluarsa harus setelah hari ini.',
        ];
    }

    public static function nomorFakturBerikutnya(): string
    {
        return 'FK-'.now()->format('Ymd').'-'.str_pad((string) (Pembelian::whereDate('created_at', today())->count() + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Simpan faktur, bentuk atau tambah batch, dan tambah stok obat.
     *
     * @param  array<string, mixed>  $data  hasil validasi dengan aturan()
     *
     * @throws ValidationException
     */
    public function __invoke(User $user, array $data): Pembelian
    {
        return DB::transaction(function () use ($data, $user): Pembelian {
            $pembelian = Pembelian::create([
                'no_faktur' => $data['no_faktur'],
                'supplier_id' => $data['supplier_id'],
                'user_id' => $user->id,
                'apotek' => $user->apotek,
                'tanggal_pembelian' => $data['tanggal_pembelian'],
                'total' => 0,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            $total = 0;

            foreach ($data['item'] as $baris) {
                // obat dikunci agar penambahan stok tidak bentrok dengan transaksi lain
                $obat = Obat::query()
                    ->forUser($user)
                    ->lockForUpdate()
                    ->find($baris['obat_id']);

                if (! $obat) {
                    throw ValidationException::withMessages([
                        'item' => 'Ada obat yang bukan milik apotek Anda.',
                    ]);
                }

                $hitung = $this->hitungBaris($obat, $baris);
                $total += $hitung['subtotal'];

                $batch = $this->simpanBatch($obat, $baris, $hitung);

                DetailPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'obat_id' => $obat->id,
                    'batch_obat_id' => $batch->id,
                    'satuan_input' => $baris['satuan_input'],
                    'isi_per_box' => $obat->isi_per_box,
                    'jumlah' => $baris['jumlah'],
                    'jumlah_satuan' => $hitung['jumlah_satuan'],
                    'harga_beli' => $baris['harga_beli'],
                    'diskon_persen' => $baris['diskon_persen'] ?? null,
                    'modal_per_satuan' => $hitung['modal_per_satuan'],
                    'subtotal' => $hitung['subtotal'],
                    'no_batch' => $baris['no_batch'],
                    'tanggal_kadaluarsa' => $baris['tanggal_kadaluarsa'] ?? null,
                ]);

                $obat->increment('stok', $hitung['jumlah_satuan']);
            }

            $pembelian->update(['total' => $total]);

            return $pembelian;
        });
    }

    /**
     * Hitung jumlah dalam satuan terkecil, modal per satuan, dan subtotal.
     *
     * @param  array<string, mixed>  $baris
     * @return array{jumlah_satuan: int, modal_per_satuan: float, subtotal: float}
     */
    private function hitungBaris(Obat $obat, array $baris): array
    {
        $jumlah = (int) $baris['jumlah'];
        $harga = (float) $baris['harga_beli'];
        $diskon = (float) ($baris['diskon_persen'] ?? 0);

        // kalau diketik dalam box, satu box berisi isi_per_box satuan terkecil
        $isiPerBox = $baris['satuan_input'] === DetailPembelian::SATUAN_BOX ? max(1, $obat->isi_per_box) : 1;

        $jumlahSatuan = $jumlah * $isiPerBox;
        $hargaSetelahDiskon = $harga * (1 - $diskon / 100);

        return [
            'jumlah_satuan' => $jumlahSatuan,
            'modal_per_satuan' => round($hargaSetelahDiskon / $isiPerBox, 2),
            'subtotal' => round($jumlah * $hargaSetelahDiskon, 2),
        ];
    }

    /**
     * Tambahkan stok ke batch. Batch dengan nomor sama pada obat yang sama
     * dianggap kiriman lanjutan, jadi stoknya ditambahkan ke batch tersebut.
     *
     * @param  array<string, mixed>  $baris
     * @param  array{jumlah_satuan: int, modal_per_satuan: float, subtotal: float}  $hitung
     */
    private function simpanBatch(Obat $obat, array $baris, array $hitung): BatchObat
    {
        $batch = BatchObat::firstOrNew([
            'obat_id' => $obat->id,
            'no_batch' => $baris['no_batch'],
        ]);

        $batch->fill([
            'tanggal_kadaluarsa' => $baris['tanggal_kadaluarsa'] ?? $batch->tanggal_kadaluarsa,
            'modal_per_satuan' => $hitung['modal_per_satuan'],
            'stok' => $batch->stok + $hitung['jumlah_satuan'],
        ]);

        $batch->status ??= BatchObat::STATUS_AKTIF;
        $batch->save();

        return $batch;
    }
}
