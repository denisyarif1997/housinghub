<div class="space-y-4">
    {{-- Search --}}
    <div class="relative">
        <i data-lucide="search" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[#94A3B8]"></i>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari deskripsi, referensi..."
            class="min-h-[48px] w-full rounded-2xl border-0 bg-white pl-10 pr-4 text-[14px] shadow-[0_4px_16px_-4px_rgba(19,78,74,0.10)] focus:ring-2 focus:ring-teal-500">
    </div>

    {{-- Daftar Kas --}}
    <div class="space-y-3">
        @forelse ($transactions as $tx)
            <div class="block rounded-[22px] bg-white p-4 shadow-[0_8px_30px_-6px_rgba(19,78,74,0.12)]">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[15px] font-semibold text-slate-800">{{ \Illuminate\Support\Str::before($tx->description, ' — ') }}</p>
                        <p class="mt-0.5 truncate font-mono text-[12px] text-[#64748B]">{{ $tx->reference ?: '-' }}</p>
                    </div>
                    @if ($tx->type === 'in')
                        <x-ui.badge color="success" class="shrink-0">Masuk</x-ui.badge>
                    @elseif ($tx->type === 'out')
                        <x-ui.badge color="danger" class="shrink-0">Keluar</x-ui.badge>
                    @else
                        <x-ui.badge color="warning" class="shrink-0">Lainnya</x-ui.badge>
                    @endif
                </div>
                <div class="mt-2 flex items-end justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[17px] font-bold @if($tx->type === 'in') text-teal-600 @elseif($tx->type === 'out') text-red-600 @endif">
                            {{ $tx->type === 'out' ? '-' : ($tx->type === 'in' ? '+' : '') }}@rupiah($tx->amount)
                        </p>
                        <p class="mt-0.5 text-[12px] text-[#64748B]">
                            {{ $tx->transaction_date->format('d/m/Y') }} · {{ $tx->account->name }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <x-ui.empty-state icon="banknote" title="Belum ada histori kas" subtitle="Histori transaksi kas akan muncul di sini." />
        @endforelse
    </div>

    <div>{{ $transactions->links() }}</div>
</div>
