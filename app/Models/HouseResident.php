<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseResident extends Model
{
    protected $fillable = [
        'house_id', 'resident_id', 'relationship', 'is_owner', 'is_primary',
        'start_date', 'end_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'is_owner' => 'boolean',
            'is_primary' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
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
}
