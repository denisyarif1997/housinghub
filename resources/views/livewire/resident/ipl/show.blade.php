@php use App\Support\Currency; @endphp

<div class="space-y-4">
    @if (session('success'))
        <x-ui.alert type="success" icon="check-circle-2">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert type="danger" icon="alert-circle">{{ session('error') }}</x-ui.alert>
    @endif

    <a href="{{ route('resident.ipl.index') }}" wire:navigate class="inline-flex items-center gap-2 text-[14px] font-semibold text-[#64748B]">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali ke daftar tagihan
    </a>

    <div class="rounded-2xl bg-[#0F172A] p-4 text-white">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[13px] text-white/70">Tagihan {{ $periodLabel }}</p>
                <p class="mt-1 text-[26px] font-bold">@rupiah($billing->total)</p>
                <p class="text-[13px] text-white/70">Rumah {{ $billing->house?->fullLabel() ?? '-' }}</p>
                <p class="text-[13px] text-white/70">Jatuh tempo {{ $billing->due_date?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <x-ui.badge color="{{ $billing->statusColor() }}">{{ $billing->statusLabel() }}</x-ui.badge>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-[13px]">
            <div class="rounded-xl bg-white/10 p-3">
                <p class="text-white/70">Sudah Dibayar</p>
                <p class="font-bold">@rupiah($billing->paid_amount)</p>
            </div>
            <div class="rounded-xl bg-white/10 p-3">
                <p class="text-white/70">Sisa Tagihan</p>
                <p class="font-bold">@rupiah($remaining)</p>
            </div>
        </div>
    </div>

    @if ($billing->status === 'cancelled')
        <x-ui.alert type="warning" icon="alert-triangle">Tagihan ini dibatalkan oleh pengelola.</x-ui.alert>
    @endif

    @if ($hasPendingPayment)
        <x-ui.alert type="info" icon="clock">
            Konfirmasi pembayaran Anda sedang menunggu verifikasi pengelola.
        </x-ui.alert>
    @endif

    @if ($remaining > 0 && $billing->status !== 'cancelled' && ! $hasPendingPayment)
        <form wire:submit="submitPayment" class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
            <p class="font-bold">Konfirmasi Pembayaran</p>
            <p class="mt-1 text-[14px] text-[#64748B]">Isi data pembayaran, lalu tunggu verifikasi pengelola.</p>

            <div class="mt-3 space-y-3">
                <x-ui.field label="Nominal Dibayar (Rp)" :error="$errors->first('amount')">
                    <input wire:model="amount" type="number" min="1" step="1" inputmode="numeric"
                        class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] px-3 text-[15px] outline-none focus:border-[#0F172A]">
                </x-ui.field>

                <x-ui.field label="Tanggal Bayar" :error="$errors->first('payment_date')">
                    <input wire:model="payment_date" type="date" max="{{ now()->toDateString() }}"
                        class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] px-3 text-[15px] outline-none focus:border-[#0F172A]">
                </x-ui.field>

                <x-ui.field label="Metode Pembayaran" :error="$errors->first('payment_method')">
                    <select wire:model="payment_method" class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] bg-white px-3 text-[15px]">
                        <option value="transfer">Transfer Bank</option>
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                        <option value="other">Lainnya</option>
                    </select>
                </x-ui.field>

                <x-ui.field label="No. Referensi (opsional)" :error="$errors->first('reference_number')">
                    <input wire:model="reference_number" placeholder="Contoh: 8812-3341"
                        class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] px-3 text-[15px] outline-none focus:border-[#0F172A]">
                </x-ui.field>

                <x-ui.field label="Bukti Pembayaran (opsional, maks 2 MB)" :error="$errors->first('proof')">
                    <input wire:model="proof" type="file" accept="image/*"
                        class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] bg-white px-3 py-3 text-[14px]">
                    <div wire:loading wire:target="proof" class="mt-1 text-[13px] text-[#64748B]">Mengunggah...</div>
                    @if ($proof)
                        <img src="{{ $proof->temporaryUrl() }}" alt="Pratinjau bukti" class="mt-2 h-32 w-auto rounded-xl border border-[#E2E8F0] object-cover">
                    @endif
                </x-ui.field>

                <x-ui.field label="Catatan (opsional)" :error="$errors->first('notes')">
                    <input wire:model="notes" placeholder="Contoh: transfer dari rekening istri"
                        class="min-h-[48px] w-full rounded-xl border border-[#E2E8F0] px-3 text-[15px] outline-none focus:border-[#0F172A]">
                </x-ui.field>
            </div>

            <button type="submit" wire:loading.attr="disabled"
                class="mt-4 flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-[#0F172A] font-semibold text-white disabled:opacity-60">
                <i data-lucide="send" class="h-5 w-5"></i>
                <span wire:loading.remove wire:target="submitPayment">Kirim Konfirmasi</span>
                <span wire:loading wire:target="submitPayment">Mengirim...</span>
            </button>
        </form>
    @endif

    {{-- Riwayat pembayaran saya --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-4">
        <p class="font-bold">Riwayat Pembayaran</p>

        <div class="mt-3 space-y-2">
            @forelse ($payments as $payment)
                <div class="rounded-xl border border-[#E2E8F0] p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-[12px] text-[#64748B]">{{ $payment->payment_number }}</p>
                            <p class="mt-0.5 text-[16px] font-bold">@rupiah($payment->amount)</p>
                            <p class="text-[13px] text-[#64748B]">{{ $payment->payment_date?->format('d/m/Y') }} · {{ $payment->methodLabel() }}</p>
                            @if ($payment->rejection_reason)
                                <p class="mt-1 text-[13px] text-red-600">Alasan ditolak: {{ $payment->rejection_reason }}</p>
                            @endif
                        </div>
                        <x-ui.badge color="{{ $payment->statusColor() }}">{{ $payment->statusLabel() }}</x-ui.badge>
                    </div>

                    @if ($payment->hasProof())
                        <a href="{{ $payment->proofUrl() }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1 text-[13px] font-semibold underline">
                            <i data-lucide="image" class="h-3.5 w-3.5"></i> Lihat bukti
                        </a>
                    @endif

                    @if ($payment->status === 'pending')
                        <button wire:click="deletePayment({{ $payment->id }})" wire:confirm="Batalkan konfirmasi pembayaran ini?"
                            class="mt-3 flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-red-200 text-[14px] font-semibold text-red-700">
                            <i data-lucide="trash-2" class="h-4 w-4"></i> Batalkan Konfirmasi
                        </button>
                    @endif
                </div>
            @empty
                <x-ui.empty-state icon="receipt" title="Belum ada pembayaran" subtitle="Konfirmasi pembayaran Anda akan tampil di sini." />
            @endforelse
        </div>
    </div>
</div>