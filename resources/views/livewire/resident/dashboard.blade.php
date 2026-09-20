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
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <div class="flex items-center justify-between gap-2">
            <p class="text-[14px] font-bold">Tagihan IPL</p>
            <a href="{{ route('resident.ipl.index') }}" wire:navigate
                class="inline-flex shrink-0 items-center gap-1 text-[13px] font-semibold text-[#64748B] underline">
                Lihat semua <i data-lucide="chevron-right" class="h-4 w-4"></i>
            </a>
        </div>

        @if (($overdueBillings ?? collect())->isNotEmpty())
            <div class="mt-3 rounded-xl border border-red-200 bg-red-50 p-3">
                <p class="text-[13px] font-bold text-red-700">
                    {{ $overdueBillings->count() }} tagihan lewat jatuh tempo
                </p>
                <p class="mt-0.5 text-[13px] text-red-600">
                    Total tunggakan <span class="font-bold">@rupiah($overdueBillings->sum(fn ($b) => $b->remaining()))</span>
                </p>
                <div class="mt-2 space-y-2">
                    @foreach ($overdueBillings as $overdue)
                        <a href="{{ route('resident.ipl.show', $overdue) }}" wire:navigate
                            class="block rounded-xl border border-red-100 bg-white p-3 active:bg-red-50">
                            <div class="flex items-start justify-between gap-2">
                                <p class="min-w-0 flex-1 truncate text-[14px] font-bold text-slate-900">{{ $overdue->periodLabel() }}</p>
                                <x-ui.badge color="{{ $overdue->statusColor() }}" class="shrink-0">{{ $overdue->statusLabel() }}</x-ui.badge>
                            </div>
                            <p class="mt-0.5 truncate text-[12px] text-slate-500">
                                {{ $overdue->iplRate?->name ?? 'Tarif tidak tercatat' }} · JT {{ $overdue->due_date?->format('d/m/Y') ?? '-' }}
                            </p>
                            <div class="mt-1.5 flex items-center justify-between gap-2">
                                <p class="text-[13px] font-bold text-red-700">@rupiah($overdue->remaining())</p>
                                <span class="inline-flex shrink-0 items-center gap-1 text-[12px] font-semibold text-[#64748B]">
                                    Detail <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <p class="mt-3 text-[13px] font-semibold text-[#64748B]">Tagihan bulan ini ({{ \App\Support\Currency::period(now()->year, now()->month) }})</p>
        <div class="mt-2 space-y-2">
            @forelse (($currentBillings ?? collect()) as $billing)
                <a href="{{ route('resident.ipl.show', $billing) }}" wire:navigate
                    class="block rounded-xl border border-[#E2E8F0] p-3 active:bg-slate-50">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[14px] font-bold">{{ $billing->iplRate?->name ?? 'Tagihan IPL' }}</p>
                            <p class="mt-0.5 text-[13px] text-[#64748B]">Rumah {{ $billing->house?->fullLabel() ?? '-' }} · JT {{ $billing->due_date?->format('d/m/Y') }}</p>
                        </div>
                        <x-ui.badge color="{{ $billing->statusColor() }}" class="shrink-0">{{ $billing->statusLabel() }}</x-ui.badge>
                    </div>
                    <div class="mt-1.5 flex items-center justify-between gap-2">
                        <p class="text-[15px] font-bold">@rupiah($billing->remaining() > 0 ? $billing->remaining() : $billing->total)</p>
                        <span class="inline-flex shrink-0 items-center gap-1 text-[12px] font-semibold text-[#64748B]">
                            Detail <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
                        </span>
                    </div>
                </a>
            @empty
                <div class="rounded-xl bg-slate-50 p-3 text-[13px] text-[#64748B]">
                    Belum ada tagihan untuk bulan ini.
                </div>
            @endforelse
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
    </div>

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
            @foreach ([['wallet', 'IPL', 'resident.ipl.index'], ['messages-square', 'Forum', 'resident.forum.index'], ['wrench', 'Aduan', 'resident.complaints.index'], ['crown', 'Catur', 'resident.chess.index'], ['megaphone', 'Info', 'resident.info.index'], ['user', 'Profil', 'resident.profile']] as [$icon, $label, $route])
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
