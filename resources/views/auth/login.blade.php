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
{{--
    Halaman masuk dominan putih dengan aksen hijau: kartu terbelah dua, ilustrasi di kiri dan
    form di kanan. Di layar kecil ilustrasinya disembunyikan, hanya form yang tampil.
--}}
<body class="login-halaman flex min-h-screen items-center justify-center px-4 py-8 font-sans antialiased">
    <div class="login-kartu grid w-full max-w-4xl overflow-hidden rounded-2xl p-2.5 lg:grid-cols-2">

        {{-- Panel ilustrasi --}}
        <div class="login-ilustrasi relative hidden min-h-[34rem] overflow-hidden rounded-xl lg:block" aria-hidden="true">
            <svg class="absolute inset-0 h-full w-full" viewBox="0 0 400 560" preserveAspectRatio="xMidYMid slice">
                <defs>
                    <linearGradient id="langit" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#065f46"/>
                        <stop offset="0.55" stop-color="#10b981"/>
                        <stop offset="1" stop-color="#a7f3d0"/>
                    </linearGradient>
                    <linearGradient id="bukit-jauh" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#34d399"/>
                        <stop offset="1" stop-color="#059669"/>
                    </linearGradient>
                    <linearGradient id="bukit-dekat" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" stop-color="#047857"/>
                        <stop offset="0.6" stop-color="#059669"/>
                        <stop offset="1" stop-color="#064e3b"/>
                    </linearGradient>
                </defs>
                <rect width="400" height="560" fill="url(#langit)"/>
                <path d="M0 330 C 70 300, 120 250, 190 235 C 250 222, 300 270, 400 300 L400 560 L0 560Z" fill="url(#bukit-jauh)"/>
                <path d="M0 400 C 60 370, 130 300, 215 285 C 260 300, 300 360, 400 380 L400 560 L0 560Z" fill="url(#bukit-dekat)"/>
                <path d="M215 285 C 240 330, 250 400, 230 560 L400 560 L400 380 C 300 360, 260 300, 215 285Z" fill="#064e3b" opacity="0.35"/>
                <rect y="420" width="400" height="140" fill="#022c22" opacity="0.25"/>
            </svg>

            <div class="absolute inset-x-0 top-0 flex items-center justify-between p-6">
                <span class="text-xl font-semibold tracking-[0.2em] text-white">{{ str(config('app.name', 'Apotek'))->upper() }}</span>
                <span class="rounded-full bg-white/15 px-3 py-1 text-xs text-white/90 backdrop-blur">Sistem Informasi Apotek</span>
            </div>

            <div class="absolute inset-x-0 bottom-0 p-8 text-center" data-slide-login>
                <div class="grid">
                    @foreach ([
                        ['Stok Tercatat,', 'Pelayanan Tenang'],
                        ['Setiap Batch', 'Terlacak Jelas'],
                        ['Kadaluarsa Terpantau,', 'Pasien Terlindungi'],
                    ] as $i => [$baris1, $baris2])
                        <p @class(['login-slide col-start-1 row-start-1 text-2xl leading-snug font-medium text-white', 'aktif' => $i === 0]) data-slide>
                            {{ $baris1 }}<br>{{ $baris2 }}
                        </p>
                    @endforeach
                </div>
                <div class="mt-6 flex justify-center gap-2">
                    @foreach (range(0, 2) as $i)
                        <span @class(['login-titik', 'aktif' => $i === 0]) data-titik></span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Form masuk --}}
        <div class="flex flex-col justify-center px-6 py-10 sm:px-10">
            <h1 class="text-3xl font-medium tracking-tight text-slate-900">Masuk ke akun</h1>
            <p class="mt-3 text-sm text-slate-500">Belum punya akun? Minta dibuatkan oleh admin apotek.</p>

            @if (session('success'))
                <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-4">
                @csrf

                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        placeholder="Email"
                        @class(['login-input', 'login-input-salah' => $errors->has('email')])
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')
                        <p id="email-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="sr-only">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                            placeholder="Masukkan password"
                            @class(['login-input pr-11', 'login-input-salah' => $errors->has('password')])
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                        <button type="button" data-toggle-password="password"
                            class="absolute top-1/2 right-2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                            aria-label="Tampilkan password" aria-pressed="false">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                    @error('password')
                        <p id="password-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2.5 pt-1 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded accent-emerald-700" @checked(old('remember'))>
                    Ingat saya di perangkat ini
                </label>

                <button type="submit" class="login-tombol mt-4 w-full">Masuk</button>
            </form>

            <p class="mt-10 text-center text-xs text-slate-400">&copy; {{ date('Y') }} {{ config('app.name', 'Apotek') }}</p>
        </div>
    </div>
</body>
</html>
