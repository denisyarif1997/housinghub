<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintResponse extends Model
{
    protected $fillable = [
        'complaint_id', 'user_id', 'resident_id', 'message', 'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function authorName(): string
    {
        return $this->user?->name ?? $this->resident?->name ?? 'Pengguna';
    }

    public function isFromStaff(): bool
    {
        return $this->user_id !== null;
    }
}
