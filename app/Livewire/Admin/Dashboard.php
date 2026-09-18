<?php

namespace App\Livewire\Admin;

use App\Models\Billing;
use App\Models\House;
use App\Models\HousingBlock;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\User;
use App\Support\Currency;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    #[Layout('layouts.admin', ['title' => 'Dashboard'])]
    public function render()
    {
        $user = auth()->user();

        $canManageHouses = $user->hasPermission('manage-houses');
        $canManageResidents = $user->hasPermission('manage-residents');
        $canManageUsers = $user->hasPermission('manage-user');
        $canManageBilling = $user->hasPermission('manage-billing');
        $canViewPayments = $user->hasPermission('manage-payment', 'verify-payment');

        $year = now()->year;
        $month = now()->month;

        $periodBillings = ($canManageBilling || $canViewPayments) ? Billing::forPeriod($year, $month) : null;

        $periodTotal = $periodBillings ? (float) (clone $periodBillings)->where('status', '!=', 'cancelled')->sum('total') : 0.0;
        $periodPaid = $periodBillings ? (float) (clone $periodBillings)->where('status', '!=', 'cancelled')->sum('paid_amount') : 0.0;
        $outstanding = $periodBillings
            ? (float) (clone $periodBillings)->outstanding()->sum('total') - (float) (clone $periodBillings)->outstanding()->sum('paid_amount')
            : 0.0;

        return view('livewire.admin.dashboard', [
            'canManageHouses' => $canManageHouses,
            'canManageResidents' => $canManageResidents,
            'canManageUsers' => $canManageUsers,
            'canManageBilling' => $canManageBilling,
            'canViewPayments' => $canViewPayments,

            'totalHouses' => $canManageHouses ? House::count() : 0,
            'totalResidents' => $canManageResidents ? Resident::count() : 0,
            'totalBlocks' => $canManageHouses ? HousingBlock::count() : 0,
            'totalUsers' => $canManageUsers ? User::count() : 0,

            'periodLabel' => Currency::period($year, $month),
            'periodTotal' => $periodTotal,
            'periodPaid' => $periodPaid,
            'periodOutstanding' => max(0, $outstanding),
            'periodUnpaidCount' => $periodBillings ? (clone $periodBillings)->outstanding()->count() : 0,
            'pendingPayments' => $canViewPayments ? Payment::where('status', 'pending')->count() : 0,
            'pendingPaymentsAmount' => $canViewPayments ? (float) Payment::where('status', 'pending')->sum('amount') : 0.0,

            'recentPayments' => $canViewPayments
                ? Payment::with(['resident', 'billing.house.block'])->orderByDesc('id')->take(5)->get()
                : collect(),

            'recentHouses' => $canManageHouses
                ? House::with(['block', 'houseResidents.resident'])->latest()->take(5)->get()
                : collect(),
        ]);
    }
}
