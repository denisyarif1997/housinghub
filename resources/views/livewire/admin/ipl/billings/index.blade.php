@php use App\Support\Currency; @endphp

<div class="space-y-4">
    @if (session('success'))
        <x-ui.alert type="success" icon="check-circle-2">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert type="danger" icon="alert-circle">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3">
            <p class="text-[12px] text-[#64748B]">Jumlah Tagihan</p>
            <p class="mt-1 text-xl font-bold">{{ number_format($summary['count'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3">
            <p class="text-[12px] text-[#64748B]">Total Tagihan</p>
            <p class="mt-1 text-xl font-bold">@rupiah($summary['total'])</p>
        </div>
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3">
            <p class="text-[12px] text-[#64748B]">Sudah Dibayar</p>
            <p class="mt-1 text-xl font-bold text-emerald-700">@rupiah($summary['paid'])</p>
        </div>
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-3">
            <p class="text-[12px] text-[#64748B]">Belum Lunas</p>
            <p class="mt-1 text-xl font-bold text-red-600">{{ number_format($summary['unpaid'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="space-y-2">
        <div class="relative">
            <i data-lucide="search" class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-[#64748B]"></i>
            <input wire:model.live.debounce.300ms="search" placeholder="Cari invoice / nama / nomor rumah..."
                class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] bg-white pl-11 pr-4 text-[15px] outline-none focus:border-[#0F172A]">
        </div>
        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
            <select wire:model.live="statusFilter" class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                <option value="">Semua Status</option>
                <option value="unpaid">Belum Bayar</option>
                <option value="partial">Bayar Sebagian</option>
                <option value="paid">Lunas</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
            <select wire:model.live="blockFilter" class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                <option value="">Semua Blok</option>
                @foreach ($blocks as $block)
                    <option value="{{ $block->id }}">Blok {{ $block->code }}</option>
                @endforeach
            </select>
            <select wire:model.live="periodMonth" class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                <option value="">Semua Bulan</option>
                @foreach ($months as $number => $label)
                    <option value="{{ $number }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="periodYear" class="min-h-[48px] w-full rounded-2xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                <option value="">Semua Tahun</option>
                @foreach (range(now()->year + 1, now()->year - 3) as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="resetFilter" class="text-[14px] font-semibold text-[#64748B] underline">Reset filter</button>
    </div>

    {{-- Mobile list --}}
    <div class="space-y-2 md:hidden">
        @forelse ($billings as $billing)
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[16px] font-bold">Rumah {{ $billing->house?->fullLabel() ?? '-' }}</p>
                        <p class="mt-0.5 truncate text-[13px] text-[#64748B]">{{ $billing->invoice_number }}</p>
                        <p class="text-[13px] text-[#64748B]">{{ $billing->resident?->name ?? 'Tanpa penghuni' }} · {{ $billing->periodLabel() }}</p>
                    </div>
                    <x-ui.badge color="{{ $billing->statusColor() }}">{{ $billing->statusLabel() }}</x-ui.badge>
                </div>
                <div class="mt-2 flex items-end justify-between">
                    <div>
                        <p class="text-[18px] font-bold">@rupiah($billing->total)</p>
                        <p class="text-[13px] text-[#64748B]">Dibayar @rupiah($billing->paid_amount) · Sisa @rupiah($billing->remaining())</p>
                    </div>
                    <p class="text-[12px] text-[#64748B]">JT {{ $billing->due_date?->format('d/m/Y') }}</p>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('admin.ipl.billings.show', $billing) }}" wire:navigate class="flex min-h-[44px] items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] text-[14px] font-semibold"><i data-lucide="eye" class="h-4 w-4"></i> Detail</a>
                    @if ($billing->status !== 'paid' && $billing->status !== 'cancelled')
                        <button wire:click="markPaid({{ $billing->id }})" wire:confirm="Tandai tagihan {{ $billing->invoice_number }} sebagai lunas?" class="flex min-h-[44px] items-center justify-center gap-2 rounded-xl border border-emerald-200 text-[14px] font-semibold text-emerald-700"><i data-lucide="check-circle-2" class="h-4 w-4"></i> Lunas</button>
                    @else
                        <button wire:click="delete({{ $billing->id }})" wire:confirm="Hapus tagihan {{ $billing->invoice_number }}?" class="flex min-h-[44px] items-center justify-center gap-2 rounded-xl border border-red-200 text-[14px] font-semibold text-red-700"><i data-lucide="trash-2" class="h-4 w-4"></i> Hapus</button>
                    @endif
                </div>
            </div>
        @empty
            <x-ui.empty-state icon="file-text" title="Belum ada tagihan" subtitle="Generate tagihan IPL terlebih dahulu." />
        @endforelse
    </div>

    {{-- Desktop table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white md:block">
        <table class="w-full text-left text-[14px]">
            <thead class="bg-slate-50 text-[13px] text-[#64748B]">
                <tr>
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Rumah</th>
                    <th class="px-4 py-3">Penghuni</th>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Tagihan</th>
                    <th class="px-4 py-3">Dibayar</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($billings as $billing)
                    <tr class="border-t border-[#E2E8F0]">
                        <td class="px-4 py-3 font-mono text-[13px]">{{ $billing->invoice_number }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $billing->house?->fullLabel() ?? '-' }}</td>
                        <td class="px-4 py-3 text-[#64748B]">{{ $billing->resident?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $billing->periodLabel() }}</td>
                        <td class="px-4 py-3 font-semibold">@rupiah($billing->total)</td>
                        <td class="px-4 py-3">@rupiah($billing->paid_amount)</td>
                        <td class="px-4 py-3"><x-ui.badge color="{{ $billing->statusColor() }}">{{ $billing->statusLabel() }}</x-ui.badge></td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.ipl.billings.show', $billing) }}" wire:navigate class="font-semibold">Detail</a>
                            @if ($billing->status !== 'paid' && $billing->status !== 'cancelled')
                                <button wire:click="markPaid({{ $billing->id }})" wire:confirm="Tandai lunas?" class="ml-3 font-semibold text-emerald-700">Lunas</button>
                            @endif
                            <button wire:click="delete({{ $billing->id }})" wire:confirm="Hapus tagihan ini?" class="ml-3 font-semibold text-red-600">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-[#64748B]">Belum ada tagihan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $billings->links() }}</div>
</div>