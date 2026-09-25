@php
// Menu sidebar. 'route' boleh belum terdaftar; link otomatis aktif setelah route dibuat.
// 'can' = permission Spatie yang dibutuhkan untuk melihat menu.
// 'active' (opsional) = pola nama route untuk menandai menu aktif, default "<prefix route>.*".
    $menus = [
    'Utama' => [
    ['label' => 'Dashboard', 'route' => auth()->user()?->dashboardRoute() ?? 'dashboard', 'active' => '*.dashboard', 'can' => 'dashboard.lihat', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ],
    'Master Data' => [
    ['label' => 'Data Obat', 'route' => 'obat.index', 'can' => 'obat.lihat', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
    ['label' => 'Data Kategori', 'route' => 'kategori.index', 'can' => 'kategori.kelola', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
    ['label' => 'Data Supplier', 'route' => 'supplier.index', 'can' => 'supplier.kelola', 'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
    ],
    'Transaksi' => [
    ['label' => 'Pembelian', 'route' => 'pembelian.index', 'can' => 'pembelian.lihat', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
    ],
    'Pengaturan' => [
    ['label' => 'Manajemen User', 'route' => 'user.index', 'can' => 'user.kelola', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
    ],
    ];
    @endphp

    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden" data-sidebar-toggle></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 transition-transform duration-200 lg:translate-x-0">
        <div class="flex h-16 items-center gap-3 border-b border-slate-800 px-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-white">{{ config('app.name', 'Apotek') }}</p>
                <p class="text-xs text-slate-400">Sistem Informasi Apotek</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 pb-6">
            @foreach ($menus as $heading => $items)
            @php
            $visible = array_filter($items, fn ($item) => auth()->user()?->can($item['can']));
            @endphp

            @if (count($visible))
            <p class="nav-heading">{{ $heading }}</p>

            @foreach ($visible as $item)
            @php
            $exists = Route::has($item['route']);
            $pattern = $item['active'] ?? str($item['route'])->beforeLast('.')->append('.*')->toString();
            $active = $exists && request()->routeIs($item['route'], $pattern);
            @endphp
            <a href="{{ $exists ? route($item['route']) : '#' }}" @class(['nav-link', 'active'=> $active])>
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
            @endforeach
            @endif
            @endforeach
        </nav>
    </aside>