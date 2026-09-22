<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') - @endif{{ config('app.name', 'Apotek') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        @include('layouts.partials.sidebar')

        <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
            @include('layouts.partials.navbar')

            <main class="flex-1 p-4 sm:p-6">
                @hasSection('header')
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-semibold text-slate-900">@yield('header')</h1>
                            @hasSection('subheader')
                                <p class="mt-1 text-sm text-slate-500">@yield('subheader')</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @yield('actions')
                        </div>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="border-t border-slate-200 bg-white px-6 py-4 text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'Apotek') }}. Sistem Informasi Apotek.
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
