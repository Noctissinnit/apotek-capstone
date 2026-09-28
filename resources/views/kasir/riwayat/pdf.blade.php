<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan {{ $tanggal->format('d-m-Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 11px; }
        h1 { margin-bottom: 4px; font-size: 20px; }
        p { margin-top: 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: left; }
        th { background: #f1f5f9; }
        .right { text-align: right; }
        .items { color: #475569; }
        tfoot th { background: #ecfdf5; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan Apotek</h1>
    <p>Tanggal: {{ $tanggal->locale('id')->translatedFormat('d F Y') }} &middot; {{ $penjualan->count() }} transaksi</p>

    <table>
        <thead>
            <tr>
                <th>No. Faktur / Waktu</th>
                <th>Kasir</th>
                <th>Rincian Obat</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($penjualan as $transaksi)
                <tr>
                    <td>{{ $transaksi->no_faktur }}<br>{{ $transaksi->tanggal_penjualan->format('H:i') }}</td>
                    <td>{{ $transaksi->user->name }}</td>
                    <td class="items">
                        @foreach ($transaksi->detail as $item)
                            {{ $item->nama_obat }} ({{ $item->jumlah }} {{ $item->satuan }} &times; Rp {{ number_format($item->harga_jual, 0, ',', '.') }}) = Rp {{ number_format($item->subtotal, 0, ',', '.') }}<br>
                        @endforeach
                    </td>
                    <td class="right">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada penjualan pada tanggal ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="right">Total penjualan</th>
                <th class="right">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
