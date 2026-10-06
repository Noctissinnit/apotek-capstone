<?php

namespace App\Queries;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Angka dan daftar ringkas untuk dashboard admin, dashboard kasir, dan
 * monitoring stok. Dipakai bersama halaman web dan API, dan semuanya
 * dibatasi pada apotek user yang login.
 */
class RingkasanDashboard
{
    /**
     * @return array{totalObat: int, stokMenipis: int, totalSupplier: int, totalPembelianBulanIni: float, pembelianTerbaru: Collection<int, Pembelian>}
     */
    public function admin(User $user): array
    {
        $obat = Obat::query()->forUser($user);
        $pembelian = Pembelian::query()->forUser($user);

        return [
            'totalObat' => (clone $obat)->count(),
            'stokMenipis' => (clone $obat)->stokMenipis()->count(),
            // supplier adalah data bersama kedua apotek
            'totalSupplier' => Supplier::count(),
            'totalPembelianBulanIni' => (float) (clone $pembelian)
                ->whereBetween('tanggal_pembelian', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total'),
            'pembelianTerbaru' => (clone $pembelian)->with('supplier')->latest('tanggal_pembelian')->latest('id')->take(5)->get(),
        ];
    }

    /**
     * @return array{totalObat: int, obatMenipis: Collection<int, Obat>, batchPerluPerhatian: Collection<int, BatchObat>, riwayatTerakhir: Collection<int, Penjualan>}
     */
    public function kasir(User $user): array
    {
        $obat = Obat::query()->forUser($user);

        return [
            'totalObat' => (clone $obat)->count(),
            'obatMenipis' => (clone $obat)->stokMenipis()->orderBy('stok')->get(),
            'batchPerluPerhatian' => $this->batchPerluPerhatian($user)->get(),
            'riwayatTerakhir' => Penjualan::query()
                ->forUser($user)
                ->with('detail.obat')
                ->latest('tanggal_penjualan')
                ->take(3)
                ->get(),
        ];
    }

    /**
     * Batch dikelompokkan per tingkat peringatan kadaluarsa.
     *
     * @return array{totalObat: int, obatMenipis: Collection<int, Obat>, batchKadaluarsa: Collection<int, BatchObat>, batchSegeraHabis: Collection<int, BatchObat>, batchSiapkanRetur: Collection<int, BatchObat>, ambang: array{segera_habis: int, siapkan_retur: int}}
     */
    public function monitoring(User $user): array
    {
        $obat = Obat::query()->forUser($user);

        $batch = $this->batchPerluPerhatian($user)
            ->get()
            ->groupBy(fn (BatchObat $item) => $item->tingkatKadaluarsa());

        return [
            'totalObat' => (clone $obat)->count(),
            'obatMenipis' => (clone $obat)->stokMenipis()->orderBy('stok')->get(),
            'batchKadaluarsa' => $batch->get('kadaluarsa', new Collection),
            'batchSegeraHabis' => $batch->get('segera_habis', new Collection),
            'batchSiapkanRetur' => $batch->get('siapkan_retur', new Collection),
            'ambang' => config('apotek.peringatan_kadaluarsa'),
        ];
    }

    /**
     * Batch yang stoknya masih ada dan tanggalnya sudah dekat atau lewat.
     *
     * @return Builder<BatchObat>
     */
    private function batchPerluPerhatian(User $user)
    {
        return BatchObat::query()
            ->forUser($user)
            ->with('obat')
            ->where('stok', '>', 0)
            ->whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', now()->addDays(config('apotek.peringatan_kadaluarsa.siapkan_retur')))
            ->orderBy('tanggal_kadaluarsa');
    }
}
