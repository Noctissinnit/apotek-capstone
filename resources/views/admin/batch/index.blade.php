@extends('layouts.app')

@section('title', 'Lacak Batch')
@section('header', 'Lacak Batch')
@section('subheader', 'Telusuri asal dan pergerakan tiap batch obat')

@section('content')
    <form method="GET" action="{{ route('batch.index') }}" class="mb-4 flex flex-wrap items-end gap-3">
        <div class="min-w-64 flex-1">
            <label for="cari" class="form-label">Cari</label>
            <input id="cari" name="cari" value="{{ $cari }}" class="form-input" placeholder="Nomor batch, nama obat, atau kode obat">
        </div>

        <div>
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                @foreach ([
                    '' => 'Semua',
                    'siap' => 'Siap dijual',
                    'kadaluarsa' => 'Kadaluarsa',
                    'ditahan' => 'Ditahan',
                    'habis' => 'Stok habis',
                ] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected($status === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn-primary">Terapkan</button>

        @if ($cari !== '' || $status !== '')
            <a href="{{ route('batch.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Reset</a>
        @endif
    </form>

    <div class="panel-card">
        <div class="panel-header">
            <h2 class="panel-title">Daftar Batch</h2>
            <span class="badge-neutral">{{ $batch->total() }} batch</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Daftar batch obat beserta stok dan masa berlakunya</caption>
                <thead class="table-head">
                    <tr>
                        <th scope="col" class="table-th">No Batch</th>
                        <th scope="col" class="table-th">Obat</th>
                        <th scope="col" class="table-th">Kadaluarsa</th>
                        <th scope="col" class="table-th">Status</th>
                        <th scope="col" class="table-th text-right">Sisa Stok</th>
                        <th scope="col" class="table-th text-right">Modal/Satuan</th>
                        <th scope="col" class="table-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($batch as $item)
                        @php $tingkat = $item->tingkatKadaluarsa(); @endphp
                        <tr class="transition hover:bg-slate-50">
                            <th scope="row" class="table-td text-left font-medium text-slate-900">{{ $item->no_batch }}</th>
                            <td class="table-td">
                                <span class="text-slate-900">{{ $item->obat?->nama_obat }}</span>
                                <span class="block text-xs text-slate-500">{{ $item->obat?->kode_obat }}</span>
                            </td>
                            <td class="table-td">
                                {{ $item->tanggal_kadaluarsa?->translatedFormat('d M Y') ?? '—' }}
                                @if ($item->tanggal_kadaluarsa)
                                    <span class="block text-xs text-slate-500">
                                        {{ $item->sisaHari() < 0 ? 'lewat '.abs($item->sisaHari()).' hari' : 'sisa '.$item->sisaHari().' hari' }}
                                    </span>
                                @endif
                            </td>
                            <td class="table-td">
                                @if ($item->status === App\Models\BatchObat::STATUS_DITAHAN)
                                    <span class="badge-warning">Ditahan</span>
                                @elseif ($item->stok < 1)
                                    <span class="badge-neutral">Habis</span>
                                @elseif ($tingkat === 'kadaluarsa')
                                    <span class="badge-danger">Kadaluarsa</span>
                                @elseif ($tingkat === 'segera_habis')
                                    <span class="badge-warning">Segera habis masa</span>
                                @else
                                    <span class="badge-success">Siap dijual</span>
                                @endif
                            </td>
                            <td class="table-td text-right">{{ number_format($item->stok) }} {{ $item->obat?->satuan }}</td>
                            <td class="table-td text-right">
                                {{ $item->modal_per_satuan ? 'Rp '.number_format($item->modal_per_satuan, 0, ',', '.') : '—' }}
                            </td>
                            <td class="table-td text-right">
                                <a href="{{ route('batch.show', $item) }}" class="font-medium text-slate-600 hover:text-slate-900">
                                    Telusuri<span class="sr-only"> batch {{ $item->no_batch }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <p>{{ $cari !== '' ? 'Tidak ada batch yang cocok dengan pencarian.' : 'Belum ada batch tercatat.' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($batch->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">{{ $batch->links() }}</div>
        @endif
    </div>
@endsection
