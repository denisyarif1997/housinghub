<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resident extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nik', 'name', 'gender', 'birth_date', 'phone', 'email', 'photo', 'status',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function houseResidents(): HasMany
    {
        return $this->hasMany(HouseResident::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function billings(): HasMany
    {
        return $this->hasMany(Billing::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function primaryHouse(): ?House
    {
        $pivot = $this->houseResidents()->where('is_primary', true)->where('status', 'active')->first();

        return $pivot?->house;
    }
}
