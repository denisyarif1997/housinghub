<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WaterMeterReading extends Model
{
    protected $fillable = [
        'house_id', 'period_month', 'period_year', 'meter_start', 'meter_end',
        'usage_m3', 'water_rate_id', 'price_per_m3', 'admin_fee', 'amount',
        'billing_id', 'status', 'recorded_by', 'notes', 'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'period_month' => 'integer',
            'period_year' => 'integer',
            'meter_start' => 'decimal:2',
            'meter_end' => 'decimal:2',
            'usage_m3' => 'decimal:2',
            'price_per_m3' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function waterRate(): BelongsTo
    {
        return $this->belongsTo(WaterRate::class);
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * URL foto meteran yang tersimpan di disk public.
     */
    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    /**
     * Hitung pemakaian & total dari meter + tarif (termasuk pemakaian minimum).
     *
     * @return array{usage:float, billable:float, water:float, total:float}
     */
    public static function calculate(float $start, float $end, float $price, float $adminFee = 0, float $minUsage = 0): array
    {
        $usage = max(0, $end - $start);
        $billable = max($usage, max(0, $minUsage));
        $water = $billable * max(0, $price);

        return [
            'usage' => $usage,
            'billable' => $billable,
            'water' => $water,
            'total' => $water + max(0, $adminFee),
        ];
    }
}
