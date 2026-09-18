<div class="space-y-4">
    <div>
        <h1 class="text-[22px] font-bold">Halo, {{ explode(' ', $user->name)[0] }} 👋</h1>
        <p class="text-[14px] text-[#64748B]">Rumah {{ $house?->fullLabel() ?? '-' }}</p>
    </div>

    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <p class="text-[14px] text-[#64748B]">Rumah Saya</p>
        <p class="mt-1 text-[18px] font-bold">{{ $house ? 'Rumah ' . $house->fullLabel() : 'Belum ada rumah' }}</p>
        <p class="mt-1 text-[14px] text-[#64748B]">{{ $house?->address ?? 'Hubungi pengelola untuk verifikasi hunian.' }}</p>
    </div>

    {{-- Kartu IPL --}}
    <a href="{{ route('resident.ipl.index') }}" wire:navigate class="block rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[14px] text-[#64748B]">IPL Bulan Ini</p>
                @if ($currentBilling)
                    <p class="mt-1 text-[22px] font-bold">@rupiah($currentBilling->total - $currentBilling->paid_amount)</p>
                    <p class="text-[13px] text-[#64748B]">{{ $currentBilling->periodLabel() }} · JT {{ $currentBilling->due_date?->format('d/m/Y') }}</p>
                @else
                    <p class="mt-1 text-[18px] font-bold text-[#64748B]">Belum ada tagihan</p>
                    <p class="text-[13px] text-[#64748B]">Tagihan periode {{ \App\Support\Currency::period(now()->year, now()->month) }}</p>
                @endif
            </div>
            @if ($currentBilling)
                <x-ui.badge color="{{ $currentBilling->statusColor() }}">{{ $currentBilling->statusLabel() }}</x-ui.badge>
            @else
                <x-ui.badge color="slate">Kosong</x-ui.badge>
            @endif
        </div>

        @if ($outstandingCount > 0)
            <div class="mt-3 rounded-xl bg-red-50 p-3 text-[13px] text-red-700">
                <span class="font-semibold">{{ $outstandingCount }} tagihan belum lunas</span> dengan total @rupiah($outstandingAmount)
            </div>
        @else
            <div class="mt-3 rounded-xl bg-emerald-50 p-3 text-[13px] font-semibold text-emerald-700">
                Semua tagihan IPL sudah lunas. Terima kasih! 🎉
            </div>
        @endif

        <p class="mt-3 text-[13px] font-semibold underline">Lihat semua tagihan IPL</p>
    </a>

    <div class="grid grid-cols-1 gap-3">
        <a href="{{ route('resident.info.index') }}" wire:navigate class="flex items-center gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-[18px]">📢</div>
            <div class="min-w-0 flex-1">
                <p class="text-[14px] font-semibold">Info & Pengumuman</p>
                <p class="truncate text-[13px] text-[#64748B]">Lihat pengumuman & kirim pengaduan</p>
            </div>
            <i data-lucide="chevron-right" class="h-4 w-4 shrink-0 text-[#94A3B8]"></i>
        </a>
    </div>

    <div>
        <h2 class="mb-3 text-[16px] font-bold">Akses Cepat</h2>
        <div class="grid grid-cols-3 gap-3 text-center text-[13px] font-medium">
            @foreach ([['wallet', 'IPL', 'resident.ipl.index'], ['messages-square', 'Forum', 'resident.forum.index'], ['wrench', 'Aduan', 'resident.complaints.index'], ['megaphone', 'Info', 'resident.info.index'], ['user', 'Profil', 'resident.profile']] as [$icon, $label, $route])
                @if ($route)
                    <a href="{{ route($route) }}" wire:navigate class="flex min-h-[84px] flex-col items-center justify-center gap-2 rounded-2xl border border-[#E2E8F0] bg-white p-3">
                        <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>{{ $label }}
                    </a>
                @else
                    <div class="flex min-h-[84px] flex-col items-center justify-center gap-2 rounded-2xl border border-[#E2E8F0] bg-white p-3 opacity-60">
                        <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>{{ $label }}
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
