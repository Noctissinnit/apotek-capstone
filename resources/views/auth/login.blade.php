<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login - {{ config('app.name', 'Apotek') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 font-sans text-slate-800 antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 flex flex-col items-center text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600 text-white">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
            </div>
            <h1 class="mt-4 text-xl font-semibold text-slate-900">{{ config('app.name', 'Apotek') }}</h1>
            <p class="mt-1 text-sm text-slate-500">Masuk untuk melanjutkan ke sistem</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        @class([
                            'w-full rounded-lg border px-3 py-2 text-sm outline-none focus:ring-2',
                            'border-red-400 focus:ring-red-200' => $errors->has('email'),
                            'border-slate-300 focus:border-emerald-500 focus:ring-emerald-200' => ! $errors->has('email'),
                        ])>
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    Ingat saya
                </label>

                <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-300 focus:outline-none">
                    Masuk
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">&copy; {{ date('Y') }} {{ config('app.name', 'Apotek') }}</p>
    </div>
</body>
</html>
