@php
    // Ikon tiap cara bayar, dipakai di layar transaksi dan halaman keranjang.
    $ikon = [
        'Tunai' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m3 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H10a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
        'QRIS' => 'M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 4h2m-2-4h6v2m0 2v4h-4',
        'Kartu Debit' => 'M3 10h18M7 15h2m4 0h4M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z',
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
