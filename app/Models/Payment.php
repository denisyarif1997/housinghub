<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $fillable = [
        'payment_number', 'billing_id', 'resident_id', 'user_id', 'amount',
        'payment_date', 'payment_method', 'reference_number', 'proof',
        'status', 'verified_by', 'verified_at', 'rejection_reason', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function house(): HasOneThrough
    {
        return $this->hasOneThrough(House::class, Billing::class, 'id', 'id', 'billing_id', 'house_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'verified' => 'Terverifikasi',
            'rejected' => 'Ditolak',
            default => 'Menunggu Verifikasi',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'verified' => 'green',
            'rejected' => 'red',
            default => 'amber',
        };
    }

    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'other' => 'Lainnya',
            default => 'Transfer Bank',
        };
    }

    public function proofUrl(): ?string
    {
        return $this->proof ? Storage::disk('public')->url($this->proof) : null;
    }

    /**
     * Nomor pembayaran unik, contoh: PAY/20260917/IPL-202609-0001-01
     */
    public static function generateNumber(Billing $billing): string
    {
        $base = 'PAY/'.now()->format('Ymd').'/'.str_replace('/', '-', $billing->invoice_number);
        $sequence = 1;

        do {
            $candidate = $base.'-'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $sequence++;
        } while (static::where('payment_number', $candidate)->exists());

        return $candidate;
    }
}
