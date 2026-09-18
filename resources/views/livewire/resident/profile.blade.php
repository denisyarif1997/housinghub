<div class="space-y-4">
    <div class="flex items-center gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#0F172A] text-lg font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div class="min-w-0">
            <p class="truncate text-[16px] font-bold">{{ $user->name }}</p>
            <p class="truncate text-[13px] text-[#64748B]">{{ $user->email }} • {{ $user->role?->name }}</p>
        </div>
    </div>

    @if (session('success'))
        <x-ui.alert type="success" icon="check-circle-2">{{ session('success') }}</x-ui.alert>
    @endif

    <form wire:submit="save" class="space-y-4 rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <x-ui.field label="Nama" :error="$errors->first('name')">
            <input wire:model.live="name" class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] px-4">
        </x-ui.field>
        <x-ui.field label="No. HP" :error="$errors->first('phone')">
            <input wire:model.live="phone" inputmode="tel" class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] px-4">
        </x-ui.field>
        <x-ui.field label="Password Baru (opsional)" :error="$errors->first('new_password')">
            <input type="password" wire:model.live="new_password" placeholder="Minimal 8 karakter" class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] px-4">
        </x-ui.field>
        <button class="flex min-h-[48px] w-full items-center justify-center rounded-2xl bg-[#0F172A] font-semibold text-white">
            <span wire:loading.remove>Simpan</span><span wire:loading>Menyimpan...</span>
        </button>
    </form>

    <div class="space-y-2">
        <p class="text-[15px] font-bold">Menu</p>
        @if (! $user->hasRole('resident'))
            <a href="{{ route('admin.dashboard') }}" class="flex min-h-[52px] items-center gap-3 rounded-2xl border border-[#E2E8F0] bg-white px-4 text-[15px] font-medium"><i data-lucide="layout-dashboard" class="h-5 w-5"></i>Dashboard Admin</a>
        @endif
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="flex min-h-[52px] w-full items-center gap-3 rounded-2xl border border-red-200 bg-white px-4 text-[15px] font-semibold text-red-700"><i data-lucide="log-out" class="h-5 w-5"></i>Keluar</button>
        </form>
    </div>
</div>
