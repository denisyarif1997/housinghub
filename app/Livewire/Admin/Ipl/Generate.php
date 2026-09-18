<?php

namespace App\Livewire\Admin\Ipl;

use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\House;
use App\Models\HousingEstate;
use App\Models\IplRate;
use App\Services\IplBillingService;
use App\Support\Currency;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Generate extends Component
{
    public string $period_month = '';

    public string $period_year = '';

    public string $housing_estate_id = '';

    public string $due_day = '10';

    /** @var array<string, mixed>|null */
    public ?array $result = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermission('manage-billing'), 403);

        $this->period_month = (string) now()->month;
        $this->period_year = (string) now()->year;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['period_month', 'period_year', 'housing_estate_id', 'due_day'], true)) {
            $this->result = null;
        }
    }

    public function generate(): void
    {
        abort_unless(auth()->user()->hasPermission('manage-billing'), 403);

        $data = $this->validate([
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'between:2020,2100'],
            'housing_estate_id' => ['nullable', 'exists:housing_estates,id'],
            'due_day' => ['required', 'integer', 'between:1,28'],
        ], [
            'period_month.required' => 'Bulan periode wajib dipilih.',
            'period_year.required' => 'Tahun periode wajib dipilih.',
            'due_day.between' => 'Tanggal jatuh tempo harus antara 1 sampai 28.',
        ]);

        $estateId = $data['housing_estate_id'] ? (int) $data['housing_estate_id'] : null;
        $month = (int) $data['period_month'];
        $year = (int) $data['period_year'];

        $result = app(IplBillingService::class)->generate(
            $year,
            $month,
            $estateId,
            (int) $data['due_day'],
            auth()->id(),
        );

        $this->result = $result;

        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'generate',
            'module' => 'billings',
            'description' => 'Generate tagihan IPL '.Currency::period($year, $month)
                .' — dibuat: '.$result['created']
                .', dipulihkan: '.$result['restored']
                .', dilewati: '.$result['skipped']
                .', tanpa tarif: '.$result['no_rate'],
        ]);

        $processed = $result['created'] + $result['restored'];

        if ($processed > 0) {
            session()->flash('success', $processed.' tagihan IPL berhasil dibuat untuk periode '.Currency::period($year, $month).'.');
        } elseif ($result['skipped'] > 0) {
            session()->flash('info', 'Semua rumah sudah memiliki tagihan pada periode ini. Tidak ada duplikat yang dibuat.');
        } else {
            session()->flash('error', 'Tidak ada tagihan yang dibuat. Pastikan sudah ada tarif IPL yang berlaku dan rumah berstatus aktif.');
        }
    }

    #[Layout('layouts.admin', ['title' => 'Generate Tagihan IPL'])]
    public function render()
    {
        $estateId = $this->housing_estate_id ? (int) $this->housing_estate_id : null;
        $month = (int) $this->period_month ?: (int) now()->month;
        $year = (int) $this->period_year ?: (int) now()->year;
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();

        $activeHouses = House::query()
            ->where('status', 'active')
            ->when($estateId, fn ($q) => $q->where('housing_estate_id', $estateId))
            ->count();

        $alreadyBilled = Billing::query()
            ->withTrashed()
            ->forPeriod($year, $month)
            ->when($estateId, fn ($q) => $q->whereHas('house', fn ($h) => $h->where('housing_estate_id', $estateId)))
            ->count();

        return view('livewire.admin.ipl.generate', [
            'estates' => HousingEstate::orderBy('name')->get(),
            'activeHouses' => $activeHouses,
            'alreadyBilled' => $alreadyBilled,
            'rate' => IplRate::forDate($estateId, $periodStart),
        ]);
    }
}
