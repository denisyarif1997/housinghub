<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Payment extends Model
{
    protected $fillable = [
        'payment_number', 'billing_id', 'resident_id', 'user_id', 'amount',
        'payment_date', 'payment_method', 'reference_number', 'proof',
        'proof_blob', 'proof_mime', 'proof_name', 'proof_size',
        'status', 'verified_by', 'verified_at', 'rejection_reason', 'notes',
    ];

    protected $hidden = [
        // Jangan pernah serialize binary bukti (bisa 2 MB) ke JSON/log.
        'proof_blob',
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

    public function cashTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
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

    public function hasProof(): bool
    {
        // Cek ringan dulu (tanpa menyentuh bytes BLOB 2 MB).
        if (! empty($this->proof)) {
            return true;
        }

        if (! empty($this->proof_size) || ! empty($this->proof_mime) || ! empty($this->proof_name)) {
            return true;
        }

        $blob = $this->proof_blob;

        if (is_resource($blob)) {
            $blob = stream_get_contents($blob);
        }

        return ! empty($blob);
    }

    public function proofUrl(): ?string
    {
        if (! $this->exists && ! $this->id) {
            return null;
        }

        if (! $this->hasProof()) {
            return null;
        }

        return route('payments.proof', $this);
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
