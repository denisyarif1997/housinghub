<?php

namespace App\Livewire\Admin\Houses;

use App\Models\ActivityLog;
use App\Models\House;
use App\Models\HousingBlock;
use App\Models\HousingEstate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $blockFilter = '';

    public string $statusFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBlockFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->authorize('delete', House::class);
        $house = House::findOrFail($id);
        $old = $house->toArray();
        $house->delete();

        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'module' => 'houses',
            'subject_type' => House::class,
            'subject_id' => $id,
            'description' => 'Menghapus rumah '.($house->block?->code.'-'.$house->house_number),
            'old_values' => $old,
        ]);

        session()->flash('success', 'Rumah berhasil dihapus.');
    }

    #[Layout('layouts.admin', ['title' => 'Rumah'])]
    public function render()
    {
        $houses = House::with(['block', 'estate', 'houseResidents.resident'])
            ->when($this->search, fn ($q) => $q->where('house_number', 'like', "%{$this->search}%")->orWhere('address', 'like', "%{$this->search}%"))
            ->when($this->blockFilter, fn ($q) => $q->where('housing_block_id', $this->blockFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.houses.index', [
            'houses' => $houses,
            'blocks' => HousingBlock::orderBy('code')->get(),
            'estates' => HousingEstate::orderBy('name')->get(),
        ]);
    }
}
