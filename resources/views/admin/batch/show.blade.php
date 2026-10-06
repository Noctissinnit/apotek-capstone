@extends('layouts.app')

@section('title', 'Telusuri Batch '.$batch->no_batch)
@section('header', 'Batch '.$batch->no_batch)
@section('subheader', $batch->obat?->nama_obat.' · '.$batch->obat?->kode_obat)

@section('actions')
    <a href="{{ route('batch.index') }}" class="btn-ghost">Kembali</a>
@endsection

@section('content')
    @php
        $tingkat = $batch->tingkatKadaluarsa();
        $cocok = $stokAwal + $totalMasuk - $totalKeluar === $batch->stok;
    @endphp

    <div class="grid gap-3 sm:grid-cols-4">
        <div class="stat-card">
            <p class="stat-label">Sisa Stok</p>
            <p class="stat-value">{{ number_format($batch->stok) }}</p>
            <p class="mt-0.5 text-xs text-slate-500">{{ $batch->obat?->satuan }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Kadaluarsa</p>
            <p class="mt-1 text-base font-semibold text-slate-900">{{ $batch->tanggal_kadaluarsa?->translatedFormat('d M Y') ?? '—' }}</p>
            @if ($batch->tanggal_kadaluarsa)
                <p class="mt-0.5 text-xs {{ $tingkat === 'kadaluarsa' ? 'text-red-600' : 'text-slate-500' }}">
                    {{ $batch->sisaHari() < 0 ? 'lewat '.abs($batch->sisaHari()).' hari' : 'sisa '.$batch->sisaHari().' hari' }}
                </p>
            @endif
        </div>
        <div class="stat-card">
            <p class="stat-label">Modal per Satuan</p>
            <p class="mt-1 text-base font-semibold text-slate-900">
                {{ $batch->modal_per_satuan ? 'Rp '.number_format($batch->modal_per_satuan, 0, ',', '.') : '—' }}
            </p>
            <p class="mt-0.5 text-xs text-slate-500">nilai sisa Rp {{ number_format($batch->stok * (float) $batch->modal_per_satuan, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Status</p>
            <p class="mt-2">
                @if ($batch->status === App\Models\BatchObat::STATUS_DITAHAN)
                    <span class="badge-warning">Ditahan</span>
                @elseif ($tingkat === 'kadaluarsa')
                    <span class="badge-danger">Kadaluarsa</span>
                @elseif ($batch->stok < 1)
                    <span class="badge-neutral">Habis</span>
                @else
                    <span class="badge-success">Siap dijual</span>
                @endif
            </p>
            @if ($batch->catatan_status)
                <p class="mt-1 text-xs text-slate-500">{{ $batch->catatan_status }}</p>
            @endif
        </div>
    </div>

    <div class="mt-5 panel-card">
        <div class="panel-header">
            <h2 class="panel-title">Riwayat Pergerakan</h2>
            <span class="badge-neutral">masuk {{ number_format($totalMasuk) }} · keluar {{ number_format($totalKeluar) }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Riwayat keluar masuk batch {{ $batch->no_batch }}</caption>
                <thead class="table-head">
                    <tr>
                        <th scope="col" class="table-th">Tanggal</th>
                        <th scope="col" class="table-th">Kegiatan</th>
                        <th scope="col" class="table-th">Dokumen</th>
                        <th scope="col" class="table-th text-right">Perubahan</th>
                        <th scope="col" class="table-th text-right">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $berjalan = $stokAwal; @endphp

                    @if ($stokAwal !== 0)
                        <tr class="bg-slate-50/60">
                            <td class="table-td text-slate-500">—</td>
                            <td class="table-td">Stok awal, sebelum pencatatan batch dimulai</td>
                            <td class="table-td text-slate-500">—</td>
                            <td class="table-td text-right font-medium text-slate-700">{{ number_format($stokAwal) }}</td>
                            <td class="table-td text-right">{{ number_format($berjalan) }}</td>
                        </tr>
                    @endif

                    @forelse ($riwayat as $baris)
                        @php $berjalan += $baris['jumlah']; @endphp
                        <tr>
                            <td class="table-td whitespace-nowrap">{{ $baris['tanggal']?->translatedFormat('d M Y') ?? '—' }}</td>
                            <td class="table-td">
                                <span class="{{ match ($baris['jenis']) {
                                    'masuk' => 'badge-success',
                                    'jual' => 'badge-neutral',
                                    'retur' => 'badge-warning',
                                    default => 'badge-danger',
                                } }}">{{ ucfirst($baris['jenis']) }}</span>
                                <span class="ml-2 text-slate-600">{{ $baris['keterangan'] }}</span>
                            </td>
                            <td class="table-td">{{ $baris['dokumen'] ?? '—' }}</td>
                            <td class="table-td text-right font-medium {{ $baris['jumlah'] > 0 ? 'text-emerald-700' : 'text-slate-700' }}">
                                {{ $baris['jumlah'] > 0 ? '+' : '' }}{{ number_format($baris['jumlah']) }}
                            </td>
                            <td class="table-td text-right">{{ number_format($berjalan) }}</td>
                        </tr>
                    @empty
                        @if ($stokAwal === 0)
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <p>Belum ada pergerakan tercatat untuk batch ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50">
                        <td colspan="4" class="px-4 py-2.5 text-right font-medium text-slate-700">Sisa menurut riwayat</td>
                        <td class="px-4 py-2.5 text-right font-semibold {{ $cocok ? 'text-slate-900' : 'text-red-600' }}">
                            {{ number_format($berjalan) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @unless ($cocok)
            <p class="border-t border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-800">
                Sisa menurut riwayat berbeda dengan stok tercatat ({{ number_format($batch->stok) }}).
                Periksa kembali data batch ini.
            </p>
        @endunless
    </div>
@endsection
