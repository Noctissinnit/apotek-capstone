<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan {{ $transaksi->no_faktur }}</title>
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
    <p>No. transaksi: {{ $transaksi->no_faktur }}<br>
        Tanggal: {{ $transaksi->tanggal_penjualan->locale('id')->translatedFormat('d F Y, H:i') }}<br>
        Kasir: {{ $transaksi->user->name }}</p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nama obat</th>
                <th class="right">Jumlah</th>
                <th class="right">Harga satuan</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transaksi->detail as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="items">{{ $item->obat->nama_obat }}</td>
                    <td class="right">{{ $item->jumlah }} {{ $item->obat->satuan }}</td>
                    <td class="right">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Tidak ada rincian obat untuk transaksi ini.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="right">Total transaksi</th>
                <th class="right">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
