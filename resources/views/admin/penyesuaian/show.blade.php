@extends('layouts.app')

@section('title', 'Detail Dokumen')
@section('header', $dokumen->no_dokumen)
@section('subheader', $dokumen->judulDokumen())

@section('actions')
    <a href="{{ route('penyesuaian.pdf', $dokumen) }}" class="btn-primary">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        Unduh PDF
    </a>
    <a href="{{ route('penyesuaian.index') }}" class="btn-ghost">Kembali</a>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-4">
        @foreach ([
            ['Jenis', $dokumen->isRetur() ? 'Retur ke Supplier' : 'Pemusnahan'],
            ['Tanggal', $dokumen->tanggal->translatedFormat('d F Y')],
            [$dokumen->isRetur() ? 'Supplier' : 'Petugas', $dokumen->isRetur() ? ($dokumen->supplier?->nama_supplier ?? '-') : ($dokumen->user?->name ?? '-')],
            ['Total Nilai', 'Rp '.number_format($dokumen->total_nilai, 0, ',', '.')],
        ] as [$label, $nilai])
            <div class="stat-card">
                <p class="stat-label">{{ $label }}</p>
                <p class="mt-2 font-semibold text-slate-900">{{ $nilai }}</p>
            </div>
        @endforeach
    </div>

    @if ($dokumen->keterangan)
        <p class="mt-4 rounded-md border border-slate-200 bg-white px-5 py-3 text-sm text-slate-600">{{ $dokumen->keterangan }}</p>
    @endif

    <div class="mt-6 panel-card">
        <div class="panel-header">
            <h2 class="panel-title">Barang yang Dikeluarkan</h2>
            <span class="badge-neutral">{{ $dokumen->detail->count() }} batch</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Rincian barang pada dokumen {{ $dokumen->no_dokumen }}</caption>
                <thead class="table-head">
                    <tr>
                        <th scope="col" class="table-th">Obat</th>
                        <th scope="col" class="table-th">Batch</th>
                        <th scope="col" class="table-th">Kadaluarsa</th>
                        <th scope="col" class="table-th">Alasan</th>
                        <th scope="col" class="table-th text-right">Jumlah</th>
                        <th scope="col" class="table-th text-right">Modal/Satuan</th>
                        <th scope="col" class="table-th text-right">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($dokumen->detail as $baris)
                        <tr>
                            <th scope="row" class="table-td text-left font-medium text-slate-900">{{ $baris->obat?->nama_obat ?? '-' }}</th>
                            <td class="table-td">{{ $baris->batch?->no_batch ?? '-' }}</td>
                            <td class="table-td">{{ $baris->batch?->tanggal_kadaluarsa?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="table-td capitalize">{{ $baris->alasan }}</td>
                            <td class="table-td text-right">{{ $baris->jumlah }} {{ $baris->obat?->satuan }}</td>
                            <td class="table-td text-right">Rp {{ number_format($baris->modal_per_satuan, 0, ',', '.') }}</td>
                            <td class="table-td text-right font-semibold text-slate-900">Rp {{ number_format($baris->nilai, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50">
                        <td colspan="6" class="px-5 py-3 text-right font-medium text-slate-700">Total Nilai</td>
                        <td class="px-5 py-3 text-right text-base font-bold text-slate-900">Rp {{ number_format($dokumen->total_nilai, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
