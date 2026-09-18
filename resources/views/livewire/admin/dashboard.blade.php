@php
    $hasStats = $canManageHouses || $canManageResidents || $canManageUsers;
    $hasFinance = $canManageBilling || $canViewPayments;
@endphp

<div class="space-y-4">
    @if ($hasStats)
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @if ($canManageHouses)
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100"><i data-lucide="house" class="h-5 w-5"></i></div>
                    <p class="mt-3 text-2xl font-bold">{{ $totalHouses }}</p>
                    <p class="text-[13px] text-[#64748B]">Total Rumah</p>
                </div>
            @endif
            @if ($canManageResidents)
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100"><i data-lucide="users" class="h-5 w-5"></i></div>
                    <p class="mt-3 text-2xl font-bold">{{ $totalResidents }}</p>
                    <p class="text-[13px] text-[#64748B]">Total Warga</p>
                </div>
            @endif
            @if ($canManageHouses)
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100"><i data-lucide="layout-grid" class="h-5 w-5"></i></div>
                    <p class="mt-3 text-2xl font-bold">{{ $totalBlocks }}</p>
                    <p class="text-[13px] text-[#64748B]">Total Blok</p>
                </div>
            @endif
            @if ($canManageUsers)
                <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100"><i data-lucide="user-cog" class="h-5 w-5"></i></div>
                    <p class="mt-3 text-2xl font-bold">{{ $totalUsers }}</p>
                    <p class="text-[13px] text-[#64748B]">Total User</p>
                </div>
            @endif
        </div>
    @endif

    @unless ($hasStats || $hasFinance)
        <x-ui.empty-state icon="shield-off" title="Belum ada modul yang bisa diakses"
            subtitle="Hubungi administrator untuk memberikan hak akses pada role Anda." />
    @endunless

    @if ($hasFinance)
    {{-- Ringkasan IPL periode berjalan --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-[16px] font-bold">IPL {{ $periodLabel }}</h2>
                <p class="text-[13px] text-[#64748B]">Periode berjalan</p>
            </div>
            @if ($canManageBilling)
                <a href="{{ route('admin.ipl.billings.index') }}" wire:navigate class="text-[14px] font-semibold">Kelola Tagihan</a>
            @endif
        </div>

        <div class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-[12px] text-[#64748B]">Total Tagihan</p>
                <p class="mt-1 text-[18px] font-bold">@rupiah($periodTotal)</p>
            </div>
            <div class="rounded-xl bg-emerald-50 p-3">
                <p class="text-[12px] text-emerald-700">Terkumpul</p>
                <p class="mt-1 text-[18px] font-bold text-emerald-800">@rupiah($periodPaid)</p>
            </div>
            <div class="rounded-xl bg-red-50 p-3">
                <p class="text-[12px] text-red-700">Tunggakan</p>
                <p class="mt-1 text-[18px] font-bold text-red-800">@rupiah($periodOutstanding)</p>
            </div>
            <div class="rounded-xl bg-amber-50 p-3">
                <p class="text-[12px] text-amber-700">Belum Lunas</p>
                <p class="mt-1 text-[18px] font-bold text-amber-800">{{ $periodUnpaidCount }} rumah</p>
            </div>
        </div>

        <a href="{{ route('admin.ipl.payments.index') }}" wire:navigate
            class="mt-3 flex min-h-[44px] items-center justify-between rounded-xl border border-[#E2E8F0] px-3 text-[14px]">
            <span class="flex items-center gap-2"><i data-lucide="clock" class="h-4 w-4"></i> Pembayaran menunggu verifikasi</span>
            <span class="flex items-center gap-2">
                <span class="font-semibold">@rupiah($pendingPaymentsAmount)</span>
                <span class="rounded-full {{ $pendingPayments > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-[#64748B]' }} px-2 py-0.5 text-[12px] font-bold">{{ $pendingPayments }}</span>
            </span>
        </a>
    </div>

    {{-- Pembayaran terbaru --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-[16px] font-bold">Pembayaran Terbaru</h2>
            <a href="{{ route('admin.ipl.payments.index') }}" wire:navigate class="text-[14px] font-semibold">Lihat Semua</a>
        </div>
        <div class="space-y-2">
            @forelse ($recentPayments as $payment)
                <div class="flex items-center gap-3 rounded-xl border border-[#E2E8F0] p-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100"><i data-lucide="receipt" class="h-5 w-5"></i></div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[14px] font-semibold">{{ $payment->resident?->name ?? $payment->billing?->house?->fullLabel() ?? '-' }}</p>
                        <p class="truncate text-[13px] text-[#64748B]">{{ $payment->payment_date?->format('d/m/Y') }} · {{ $payment->methodLabel() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[14px] font-bold">@rupiah($payment->amount)</p>
                        <x-ui.badge color="{{ $payment->statusColor() }}">{{ $payment->statusLabel() }}</x-ui.badge>
                    </div>
                </div>
            @empty
                <x-ui.empty-state icon="receipt" title="Belum ada pembayaran" subtitle="Generate tagihan IPL terlebih dahulu." />
            @endforelse
        </div>
    </div>
    @endif

    @if ($canManageHouses)
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-[16px] font-bold">Rumah Terbaru</h2>
            <a href="{{ route('admin.houses.index') }}" wire:navigate class="text-[14px] font-semibold">Lihat Semua</a>
        </div>
        <div class="space-y-2">
            @forelse ($recentHouses as $house)
                <div class="flex items-center gap-3 rounded-xl border border-[#E2E8F0] p-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 font-bold">{{ $house->block?->code }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[14px] font-semibold">Rumah {{ $house->fullLabel() }}</p>
                        <p class="truncate text-[13px] text-[#64748B]">{{ $house->houseResidents->first()?->resident?->name ?? 'Belum ada penghuni' }}</p>
                    </div>
                    <x-ui.badge color="green">{{ $house->occupancy_status }}</x-ui.badge>
                </div>
            @empty
                <x-ui.empty-state title="Belum ada rumah" subtitle="Tambahkan data rumah terlebih dahulu." />
            @endforelse
        </div>
    </div>
    @endif
</div>
