<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan {{ $tanggalMulai->format('d-m-Y') }} sampai {{ $tanggalSelesai->format('d-m-Y') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 10px; }
        h1 { margin-bottom: 4px; font-size: 20px; }
        h2 { margin: 22px 0 5px; font-size: 13px; }
        p { margin-top: 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        th { background: #f1f5f9; }
        .right { text-align: right; }
        .summary { margin-top: 20px; padding: 10px; background: #ecfdf5; font-size: 13px; font-weight: bold; text-align: right; }
        .transaction { page-break-inside: avoid; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan Apotek</h1>
    <p>Periode: {{ $tanggalMulai->locale('id')->translatedFormat('d F Y') }} sampai {{ $tanggalSelesai->locale('id')->translatedFormat('d F Y') }}<br>
        Jumlah transaksi: {{ $penjualan->count() }}</p>

    @forelse ($penjualan as $transaksi)
        <section class="transaction">
            <h2>{{ $transaksi->no_transaksi }}</h2>
            <p>{{ $transaksi->tanggal_penjualan->locale('id')->translatedFormat('d F Y, H:i') }} &middot; Kasir: {{ $transaksi->user->name }}</p>
            <table>
                <thead>
                    <tr>
                        <th>Nama obat</th>
                        <th class="right">Jumlah</th>
                        <th class="right">Harga satuan</th>
                        <th class="right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaksi->detail as $item)
                        <tr>
                            <td>{{ $item->obat->nama_obat }}</td>
                            <td class="right">{{ $item->jumlah }} {{ $item->obat->satuan }}</td>
                            <td class="right">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                            <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada rincian obat.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="right">Total transaksi</th>
                        <th class="right">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </section>
    @empty
        <p>Tidak ada transaksi pada rentang tanggal ini.</p>
    @endforelse

    <div class="summary">Total seluruh transaksi: Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</div>
</body>
</html>
