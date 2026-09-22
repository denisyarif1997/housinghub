@props(['title' => config('app.name', 'HousingHub')])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title }}</title>
    <meta name="color-scheme" content="light dark">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script>
        // Terapkan tema sebelum render untuk mencegah flash (FOUC).
        (function () {
            var t = localStorage.getItem('theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <style>
        /* fix untuk notch iphone */
       .pt-safe { padding-top: env(safe-area-inset-top); }
       .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-[#0F172A] antialiased">
<div class="mx-auto flex min-h-dvh w-full max-w-md flex-col">
    <header class="sticky top-0 z-30 border-b border-[#E2E8F0] bg-white/80 pt-safe backdrop-blur">
        <div class="flex h-16 items-center justify-between px-4">
            <a href="{{ route('resident.dashboard') }}" class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#0F172A] text-white">
                    <i data-lucide="home" class="h-5 w-5"></i>
                </div>
                <p class="font-bold tracking-tight">{{ config('app.name', 'HousingHub') }}</p>
            </a>
            <div class="flex items-center gap-2">
                <button type="button" data-theme-toggle
                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#E2E8F0] text-[#0F172A] transition hover:bg-slate-100 active:scale-95"
                    aria-label="Ganti tema">
                    <i data-lucide="moon" class="h-5 w-5 hidden dark:block"></i>
                    <i data-lucide="sun" class="h-5 w-5 block dark:hidden"></i>
                </button>
                <button type="button" disabled title="Notifikasi belum tersedia"
                    class="flex h-11 w-11 items-center justify-center rounded-xl border border-[#E2E8F0] text-[#94A3B8] opacity-50">
                    <i data-lucide="bell" class="h-5 w-5"></i>
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar"
                        class="flex h-11 w-11 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-700 transition hover:bg-red-100 active:scale-95">
                        <i data-lucide="log-out" class="h-5 w-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="flex-1 px-4 pb-28 pt-4">{{ $slot }}</main>

    @php
        $navItems = [
            ['resident.dashboard', 'home', 'Home', 'resident.dashboard'],
            ['resident.ipl.index', 'wallet', 'Tagihan', 'resident.ipl.*'],
            ['resident.chess.index', 'crown', 'Catur', 'resident.chess.*'],
            ['resident.forum.index', 'messages-square', 'Forum', 'resident.forum.*'],
            ['resident.complaints.index', 'wrench', 'Aduan', 'resident.complaints.*'],
            ['resident.info.index', 'megaphone', 'Info', 'resident.info.*'],
            ['resident.profile', 'user', 'Profil', 'resident.profile'],
        ];
    @endphp

    <nav class="fixed inset-x-0 bottom-0 z-30 pb-safe">
        <div class="mx-auto max-w-md px-3 pb-3">
            <div class="no-scrollbar flex gap-1 overflow-x-auto rounded-[20px] border border-[#E2E8F0] bg-white/90 px-1.5 shadow-[0_10px_30px_rgba(15,23,42,0.12)] backdrop-blur-xl">
                @foreach ($navItems as [$route, $icon, $label, $pattern])
                    @php $active = request()->routeIs($pattern); @endphp
                    <a href="{{ route($route) }}"
                        class="flex min-h-[64px] w-[62px] shrink-0 flex-col items-center justify-center gap-1 text-[10px] tracking-wide {{ $active? 'font-semibold text-[#0F172A]' : 'font-medium text-[#64748B] hover:text-[#0F172A]' }}">
                        <span class="flex h-8 w-10 items-center justify-center rounded-lg transition {{ $active? 'bg-[#0F172A] text-white shadow-sm' : '' }}">
                            <i data-lucide="{{ $icon }}" class="h-[20px] w-[20px]"></i>
                        </span>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </nav>
</div>

@livewireScripts
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    // buat livewire navigate
    document.addEventListener('livewire:navigated', () => lucide.createIcons());
</script>
</body>
</html>