<div class="space-y-4">
    @if (session('success'))
        <x-ui.alert type="success" icon="check-circle-2">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert type="danger" icon="alert-circle">{{ session('error') }}</x-ui.alert>
    @endif

    {{-- Ringkasan tagihan aktif --}}
    <div class="rounded-2xl bg-[#0F172A] p-4 text-white">
        <p class="text-[13px] text-white/70">Total Tagihan Belum Lunas</p>
        <p class="mt-1 text-[28px] font-bold">@rupiah($summary['outstandingAmount'])</p>
        <p class="text-[13px] text-white/70">{{ $summary['outstandingCount'] }} tagihan menunggu pembayaran</p>
        <div class="mt-3 flex items-center justify-between rounded-xl bg-white/10 p-3 text-[13px]">
            <span>Sudah dibayar tahun {{ now()->year }}</span>
            <span class="font-bold">@rupiah($summary['paidThisYear'])</span>
        </div>
    </div>

    @if ($summary['currentPeriod'])
        @php $current = $summary['currentPeriod']; @endphp
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[13px] text-[#64748B]">Tagihan {{ $current->periodLabel() }}</p>
                    <p class="mt-1 text-[20px] font-bold">@rupiah($current->total)</p>
                    <p class="text-[13px] text-[#64748B]">Jatuh tempo {{ $current->due_date?->format('d/m/Y') }}</p>
                </div>
                <x-ui.badge color="{{ $current->statusColor() }}">{{ $current->statusLabel() }}</x-ui.badge>
            </div>
            <a href="{{ route('resident.ipl.show', $current) }}" wire:navigate
                class="mt-3 flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-[#0F172A] font-semibold text-white">
                <i data-lucide="wallet" class="h-5 w-5"></i>
                {{ $current->remaining() > 0 ? 'Bayar Sekarang' : 'Lihat Detail' }}
            </a>
        </div>
    @endif

    {{-- Filter --}}
    <div class="grid grid-cols-2 gap-2">
        <select wire:model.live="statusFilter" class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
            <option value="">Semua Status</option>
            <option value="unpaid">Belum Bayar</option>
            <option value="partial">Bayar Sebagian</option>
            <option value="paid">Lunas</option>
            <option value="cancelled">Dibatalkan</option>
        </select>
        <select wire:model.live="yearFilter" class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
            <option value="">Semua Tahun</option>
            @foreach ($years as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>

    {{-- Daftar tagihan --}}
    <div class="space-y-2">
        @forelse ($billings as $billing)
            <a href="{{ route('resident.ipl.show', $billing) }}" wire:navigate
                class="block rounded-2xl border border-[#E2E8F0] bg-white p-4 active:bg-slate-50">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[16px] font-bold">{{ $billing->periodLabel() }}</p>
                        <p class="mt-0.5 font-mono text-[12px] text-[#64748B]">{{ $billing->invoice_number }}</p>
                        <p class="text-[13px] text-[#64748B]">Rumah {{ $billing->house?->fullLabel() ?? '-' }}</p>
                    </div>
                    <x-ui.badge color="{{ $billing->statusColor() }}">{{ $billing->statusLabel() }}</x-ui.badge>
                </div>
                <div class="mt-2 flex items-end justify-between">
                    <div>
                        <p class="text-[18px] font-bold">@rupiah($billing->total)</p>
                        @if ($billing->remaining() > 0 && $billing->paid_amount > 0)
                            <p class="text-[13px] text-[#64748B]">Sisa @rupiah($billing->remaining())</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#64748B]">
                        Detail <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    </span>
                </div>
            </a>
        @empty
            <x-ui.empty-state icon="file-text" title="Belum ada tagihan IPL"
                subtitle="Tagihan akan muncul setelah pengelola melakukan generate periode." />
        @endforelse
    </div>

    <div>{{ $billings->links() }}</div>
</div>