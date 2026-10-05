<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $dokumen->judulDokumen() }} {{ $dokumen->no_dokumen }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 15px; margin: 0 0 2px; text-transform: uppercase; }
        .meta { margin: 14px 0; }
        .meta td { padding: 2px 0; vertical-align: top; }
        .meta td:first-child { width: 120px; color: #64748b; }
        table.isi { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.isi th, table.isi td { border: 1px solid #cbd5e1; padding: 5px 6px; }
        table.isi th { background: #f1f5f9; text-align: left; font-size: 10px; text-transform: uppercase; }
        .kanan { text-align: right; }
        .total td { font-weight: bold; background: #f8fafc; }
        .ttd { margin-top: 36px; width: 100%; }
        .ttd td { width: 50%; text-align: center; font-size: 11px; padding-top: 4px; }
        .garis-ttd { height: 56px; }
        .catatan { margin-top: 10px; font-size: 10px; color: #475569; }
        .kepala { border-bottom: 2px solid #0f172a; padding-bottom: 6px; }
    </style>
</head>
<body>
    <div class="kepala">
        <h1>{{ $dokumen->judulDokumen() }}</h1>
        <div>{{ config('app.name') }} &mdash; {{ $dokumen->apotek }}</div>
    </div>

    <table class="meta">
        <tr><td>No. Dokumen</td><td>: {{ $dokumen->no_dokumen }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ $dokumen->tanggal->translatedFormat('d F Y') }}</td></tr>
        @if ($dokumen->isRetur())
            <tr><td>Supplier Tujuan</td><td>: {{ $dokumen->supplier?->nama_supplier ?? '-' }}</td></tr>
        @endif
        <tr><td>Petugas</td><td>: {{ $dokumen->user?->name ?? '-' }}</td></tr>
        @if ($dokumen->keterangan)
            <tr><td>Keterangan</td><td>: {{ $dokumen->keterangan }}</td></tr>
        @endif
    </table>

    <p>
        {{ $dokumen->isRetur()
            ? 'Barang berikut dikembalikan kepada supplier dan telah dikeluarkan dari persediaan apotek:'
            : 'Barang berikut telah dimusnahkan secara fisik dan dikeluarkan dari persediaan apotek:' }}
    </p>

    <table class="isi">
        <thead>
            <tr>
                <th style="width:18px">No</th>
                <th>Nama Obat</th>
                <th>No. Batch</th>
                <th>Kadaluarsa</th>
                <th>Alasan</th>
                <th class="kanan">Jumlah</th>
                <th class="kanan">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dokumen->detail as $nomor => $baris)
                <tr>
                    <td>{{ $nomor + 1 }}</td>
                    <td>{{ $baris->obat?->nama_obat ?? '-' }}</td>
                    <td>{{ $baris->batch?->no_batch ?? '-' }}</td>
                    <td>{{ $baris->batch?->tanggal_kadaluarsa?->translatedFormat('d M Y') ?? '-' }}</td>
                    <td>{{ ucfirst($baris->alasan) }}</td>
                    <td class="kanan">{{ $baris->jumlah }} {{ $baris->obat?->satuan }}</td>
                    <td class="kanan">Rp {{ number_format($baris->nilai, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="6" class="kanan">Total Nilai</td>
                <td class="kanan">Rp {{ number_format($dokumen->total_nilai, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p class="catatan">
        Dokumen ini dicetak dari {{ config('app.name') }} pada {{ now()->translatedFormat('d F Y H:i') }} WIB.
    </p>

    <table class="ttd">
        <tr>
            <td>Petugas Apotek</td>
            <td>{{ $dokumen->isRetur() ? 'Penerima Supplier' : 'Saksi' }}</td>
        </tr>
        <tr>
            <td class="garis-ttd"></td>
            <td class="garis-ttd"></td>
        </tr>
        <tr>
            <td>( {{ $dokumen->user?->name ?? '....................' }} )</td>
            <td>( .................................... )</td>
        </tr>
    </table>
</body>
</html>
