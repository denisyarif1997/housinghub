<?php

namespace App\Models;

use App\Support\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'house_id', 'resident_id', 'ipl_rate_id',
        'period_month', 'period_year', 'amount', 'discount', 'total',
        'paid_amount', 'due_date', 'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function iplRate(): BelongsTo
    {
        return $this->belongsTo(IplRate::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function verifiedPayments(): HasMany
    {
        return $this->hasMany(Payment::class)->where('status', 'verified');
    }

    public function scopeForPeriod(Builder $query, int $year, int $month): Builder
    {
        return $query->where('period_year', $year)->where('period_month', $month);
    }

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }

    public function periodLabel(): string
    {
        return Currency::period((int) $this->period_year, (int) $this->period_month);
    }

    public function remaining(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        if (! in_array($this->status, ['unpaid', 'partial'], true) || ! $this->due_date) {
            return false;
        }

        return $this->due_date->startOfDay()->lt(now()->startOfDay());
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'Lunas',
            'partial' => 'Bayar Sebagian',
            'cancelled' => 'Dibatalkan',
            default => $this->isOverdue() ? 'Terlambat' : 'Belum Bayar',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'paid' => 'green',
            'partial' => 'amber',
            'cancelled' => 'slate',
            default => $this->isOverdue() ? 'red' : 'sky',
        };
    }

    /**
     * Hitung ulang paid_amount & status dari pembayaran yang sudah diverifikasi.
     */
    public function syncPaymentStatus(): void
    {
        if ($this->status === 'cancelled') {
            return;
        }

        $paid = (float) $this->payments()->where('status', 'verified')->sum('amount');
        $total = (float) $this->total;

        $status = 'unpaid';
        if ($total > 0 && $paid >= $total) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partial';
        }

        $this->update(['paid_amount' => $paid, 'status' => $status]);
    }
}
