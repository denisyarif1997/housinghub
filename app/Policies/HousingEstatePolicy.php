<?php

namespace App\Policies;

use App\Models\HousingEstate;
use App\Models\User;

class HousingEstatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage-residents') || $user->hasPermission('manage-houses');
    }

    public function view(User $user, HousingEstate $housingEstate): bool
    {
        return $user->hasPermission('manage-residents') || $user->hasPermission('manage-houses');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-houses');
    }

    public function update(User $user, HousingEstate $housingEstate): bool
    {
        return $user->hasPermission('manage-houses');
    }

    public function delete(User $user, HousingEstate $housingEstate): bool
    {
        return $user->hasPermission('manage-houses');
    }
}
