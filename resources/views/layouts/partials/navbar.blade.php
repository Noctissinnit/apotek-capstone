<header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
    <div class="flex items-center gap-3">
        <button type="button" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden" data-sidebar-toggle aria-label="Buka menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <p class="hidden text-sm text-slate-500 sm:block">
            {{ now()->translatedFormat('l, d F Y') }}
        </p>
    </div>

    @auth
        <div class="flex items-center gap-3">
            <div class="text-right">
                <p class="text-sm font-medium text-slate-900">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500 capitalize">{{ auth()->user()->getRoleNames()->implode(', ') }}</p>
            </div>
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
                {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
            </div>

            @if (Route::has('logout'))
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-red-600" title="Keluar">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            @endif
        </div>
    @endauth
</header>
