<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PenjualanController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $query = $this->filteredSales($filters);
        $totalTransaksi = (clone $query)->count();
        $totalPenjualan = (clone $query)->sum('total');

        return view('kasir.penjualan.index', [
            'penjualan' => $query->with(['user', 'detail.obat'])
                ->latest('tanggal_penjualan')
                ->paginate(10)
                ->withQueryString(),
            'filters' => $filters,
            'totalTransaksi' => $totalTransaksi,
            'totalPenjualan' => $totalPenjualan,
        ]);
    }

    public function pdf(Request $request): Response
    {
        $filters = $this->validatedFilters($request);
        $penjualan = $this->filteredSales($filters)
            ->with(['user', 'detail.obat'])
            ->latest('tanggal_penjualan')
            ->get();

        return Pdf::loadView('kasir.penjualan.pdf', [
            'penjualan' => $penjualan,
            'filters' => $filters,
            'totalTransaksi' => $penjualan->count(),
            'totalPenjualan' => $penjualan->sum(fn (Penjualan $sale): float => (float) $sale->total),
        ])
            ->setPaper('a4', 'landscape')
            ->download('laporan-penjualan-'.now()->format('Ymd-His').'.pdf');
    }

    /**
     * @return array{tanggal_mulai?: string|null, tanggal_akhir?: string|null, q?: string|null}
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
    }

    /**
     * @param  array{tanggal_mulai?: string|null, tanggal_akhir?: string|null, q?: string|null}  $filters
     * @return Builder<Penjualan>
     */
    private function filteredSales(array $filters): Builder
    {
        return Penjualan::query()
            ->when($filters['tanggal_mulai'] ?? null, fn (Builder $query, string $date) => $query->whereDate('tanggal_penjualan', '>=', $date))
            ->when($filters['tanggal_akhir'] ?? null, fn (Builder $query, string $date) => $query->whereDate('tanggal_penjualan', '<=', $date))
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('no_transaksi', 'like', "%{$search}%")
                        ->orWhere('nama_pelanggan', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            });
    }
}
