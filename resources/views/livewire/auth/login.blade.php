<div>
    <h1 class="text-2xl font-bold">Masuk ke {{ config('app.name', 'HousingHub') }}</h1>
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

    {{-- <div class="mt-6 rounded-2xl border border-[#E2E8F0] bg-white p-4 text-[13px] text-[#64748B]"> --}}
        {{-- <p class="font-semibold text-[#0F172A]">Akun demo</p> --}}
        {{-- <p class="mt-1">Silahkan Hubungi </p> --}}
        {{-- <p>Warga: warga.a.1@housinghub.id / password123</p> --}}
    {{-- </div> --}}
</div>
