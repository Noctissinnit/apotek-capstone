<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Pasang status sidebar sebelum halaman digambar, agar tidak berkedip saat dimuat --}}
    <script>
        try {
            if (localStorage.getItem('sidebar-tutup') === '1') document.documentElement.classList.add('sidebar-tutup');
        } catch (e) {}
    </script>

    <title>@hasSection('title')@yield('title') - @endif{{ config('app.name', 'Apotek') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        @include('layouts.partials.sidebar')

        <div id="konten" class="flex min-w-0 flex-1 flex-col transition-[padding] duration-200 lg:pl-60">
            @include('layouts.partials.navbar')

            <main class="mx-auto w-full max-w-screen-2xl flex-1 px-4 py-5 sm:px-6">
                @hasSection('header')
                    <div class="mb-5 flex flex-wrap items-end justify-between gap-3 border-b border-slate-200 pb-4">
                        <div>
                            <h1 class="text-lg font-semibold tracking-tight text-slate-900">@yield('header')</h1>
                            @hasSection('subheader')
                                <p class="mt-0.5 text-sm text-slate-500">@yield('subheader')</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @yield('actions')
                        </div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 flex items-start gap-2 rounded-md border-l-2 border-emerald-600 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 flex items-start gap-2 rounded-md border-l-2 border-red-600 bg-red-50 px-3 py-2.5 text-sm text-red-900">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('transaksi_sukses'))
                    {{-- Dibaca app.js untuk menampilkan notifikasi pembayaran berhasil --}}
                    <script type="application/json" id="transaksi-sukses">@json(session('transaksi_sukses'))</script>

                    {{-- Cadangan bila JavaScript mati --}}
                    <noscript>
                        <div class="mb-4 flex items-start gap-2 rounded-md border-l-2 border-emerald-600 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900">
                            Pembayaran berhasil. Faktur {{ session('transaksi_sukses')['no_faktur'] }},
                            total {{ session('transaksi_sukses')['total'] }}
                            ({{ session('transaksi_sukses')['metode_pembayaran'] }}).
                        </div>
                    </noscript>
                @endif

                @yield('content')
            </main>

            <footer class="border-t border-slate-200 bg-white px-6 py-4 text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'Apotek') }}. Sistem Informasi Apotek.
            </footer>
        </div>
    </div>

    @include('components.modal')

    @stack('scripts')
</body>
</html>
