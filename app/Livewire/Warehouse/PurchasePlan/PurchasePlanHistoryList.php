<?php

namespace App\Livewire\Warehouse\PurchasePlan;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PurchasePlanHistory;

class PurchasePlanHistoryList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    protected $updatesQueryString = ['search', 'statusFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $histories = PurchasePlanHistory::with(['purchasePlan.product', 'user'])
            ->when($this->search, function ($q) {
                $q->whereHas('purchasePlan.product', function ($q2) {
                    $q2->where('code', 'like', '%' . $this->search . '%')
                       ->orWhere('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('notes', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('new_status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.warehouse.purchase-plan.purchase-plan-history-list', [
            'histories' => $histories
        ]);
    }
}
