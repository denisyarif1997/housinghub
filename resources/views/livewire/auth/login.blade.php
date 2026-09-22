<div>
    <h1 class="text-2xl font-bold">Masuk ke {{ config('app.name', 'Perumku') }}</h1>
    <p class="mt-1 text-[14px] text-[#64748B]">Kelola rumah, IPL, dan layanan warga.</p>

    <form wire:submit="login" class="mt-6 space-y-4">
        <x-ui.field label="Email" :error="$errors->first('email')">
            <input type="email" wire:model.live="email" placeholder="nama@email.com" autocomplete="email"
                class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] bg-white px-4 text-[16px] outline-none focus:border-[#0F172A]">
        </x-ui.field>

        <x-ui.field label="Password" :error="$errors->first('password')">
            <input type="password" wire:model.live="password" placeholder="••••••••" autocomplete="current-password"
                class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] bg-white px-4 text-[16px] outline-none focus:border-[#0F172A]">
        </x-ui.field>

        <label class="flex min-h-[44px] items-center gap-3 text-[14px]">
            <input type="checkbox" wire:model.live="remember" class="h-5 w-5 rounded accent-[#0F172A]">
            Ingat saya
        </label>

        @error('email')
            @if (str_contains($message, 'salah') || str_contains($message, 'aktif'))
                <x-ui.alert type="danger" icon="alert-circle">{{ $message }}</x-ui.alert>
            @endif
        @enderror

        <button type="submit" wire:loading.attr="disabled"
            class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-2xl bg-[#0F172A] font-semibold text-white disabled:opacity-70">
            <span wire:loading.remove>Masuk</span>
            {{-- <span wire:loading>Memeriksa...</span> --}}
        </button>
    </form>

    <!-- Kartu Notifikasi Demo -->
    <div class="mt-6 rounded-2xl border border-[#E2E8F0] bg-white p-4 text-[13px] text-[#64748B] shadow-sm">
        <div class="flex items-center justify-between pb-2 border-b border-[#F1F5F9]">
            <div class="flex items-center gap-2">
                <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
                <p class="font-semibold text-[#0F172A]">Akses Akun Demo</p>
            </div>
            <span class="rounded-full bg-[#F1F5F9] px-2.5 py-0.5 text-[11px] font-medium text-[#475569]">
                Demo Mode
            </span>
        </div>

        <p class="mt-3 text-[#475569] leading-relaxed">
            Ingin mencoba fitur aplikasi {{ config('app.name', 'Perumku') }}? Silakan hubungi kami untuk mendapatkan kredensial akun demo.
        </p>

        <a 
            href="https://wa.me/6289525645332?text=Halo%20Perumku,%20saya%20ingin%20mencoba%20akun%20demo" 
            target="_blank" 
            rel="noopener noreferrer"
            class="mt-3.5 flex w-full items-center justify-center gap-1.5 rounded-xl bg-emerald-50 px-4 py-2.5 text-[13px] font-semibold text-emerald-700 transition-colors hover:bg-emerald-100"
        >
            <span>Hubungi Whatsapp</span>
            <svg class="h-4 w-4 fill-current text-emerald-600" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
            </svg>
            {{-- <span>089525645332</span> --}}
        </a>
    </div>
</div>