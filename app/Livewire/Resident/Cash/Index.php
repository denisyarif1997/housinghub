<?php

namespace App\Livewire\Resident\Cash;

use App\Models\CashTransaction;
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

    #[Layout('layouts.resident', ['title' => 'Histori Kas'])]
    public function render()
    {
        return view('livewire.resident.cash.index', [
            'transactions' => CashTransaction::with(['account'])
                ->whereNull('payment_id') // Sembunyikan transaksi dari pembayaran tagihan IPL/Air
                ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                    ->where('description', 'like', "%{$this->search}%")
                    ->orWhere('reference', 'like', "%{$this->search}%")
                    ->orWhere('category', 'like', "%{$this->search}%")))
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }
}
