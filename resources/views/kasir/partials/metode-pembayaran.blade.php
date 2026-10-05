@php
    // Ikon tiap cara bayar, dipakai di layar transaksi dan halaman keranjang.
    $ikon = [
        'Tunai' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m3 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H10a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        'QRIS' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 4h2m-2-4h6v2m0 2v4h-4',
        'Transfer/Debit' => 'M3 10h18M7 15h2m4 0h4M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z',
    ];

    $terpilih = old('metode_pembayaran', 'Tunai');
@endphp

<fieldset class="mt-5">
    <legend class="mb-2 text-sm font-medium text-slate-700">Cara pembayaran</legend>

    <div class="grid grid-cols-3 gap-2">
        @foreach (App\Http\Controllers\KasirTransaksiController::METODE_PEMBAYARAN as $metode)
            <label class="flex cursor-pointer flex-col items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-2 py-3 text-center transition hover:border-emerald-400 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/70 has-[:checked]:text-emerald-800 has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-emerald-500/20">
                <input type="radio" name="metode_pembayaran" value="{{ $metode }}" class="sr-only" @checked($terpilih === $metode) required>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon[$metode] }}"/></svg>
                <span class="text-xs font-semibold">{{ $metode }}</span>
            </label>
        @endforeach
    </div>

    @error('metode_pembayaran')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</fieldset>

{{-- Kode QR berisi nominal, ditunjukkan ke pembeli untuk dipindai --}}
@if (($qris ?? null) !== null)
    <div class="mt-4 hidden" data-bagian-qris>
        @if ($qris['status'] === 'siap')
            <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
                <p class="text-sm font-medium text-slate-700">Minta pembeli memindai kode ini</p>
                <div class="mx-auto mt-3 w-48 [&>svg]:h-auto [&>svg]:w-full">{!! $qris['svg'] !!}</div>
                <p class="mt-3 text-lg font-bold text-slate-900">Rp {{ number_format($qris['nominal'], 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    Nominal sudah terisi otomatis. Pastikan notifikasi pembayaran masuk sebelum menekan Proses Pembayaran.
                </p>
            </div>
        @elseif ($qris['status'] === 'belum_diatur')
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Kode QRIS apotek belum diatur, jadi kode QR tidak bisa ditampilkan.
                Pembayaran QRIS tetap bisa dicatat memakai stiker QRIS di meja.
            </div>
        @else
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                Kode QRIS apotek tidak terbaca. Periksa kembali teks QRIS yang disalin ke pengaturan.
            </div>
        @endif
    </div>
@endif

{{-- Kebalikan dari kolom tunai: hanya untuk pembayaran non-tunai --}}
<div class="mt-4" data-bagian-referensi>
    <label for="no_referensi" class="mb-1.5 block text-sm font-medium text-slate-700">
        No. Referensi <span class="font-normal text-slate-400">(boleh dikosongkan)</span>
    </label>
    <input
        id="no_referensi"
        name="no_referensi"
        value="{{ old('no_referensi') }}"
        maxlength="50"
        class="soft-input"
        placeholder="Dari notifikasi HP atau struk EDC"
        aria-describedby="referensi-bantuan"
    >
    <p id="referensi-bantuan" class="mt-1.5 text-xs text-slate-500">
        Untuk mencocokkan penjualan dengan mutasi rekening nanti.
    </p>
    @error('no_referensi')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Hanya muncul untuk tunai; QRIS dan Transfer/Debit jumlahnya selalu pas --}}
<div class="mt-4" data-bagian-tunai data-total="{{ $total }}">
    <label for="uang_diterima" class="mb-1.5 block text-sm font-medium text-slate-700">Uang diterima</label>

    <div class="flex flex-wrap gap-1.5">
        <button type="button" class="btn-ghost px-2.5 py-1 text-xs" data-uang-cepat="{{ $total }}">Uang pas</button>
        @foreach ([20000, 50000, 100000] as $nominal)
            @if ($nominal > $total)
                <button type="button" class="btn-ghost px-2.5 py-1 text-xs" data-uang-cepat="{{ $nominal }}">
                    {{ number_format($nominal / 1000) }}rb
                </button>
            @endif
        @endforeach
    </div>

    <input
        id="uang_diterima"
        type="number"
        name="uang_diterima"
        value="{{ old('uang_diterima') }}"
        min="0"
        step="100"
        inputmode="numeric"
        placeholder="0"
        class="soft-input mt-2 text-right text-lg font-semibold"
        data-uang-diterima
    >

    <div class="mt-2 flex items-baseline justify-between rounded-xl bg-slate-50 px-3 py-2">
        <span class="text-sm text-slate-500">Kembalian</span>
        <span class="text-lg font-bold text-slate-900" data-kembalian>Rp 0</span>
    </div>

    @error('uang_diterima')
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
