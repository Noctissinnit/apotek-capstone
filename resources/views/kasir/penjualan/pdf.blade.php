<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 10px; }
        h1 { margin: 0 0 5px; font-size: 20px; }
        p { margin: 3px 0; color: #475569; }
        .summary { margin: 18px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; }
        .right { text-align: right; white-space: nowrap; }
        .items div { margin-bottom: 2px; }
        tfoot td { font-weight: bold; background: #f8fafc; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan Apotek</h1>
    <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    <p>Periode:
        {{ !empty($filters['tanggal_mulai']) ? \Carbon\Carbon::parse($filters['tanggal_mulai'])->format('d/m/Y') : 'Semua tanggal' }}
        @if (!empty($filters['tanggal_akhir']))
            s.d. {{ \Carbon\Carbon::parse($filters['tanggal_akhir'])->format('d/m/Y') }}
        @endif
    </p>
    @if (!empty($filters['q']))
        <p>Kata kunci: {{ $filters['q'] }}</p>
    @endif

    <div class="summary">
        <strong>Jumlah transaksi:</strong> {{ number_format($totalTransaksi) }}
        &nbsp;&nbsp;&nbsp;
        <strong>Total penjualan:</strong> Rp {{ number_format((float) $totalPenjualan, 0, ',', '.') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Rincian item</th>
                <th>Kasir</th>
                <th>Pembayaran</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($penjualan as $transaksi)
                <tr>
                    <td>{{ $transaksi->no_transaksi }}</td>
                    <td>{{ $transaksi->tanggal_penjualan->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaksi->nama_pelanggan ?: 'Pelanggan Umum' }}</td>
                    <td class="items">
                        @foreach ($transaksi->detail as $item)
                            <div>{{ $item->obat->nama_obat }} × {{ $item->jumlah }}</div>
                        @endforeach
                    </td>
                    <td>{{ $transaksi->user->name }}</td>
                    <td>{{ $transaksi->metode_pembayaran }}</td>
                    <td class="right">Rp {{ number_format((float) $transaksi->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align: center;">Tidak ada transaksi pada periode ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="right">Total Penjualan</td>
                <td class="right">Rp {{ number_format((float) $totalPenjualan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
