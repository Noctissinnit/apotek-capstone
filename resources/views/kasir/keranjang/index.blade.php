@extends('layouts.app')

@section('title', 'Keranjang')
@section('header', 'Keranjang')
@section('subheader', 'Periksa daftar obat sebelum menyelesaikan transaksi')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ $jumlahItem }} item dipilih</p>
                <p class="text-xs text-slate-500">{{ $items->count() }} jenis obat</p>
            </div>
        </div>
        <a href="{{ route('kasir.transaksi') }}" class="btn-ghost">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
            Tambah Obat
        </a>
    </div>

    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="cart-title">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 id="cart-title" class="text-base font-semibold text-slate-900">Daftar Obat</h2>
                    <p class="mt-0.5 text-sm text-slate-500">Rincian obat pada transaksi ini</p>
                </div>
                @if ($items->isNotEmpty())
                    <span class="badge-success">{{ $items->count() }} jenis</span>
                @endif
            </div>

            @if ($items->isEmpty())
                <div class="flex min-h-72 flex-col items-center justify-center px-6 py-12 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </span>
                    <p class="mt-4 font-semibold text-slate-800">Keranjang masih kosong</p>
                    <p class="mt-1 text-sm text-slate-500">Belum ada obat pada transaksi ini.</p>
                    <a href="{{ route('kasir.transaksi') }}" class="btn-primary mt-5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                        Pilih Obat
                    </a>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <article class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-3 px-4 py-4 transition hover:bg-slate-50/70 sm:grid-cols-[minmax(0,1fr)_100px_130px_40px] sm:gap-4 sm:px-5">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-sm font-bold text-emerald-700">
                                    {{ str($item['obat']->nama_obat)->substr(0, 1)->upper() }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900">{{ $item['obat']->nama_obat }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $item['obat']->kode_obat }} <span class="px-1">·</span> Rp {{ number_format($item['harga_cents'] / 100, 0, ',', '.') }} / {{ $item['obat']->satuan }}</p>
                                </div>
                            </div>
                            <div class="col-start-2 row-start-1 text-right sm:col-auto sm:row-auto sm:text-left">
                                <p class="text-[11px] font-medium uppercase text-slate-400 sm:hidden">Jumlah</p>
                                <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1.5 text-sm font-semibold text-slate-700">{{ $item['jumlah'] }} <span class="ml-1 font-normal text-slate-500">{{ $item['obat']->satuan }}</span></span>
                            </div>
                            <div class="col-span-2 flex items-center justify-between border-t border-slate-100 pt-3 sm:col-span-1 sm:block sm:border-0 sm:pt-0 sm:text-right">
                                <p class="text-[11px] font-medium uppercase text-slate-400 sm:hidden">Subtotal</p>
                                <p class="font-semibold text-slate-900">Rp {{ number_format($item['subtotal_cents'] / 100, 0, ',', '.') }}</p>
                            </div>
                            <form method="POST" action="{{ route('kasir.keranjang.remove', $item['obat']) }}" class="col-start-2 row-start-2 justify-self-end sm:col-auto sm:row-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-200" aria-label="Hapus {{ $item['obat']->nama_obat }}" title="Hapus item">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:sticky xl:top-20" aria-labelledby="summary-title">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 id="summary-title" class="text-base font-semibold text-slate-900">Ringkasan Pembayaran</h2>
            </div>
            <div class="p-5">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Jenis obat</dt>
                            <dd class="font-medium text-slate-800">{{ $items->count() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">Jumlah item</dt>
                            <dd class="font-medium text-slate-800">{{ $jumlahItem }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-4 border-t border-dashed border-slate-200 pt-4">
                    <p class="text-sm font-medium text-slate-500">Total pembayaran</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">Rp {{ number_format($subtotal / 100, 0, ',', '.') }}</p>
                </div>

                <form method="POST" action="{{ route('kasir.checkout') }}">
                    @csrf
                    @include('kasir.partials.metode-pembayaran')

                    <button type="submit" class="btn-primary mt-5 w-full py-3" @disabled($items->isEmpty())>
                        Proses Pembayaran
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-7-7 7 7-7 7"/></svg>
                    </button>
                </form>
            </div>
        </aside>
    </div>
@endsection