<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0F172A">
    <title>@yield('title', 'Masuk') — {{ config('app.name', 'HousingHub') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
    @livewireStyles
</head>
<body class="bg-[#F8FAFC] text-[#0F172A] antialiased">
    <div class="mx-auto flex min-h-dvh w-full max-w-md flex-col px-5 pb-8 pt-10 md:max-w-lg">
        <div class="mb-8 flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#0F172A] text-white">
                <i data-lucide="home" class="h-5 w-5"></i>
            </div>
            <div>
                <p class="text-lg font-bold leading-tight">{{ config('app.name', 'HousingHub') }}</p>
                <p class="text-[13px] text-[#64748B]">Kelola perumahan dari HP</p>
            </div>
        </div>
        {{ $slot ?? '' }}
        @yield('content')
    </div>
    @livewireScripts
</body>
</html>
