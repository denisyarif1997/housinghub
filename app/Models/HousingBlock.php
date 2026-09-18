<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HousingBlock extends Model
{
    protected $fillable = ['housing_estate_id', 'code', 'name', 'description', 'status'];

    public function estate(): BelongsTo
    {
        return $this->belongsTo(HousingEstate::class, 'housing_estate_id');
    }

    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }

    public function label(): string
    {
        return "Blok {$this->code} - {$this->name}";
    }
}
