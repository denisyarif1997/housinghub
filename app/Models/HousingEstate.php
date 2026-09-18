<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HousingEstate extends Model
{
    protected $fillable = ['code', 'name', 'address', 'phone', 'email', 'logo', 'status'];

    public function blocks(): HasMany
    {
        return $this->hasMany(HousingBlock::class);
    }

    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }
}
