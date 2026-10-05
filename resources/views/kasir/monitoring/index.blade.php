@extends('layouts.app')

@section('title', 'Monitoring Stok')
@section('header', 'Monitoring Stok')
@section('subheader', 'Stok menipis dan batch yang mendekati kadaluarsa')

@section('content')
    @php
        // Tiga tingkat peringatan, masing-masing dengan tindakan yang berbeda.
        $kelompok = [
            [
                'judul' => 'Kadaluarsa',
                'tindakan' => 'Tarik dari rak sekarang. Tidak bisa dijual lagi.',
                'badge' => 'badge-danger',
                'garis' => 'border-red-200',
                'isi' => $batchKadaluarsa,
            ],
            [
                'judul' => 'Segera Habis Masa',
                'tindakan' => 'Kurang dari '.$ambang['segera_habis'].' hari. Jual cepat atau siapkan retur.',
                'badge' => 'badge-warning',
                'garis' => 'border-amber-200',
                'isi' => $batchSegeraHabis,
            ],
            [
                'judul' => 'Siapkan Retur',
                'tindakan' => 'Kurang dari '.$ambang['siapkan_retur'].' hari. Masih aman dijual, mulai urus retur.',
                'badge' => 'badge-neutral',
                'garis' => 'border-slate-200',
                'isi' => $batchSiapkanRetur,
            ],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-4">
        <div class="stat-card">
            <p class="stat-label">Jenis Obat</p>
            <p class="stat-value text-slate-900">{{ number_format($totalObat) }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Stok Menipis</p>
            <p class="stat-value {{ $obatMenipis->count() ? 'text-amber-600' : 'text-slate-900' }}">{{ $obatMenipis->count() }}</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Kadaluarsa</p>
            <p class="stat-value {{ $batchKadaluarsa->count() ? 'text-red-600' : 'text-slate-900' }}">{{ $batchKadaluarsa->count() }}</p>
            <p class="mt-1 text-xs text-slate-500">batch, menunggu ditindaklanjuti</p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Segera Habis Masa</p>
            <p class="stat-value {{ $batchSegeraHabis->count() ? 'text-amber-600' : 'text-slate-900' }}">{{ $batchSegeraHabis->count() }}</p>
            <p class="mt-1 text-xs text-slate-500">batch, &le; {{ $ambang['segera_habis'] }} hari lagi</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="space-y-6">
            @foreach ($kelompok as $bagian)
                @continue($bagian['isi']->isEmpty())

                <div class="panel-card border {{ $bagian['garis'] }}">
                    <div class="panel-header">
                        <div>
                            <h2 class="panel-title">{{ $bagian['judul'] }}</h2>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $bagian['tindakan'] }}</p>
                        </div>
                        <span class="{{ $bagian['badge'] }}">{{ $bagian['isi']->count() }} batch</span>
                    </div>

                    <ul class="divide-y divide-slate-100" role="list">
                        @foreach ($bagian['isi'] as $batch)
                            <li class="flex items-start justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-slate-900">{{ $batch->obat?->nama_obat }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Batch {{ $batch->no_batch }} &middot; {{ $batch->stok }} {{ $batch->obat?->satuan }}
                                        &middot; {{ $batch->tanggal_kadaluarsa->translatedFormat('d M Y') }}
                                    </p>
                                </div>
                                <span class="{{ $bagian['badge'] }} shrink-0">
                                    {{ $batch->sisaHari() < 0 ? 'Lewat '.abs($batch->sisaHari()).' hari' : 'Sisa '.$batch->sisaHari().' hari' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            @if ($batchKadaluarsa->isEmpty() && $batchSegeraHabis->isEmpty() && $batchSiapkanRetur->isEmpty())
                <div class="panel-card">
                    <div class="empty-state">
                        <svg class="h-8 w-8 text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p>Tidak ada batch yang mendekati kadaluarsa.</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <h2 class="panel-title">Stok Menipis</h2>
                <span class="badge-neutral">{{ $obatMenipis->count() }} obat</span>
            </div>

            <ul class="divide-y divide-slate-100" role="list">
                @forelse ($obatMenipis as $obat)
                    @php
                        $persen = $obat->stok_minimum > 0
                            ? min(100, (int) round($obat->stok / $obat->stok_minimum * 100))
                            : ($obat->stok > 0 ? 100 : 0);
                        $habis = $obat->stok < 1;
                    @endphp
                    <li class="px-5 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-900">{{ $obat->nama_obat }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $obat->kode_obat }} &middot; siap dijual {{ $obat->stokSiapDijual() }} {{ $obat->satuan }}</p>
                            </div>
                            <span class="{{ $habis ? 'badge-danger' : 'badge-warning' }} shrink-0">
                                {{ $obat->stok }} / {{ $obat->stok_minimum }} {{ $obat->satuan }}
                            </span>
                        </div>

                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"
                            role="progressbar"
                            aria-valuenow="{{ $obat->stok }}"
                            aria-valuemin="0"
                            aria-valuemax="{{ max($obat->stok_minimum, 1) }}"
                            aria-label="Stok {{ $obat->nama_obat }} terhadap batas minimum">
                            <div class="h-full rounded-full {{ $habis ? 'bg-red-500' : 'bg-amber-500' }}" style="width: {{ $persen }}%"></div>
                        </div>
                    </li>
                @empty
                    <li class="empty-state">
                        <svg class="h-8 w-8 text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p>Semua stok aman.</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
