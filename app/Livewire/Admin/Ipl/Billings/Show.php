<?php

namespace App\Livewire\Admin\Ipl\Billings;

use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Show extends Component
{
    public Billing $billing;

    public string $payment_amount = '';

    public string $payment_method = 'cash';

    public string $payment_date = '';

    public string $reference_number = '';

    public string $payment_notes = '';

    public ?int $rejectingId = null;

    public string $rejection_reason = '';

    public function mount(Billing $billing): void
    {
        $this->authorize('view', $billing);
        abort_unless(
            auth()->user()->hasPermission('manage-billing') || auth()->user()->hasPermission('verify-payment'),
            403
        );

        $this->billing = $billing;
        $this->payment_date = now()->toDateString();
        $this->payment_amount = (string) $billing->remaining();
    }

    /**
     * Catat pembayaran manual (tunai/transfer) yang langsung terverifikasi.
     */
    public function recordPayment(): void
    {
        abort_unless(auth()->user()->hasPermission('manage-payment'), 403);

        if ($this->billing->status === 'cancelled') {
            session()->flash('error', 'Tagihan sudah dibatalkan.');

            return;
        }

        $data = $this->validate([
            'payment_amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:cash,transfer,qris,other'],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'payment_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'payment_amount.required' => 'Nominal pembayaran wajib diisi.',
            'payment_amount.min' => 'Nominal pembayaran harus lebih dari 0.',
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
        ]);

        $billing = $this->billing;

        DB::transaction(function () use ($billing, $data) {
            Payment::create([
                'payment_number' => Payment::generateNumber($billing),
                'billing_id' => $billing->id,
                'resident_id' => $billing->resident_id,
                'user_id' => auth()->id(),
                'amount' => $data['payment_amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?: null,
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $data['payment_notes'] ?: 'Dicatat manual oleh pengelola.',
            ]);

            $billing->syncPaymentStatus();

            ActivityLog::record([
                'user_id' => auth()->id(), 'action' => 'create', 'module' => 'payments',
                'subject_type' => Billing::class, 'subject_id' => $billing->id,
                'description' => 'Mencatat pembayaran IPL '.$billing->invoice_number,
                'new_values' => $billing->fresh()->toArray(),
            ]);
        });

        $this->billing->refresh();
        $this->reset(['payment_amount', 'reference_number', 'payment_notes']);
        $this->payment_amount = (string) $this->billing->remaining();
        $this->payment_date = now()->toDateString();

        session()->flash('success', 'Pembayaran berhasil dicatat.');
    }

    public function verifyPayment(int $id): void
    {
        $this->authorize('verify', Payment::class);

        $payment = Payment::where('billing_id', $this->billing->id)->findOrFail($id);

        if ($payment->status === 'verified') {
            session()->flash('error', 'Pembayaran sudah terverifikasi.');

            return;
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            $this->billing->syncPaymentStatus();

            ActivityLog::record([
                'user_id' => auth()->id(), 'action' => 'approve', 'module' => 'payments',
                'subject_type' => Payment::class, 'subject_id' => $payment->id,
                'description' => 'Memverifikasi pembayaran '.$payment->payment_number,
                'new_values' => $payment->fresh()->toArray(),
            ]);
        });

        $this->billing->refresh();
        $this->payment_amount = (string) $this->billing->remaining();

        session()->flash('success', 'Pembayaran '.$payment->payment_number.' berhasil diverifikasi.');
    }

    public function startReject(int $id): void
    {
        $this->authorize('reject', Payment::class);

        $this->rejectingId = $id;
        $this->rejection_reason = '';
        $this->resetValidation('rejection_reason');
    }

    public function cancelReject(): void
    {
        $this->reset(['rejectingId', 'rejection_reason']);
    }

    public function rejectPayment(): void
    {
        $this->authorize('reject', Payment::class);

        if (! $this->rejectingId) {
            return;
        }

        $data = $this->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $payment = Payment::where('billing_id', $this->billing->id)->findOrFail($this->rejectingId);

        DB::transaction(function () use ($payment, $data) {
            $payment->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'rejection_reason' => $data['rejection_reason'],
            ]);

            $this->billing->syncPaymentStatus();

            ActivityLog::record([
                'user_id' => auth()->id(), 'action' => 'reject', 'module' => 'payments',
                'subject_type' => Payment::class, 'subject_id' => $payment->id,
                'description' => 'Menolak pembayaran '.$payment->payment_number.' — '.$data['rejection_reason'],
                'new_values' => $payment->fresh()->toArray(),
            ]);
        });

        $this->billing->refresh();
        $this->payment_amount = (string) $this->billing->remaining();
        $number = $payment->payment_number;
        $this->cancelReject();

        session()->flash('success', 'Pembayaran '.$number.' ditolak.');
    }

    public function deletePayment(int $id): void
    {
        $payment = Payment::where('billing_id', $this->billing->id)->findOrFail($id);

        $this->authorize('delete', $payment);

        if ($payment->status === 'verified') {
            session()->flash('error', 'Pembayaran terverifikasi tidak bisa dihapus.');

            return;
        }

        $number = $payment->payment_number;

        DB::transaction(function () use ($payment) {
            $old = $payment->toArray();
            $payment->delete();

            $this->billing->syncPaymentStatus();

            ActivityLog::record([
                'user_id' => auth()->id(), 'action' => 'delete', 'module' => 'payments',
                'subject_type' => Payment::class, 'subject_id' => $payment->id,
                'description' => 'Menghapus pembayaran '.$payment->payment_number,
                'old_values' => $old,
            ]);
        });

        $this->billing->refresh();

        session()->flash('success', 'Pembayaran '.$number.' berhasil dihapus.');
    }

    public function cancelBilling(): void
    {
        $this->authorize('update', $this->billing);

        if ($this->billing->verifiedPayments()->exists()) {
            session()->flash('error', 'Tagihan sudah memiliki pembayaran terverifikasi sehingga tidak bisa dibatalkan.');

            return;
        }

        $this->billing->update(['status' => 'cancelled']);

        ActivityLog::record([
            'user_id' => auth()->id(), 'action' => 'update', 'module' => 'billings',
            'subject_type' => Billing::class, 'subject_id' => $this->billing->id,
            'description' => 'Membatalkan tagihan '.$this->billing->invoice_number,
            'new_values' => $this->billing->fresh()->toArray(),
        ]);

        $this->billing->refresh();

        session()->flash('success', 'Tagihan dibatalkan.');
    }

    public function activeBilling(): void
    {
         $this->billing->update(['status' => 'unpaid']);

        ActivityLog::record([
            'user_id' => auth()->id(), 'action' => 'update', 'module' => 'billings',
            'subject_type' => Billing::class, 'subject_id' => $this->billing->id,
            'description' => 'Aktivasi tagihan yang di batalkan '.$this->billing->invoice_number,
            'new_values' => $this->billing->fresh()->toArray(),
        ]);

        $this->billing->refresh();

        session()->flash('success', 'Tagihan aktif kembali.');
    }

    #[Layout('layouts.admin', ['title' => 'Detail Tagihan IPL'])]
    public function render()
    {
        return view('livewire.admin.ipl.billings.show', [
            'billing' => $this->billing->load(['house.block', 'resident', 'iplRate', 'waterRate']),
            'payments' => $this->billing->payments()
                ->with(['verifier', 'resident'])
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->get(),
        ]);
    }
}
