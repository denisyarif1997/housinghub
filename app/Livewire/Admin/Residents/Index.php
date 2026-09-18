<?php

namespace App\Livewire\Admin\Residents;

use App\Models\ActivityLog;
use App\Models\HouseResident;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->authorize('delete', Resident::class);
        $resident = Resident::findOrFail($id);
        DB::transaction(function () use ($resident) {
            User::where('resident_id', $resident->id)->delete();
            HouseResident::where('resident_id', $resident->id)->delete();
            $resident->delete();
        });

        ActivityLog::record([
            'user_id' => auth()->id(), 'action' => 'delete', 'module' => 'residents',
            'subject_type' => Resident::class, 'subject_id' => $id,
            'description' => 'Menghapus warga '.$resident->name,
        ]);

        session()->flash('success', 'Warga berhasil dihapus.');
    }

    #[Layout('layouts.admin', ['title' => 'Warga'])]
    public function render()
    {
        $residents = Resident::with(['houseResidents.house.block', 'user'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('nik', 'like', "%{$this->search}%")->orWhere('phone', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.residents.index', compact('residents'));
    }
}
