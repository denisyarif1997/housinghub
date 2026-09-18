<?php

namespace App\Livewire\Resident;

use App\Models\Billing;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    #[Layout('layouts.resident', ['title' => 'Halo'])]
    public function render()
    {
        $user = Auth::user()->loadMissing(['resident.houseResidents.house.block', 'role']);

        $house = $user->resident?->houseResidents->firstWhere('is_primary', true)?->house
            ?? $user->resident?->houseResidents->first()?->house;

        $billings = collect();

        if ($user->resident_id) {
            $residentId = (int) $user->resident_id;

            $billings = Billing::query()
                ->where(fn ($query) => $query
                    ->where('resident_id', $residentId)
                    ->orWhereHas('house.houseResidents', fn ($relation) => $relation
                        ->where('resident_id', $residentId)
                        ->where('status', 'active')))
                ->orderByDesc('period_year')
                ->orderByDesc('period_month')
                ->get();
        }

        $outstanding = $billings->whereIn('status', ['unpaid', 'partial']);

        $currentBilling = $billings->first(fn ($billing) => (int) $billing->period_month === (int) now()->month
            && (int) $billing->period_year === (int) now()->year);

        return view('livewire.resident.dashboard', [
            'user' => $user,
            'house' => $house,
            'currentBilling' => $currentBilling,
            'outstandingAmount' => (float) $outstanding->sum(fn ($billing) => $billing->remaining()),
            'outstandingCount' => $outstanding->count(),
        ]);
    }
}
