<?php

namespace App\Actions;

use App\Models\DetailPenjualan;
use App\Models\Obat;
use App\Models\PemakaianBatch;
use App\Models\Penjualan;
use App\Models\User;
use App\Support\Rupiah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Pembayaran di meja kasir: catat penjualan, kurangi stok obat, dan ambil
 * stok dari batch yang paling dekat kadaluarsa lebih dulu (FEFO).
 * Dipakai bersama layar kasir web dan API.
 */
class ProsesCheckout
{
    /**
     * Cara pembayaran yang dilayani di meja kasir. Semuanya dibayar langsung di tempat:
     * QRIS lewat stiker QR milik apotek, kartu debit lewat mesin EDC bank.
     * Sistem hanya mencatat, tidak memproses pembayaran.
     */
    public const TUNAI = 'Tunai';

    public const METODE_PEMBAYARAN = [self::TUNAI, 'QRIS', 'Transfer/Debit'];

    /**
     * @return array<string, mixed>
     */
    public static function aturan(): array
    {
        return [
            'metode_pembayaran' => ['required', Rule::in(self::METODE_PEMBAYARAN)],
            // hanya relevan untuk tunai; QRIS dan Transfer/Debit selalu pas
            'uang_diterima' => ['required_if:metode_pembayaran,'.self::TUNAI, 'nullable', 'numeric', 'min:0'],
            // bukti pembayaran non-tunai, boleh dikosongkan
            'no_referensi' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function pesan(): array
    {
        return [
            'uang_diterima.required_if' => 'Isi dulu uang yang diterima dari pembeli.',
        ];
    }

    /**
     * @param  array<int, int>  $keranjang  [obat_id => jumlah]
     * @param  array<string, mixed>  $data  hasil validasi dengan aturan()
     *
     * @throws ValidationException
     */
    public function __invoke(User $user, array $keranjang, array $data): Penjualan
    {
        if ($keranjang === []) {
            throw ValidationException::withMessages(['keranjang' => 'Keranjang masih kosong.']);
        }

        return DB::transaction(function () use ($user, $keranjang, $data): Penjualan {
            // Penyaringan apotek diulang di sini, bukan hanya saat menampilkan daftar,
            // supaya keranjang yang sudah telanjur berisi obat apotek lain tetap ditolak
            $daftarObat = Obat::query()
                ->forUser($user)
                ->whereIn('id', array_keys($keranjang))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalSen = 0;

            foreach ($keranjang as $obatId => $jumlah) {
                $obat = $daftarObat->get($obatId);

                if (! $obat) {
                    throw ValidationException::withMessages([
                        'keranjang' => 'Ada obat di keranjang yang bukan milik apotek Anda. Kosongkan keranjang lalu ulangi.',
                    ]);
                }

                if ($obat->stokSiapDijual() < $jumlah) {
                    throw ValidationException::withMessages([
                        'keranjang' => 'Stok salah satu obat berubah atau tidak mencukupi. Periksa kembali keranjang.',
                    ]);
                }

                $totalSen += Rupiah::keSen((string) $obat->harga_jual) * $jumlah;
            }

            // uang pembeli dicatat hanya untuk tunai, berikut kembaliannya
            $uangDiterimaSen = null;
            $kembalianSen = null;

            if ($data['metode_pembayaran'] === self::TUNAI) {
                $uangDiterimaSen = Rupiah::keSen((float) $data['uang_diterima']);

                if ($uangDiterimaSen < $totalSen) {
                    throw ValidationException::withMessages([
                        'uang_diterima' => 'Uang yang diterima kurang dari total belanja '.Rupiah::tampil($totalSen).'.',
                    ]);
                }

                $kembalianSen = $uangDiterimaSen - $totalSen;
            }

            $penjualan = Penjualan::create([
                'no_faktur' => 'TRX-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(5)),
                'user_id' => $user->id,
                'apotek' => $user->apotek,
                'tanggal_penjualan' => now(),
                'total' => Rupiah::dariSen($totalSen),
                'uang_diterima' => $uangDiterimaSen === null ? null : Rupiah::dariSen($uangDiterimaSen),
                'kembalian' => $kembalianSen === null ? null : Rupiah::dariSen($kembalianSen),
                'metode_pembayaran' => $data['metode_pembayaran'],
                // tunai tidak punya nomor bukti
                'no_referensi' => $data['metode_pembayaran'] === self::TUNAI ? null : ($data['no_referensi'] ?? null),
            ]);

            foreach ($keranjang as $obatId => $jumlah) {
                $obat = $daftarObat->get($obatId);

                $detail = DetailPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'obat_id' => $obat->id,
                    'jumlah' => $jumlah,
                    'harga_jual' => $obat->harga_jual,
                    'subtotal' => Rupiah::dariSen(Rupiah::keSen((string) $obat->harga_jual) * $jumlah),
                ]);

                $this->ambilDariBatch($detail, $obat, $jumlah);
                $obat->decrement('stok', $jumlah);
            }

            return $penjualan;
        });
    }

    /**
     * Kurangi stok dari batch yang paling dekat kadaluarsa lebih dulu.
     * Kalau batch terdepan tidak cukup, sisanya diambil dari batch berikutnya.
     * Kasir tidak memilih apa pun, ini berjalan sendiri saat pembayaran.
     */
    private function ambilDariBatch(DetailPenjualan $detail, Obat $obat, int $jumlah): void
    {
        $sisa = $jumlah;

        $daftarBatch = $obat->batch()
            ->siapDijual()
            ->lockForUpdate()
            ->get();

        foreach ($daftarBatch as $batch) {
            if ($sisa < 1) {
                break;
            }

            $diambil = min($sisa, $batch->stok);

            PemakaianBatch::create([
                'detail_penjualan_id' => $detail->id,
                'batch_obat_id' => $batch->id,
                'jumlah' => $diambil,
                'modal_per_satuan' => $batch->modal_per_satuan,
            ]);

            $batch->decrement('stok', $diambil);
            $sisa -= $diambil;
        }

        if ($sisa > 0) {
            // tidak seharusnya terjadi karena sudah diperiksa sebelumnya,
            // tetapi kalau sampai terjadi, seluruh transaksi dibatalkan
            throw ValidationException::withMessages([
                'keranjang' => "Stok {$obat->nama_obat} di catatan batch tidak mencukupi. Periksa data batch obat ini.",
            ]);
        }
    }
}
