<?php

namespace App\Livewire\Admin\Ipl\Payments;

use App\Models\ActivityLog;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public ?int $rejectingId = null;

    public string $rejection_reason = '';

    public function mount(): void
    {
        abort_unless(
            auth()->user()->hasPermission('verify-payment') || auth()->user()->hasPermission('manage-payment'),
            403
        );

        $this->statusFilter = 'pending';
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'statusFilter'], true)) {
            $this->resetPage();
        }
    }

    public function verify(int $id): void
    {
        $this->authorize('verify', Payment::class);

        $payment = Payment::with('billing')->findOrFail($id);

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

            $payment->billing?->syncPaymentStatus();

            ActivityLog::record([
                'user_id' => auth()->id(), 'action' => 'approve', 'module' => 'payments',
                'subject_type' => Payment::class, 'subject_id' => $payment->id,
                'description' => 'Memverifikasi pembayaran '.$payment->payment_number,
                'new_values' => $payment->fresh()->toArray(),
            ]);
        });

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

    public function reject(): void
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

        $payment = Payment::with('billing')->findOrFail($this->rejectingId);

        DB::transaction(function () use ($payment, $data) {
            $payment->update([
                'status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'rejection_reason' => $data['rejection_reason'],
            ]);

            $payment->billing?->syncPaymentStatus();

            ActivityLog::record([
                'user_id' => auth()->id(), 'action' => 'reject', 'module' => 'payments',
                'subject_type' => Payment::class, 'subject_id' => $payment->id,
                'description' => 'Menolak pembayaran '.$payment->payment_number.' — '.$data['rejection_reason'],
                'new_values' => $payment->fresh()->toArray(),
            ]);
        });

        $number = $payment->payment_number;
        $this->cancelReject();

        session()->flash('success', 'Pembayaran '.$number.' ditolak.');
    }

    public function delete(int $id): void
    {
        $payment = Payment::with('billing')->findOrFail($id);

        $this->authorize('delete', $payment);

        if ($payment->status === 'verified') {
            session()->flash('error', 'Pembayaran terverifikasi tidak bisa dihapus.');

            return;
        }

        $number = $payment->payment_number;

        DB::transaction(function () use ($payment) {
            $old = $payment->toArray();
            $payment->delete();

            $payment->billing?->syncPaymentStatus();

            ActivityLog::record([
                'user_id' => auth()->id(), 'action' => 'delete', 'module' => 'payments',
                'subject_type' => Payment::class, 'subject_id' => $payment->id,
                'description' => 'Menghapus pembayaran '.$payment->payment_number,
                'old_values' => $old,
            ]);
        });

        session()->flash('success', 'Pembayaran '.$number.' berhasil dihapus.');
    }

    protected function filteredQuery(): Builder
    {
        return Payment::query()
            ->when($this->search, fn (Builder $q) => $q->where(fn (Builder $qq) => $qq
                ->where('payment_number', 'like', "%{$this->search}%")
                ->orWhere('reference_number', 'like', "%{$this->search}%")
                ->orWhereHas('resident', fn (Builder $r) => $r->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('billing', fn (Builder $b) => $b->where('invoice_number', 'like', "%{$this->search}%"))))
            ->when($this->statusFilter, fn (Builder $q) => $q->where('status', $this->statusFilter));
    }

    #[Layout('layouts.admin', ['title' => 'Pembayaran IPL'])]
    public function render()
    {
        $query = $this->filteredQuery();

        return view('livewire.admin.ipl.payments.index', [
            'payments' => (clone $query)
                ->with(['billing.house.block', 'resident', 'verifier'])
                ->orderByRaw("case when status = 'pending' then 0 else 1 end")
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->paginate(15),
            'summary' => [
                'pending' => Payment::where('status', 'pending')->count(),
                'pendingAmount' => (float) Payment::where('status', 'pending')->sum('amount'),
                'verifiedThisMonth' => (float) Payment::where('status', 'verified')
                    ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                    ->sum('amount'),
            ],
        ]);
    }
}
