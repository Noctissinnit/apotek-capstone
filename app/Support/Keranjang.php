<?php

namespace App\Support;

use App\Models\Obat;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Keranjang belanja kasir, disimpan di session sebagai [obat_id => jumlah].
 * Dipakai bersama layar kasir web dan API, karena keduanya memakai
 * session login yang sama (Sanctum mode cookie).
 */
class Keranjang
{
    private const KUNCI = 'kasir.cart';

    public function __construct(private Session $session) {}

    /** @return array<int, int> */
    public function isi(): array
    {
        return collect($this->session->get(self::KUNCI, []))
            ->mapWithKeys(fn ($jumlah, $id) => [(int) $id => (int) $jumlah])
            ->all();
    }

    public function kosong(): bool
    {
        return $this->isi() === [];
    }

    /**
     * Tambah jumlah obat di keranjang.
     *
     * @throws ValidationException bila stok siap jual tidak mencukupi
     */
    public function tambah(User $user, int $obatId, int $jumlah = 1): Obat
    {
        $obat = $this->obatMilik($user, $obatId);

        return $this->atur($user, $obat->id, ($this->isi()[$obat->id] ?? 0) + $jumlah);
    }

    /**
     * Ganti jumlah obat di keranjang menjadi angka tertentu.
     *
     * @throws ValidationException bila stok siap jual tidak mencukupi
     */
    public function atur(User $user, int $obatId, int $jumlah): Obat
    {
        $obat = $this->obatMilik($user, $obatId);

        // yang dihitung adalah stok yang boleh dijual, bukan seluruh stok:
        // batch kadaluarsa atau yang sedang ditahan tidak ikut
        $tersedia = $obat->stokSiapDijual();

        if ($tersedia < $jumlah) {
            throw ValidationException::withMessages([
                'jumlah' => "Stok {$obat->nama_obat} tidak mencukupi. Siap dijual: {$tersedia} {$obat->satuan}.",
            ]);
        }

        $isi = $this->isi();
        $isi[$obat->id] = $jumlah;
        $this->session->put(self::KUNCI, $isi);

        return $obat;
    }

    public function hapus(int $obatId): void
    {
        $isi = $this->isi();
        unset($isi[$obatId]);
        $this->session->put(self::KUNCI, $isi);
    }

    public function kosongkan(): void
    {
        $this->session->forget(self::KUNCI);
    }

    /**
     * Isi keranjang beserta harganya. Obat yang ternyata bukan milik apotek
     * user (atau sudah dihapus) dilewati.
     *
     * @return Collection<int, array{obat: Obat, jumlah: int, harga_cents: int, subtotal_cents: int}>
     */
    public function rincian(User $user): Collection
    {
        $isi = $this->isi();
        $daftarObat = Obat::query()
            ->forUser($user)
            ->whereIn('id', array_keys($isi))
            ->get()
            ->keyBy('id');

        return collect($isi)->map(function (int $jumlah, int $id) use ($daftarObat): ?array {
            $obat = $daftarObat->get($id);

            if (! $obat) {
                return null;
            }

            $harga = Rupiah::keSen((string) $obat->harga_jual);

            return [
                'obat' => $obat,
                'jumlah' => $jumlah,
                'harga_cents' => $harga,
                'subtotal_cents' => $harga * $jumlah,
            ];
        })->filter()->values();
    }

    /**
     * Kode QR berisi nominal belanja, supaya pembeli tinggal memindai.
     * Null bila keranjang kosong; status 'belum_diatur' bila kode QRIS apotek
     * belum diisi di .env; 'tidak_terbaca' bila kodenya salah salin.
     *
     * @param  Collection<int, array<string, mixed>>  $rincian  hasil rincian()
     * @return array{status: string, nominal: int, svg?: string}|null
     */
    public function qris(User $user, Collection $rincian): ?array
    {
        $nominal = (int) round($rincian->sum('subtotal_cents') / 100);

        if ($nominal < 1) {
            return null;
        }

        $kodeStatis = (string) (config('apotek.qris.'.$user->apotek) ?? '');

        if (trim($kodeStatis) === '') {
            return ['status' => 'belum_diatur', 'nominal' => $nominal];
        }

        $qris = new QrisDinamis;

        try {
            return [
                'status' => 'siap',
                'nominal' => $nominal,
                'svg' => $qris->gambarSvg($qris->dariStatis($kodeStatis, $nominal)),
            ];
        } catch (InvalidArgumentException $e) {
            // kode QRIS salah salin, jangan sampai menghalangi penjualan
            report($e);

            return ['status' => 'tidak_terbaca', 'nominal' => $nominal];
        }
    }

    private function obatMilik(User $user, int $obatId): Obat
    {
        // Obat milik apotek lain tidak boleh masuk keranjang
        return Obat::query()->forUser($user)->findOrFail($obatId);
    }
}
