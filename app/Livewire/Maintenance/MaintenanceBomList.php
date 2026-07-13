<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceBom;
use Livewire\Component;
use Livewire\WithPagination;

class MaintenanceBomList extends Component
{
    use WithPagination;

    public $search = '';
    public $cycleFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'cycleFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCycleFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $boms = MaintenanceBom::with(['asset', 'items', 'creator'])
            ->when($this->search, function ($query) {
                $query->where('bom_code', 'like', '%' . $this->search . '%')
                    ->orWhere('maintenance_level', 'like', '%' . $this->search . '%')
                    ->orWhereHas('asset', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('asset_code', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('items.product', function ($q) {
                        $q->where('code', 'like', '%' . $this->search . '%')
                          ->orWhere('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->cycleFilter, function ($query) {
                $query->where('cycle', $this->cycleFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('components.maintenance.maintenance-bom-list', compact('boms'));
    }
}
