<?php

namespace App\Livewire\Warehouse;

use App\Models\InventoryTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class Reports extends Component
{
    use WithPagination;

    public $dateFrom = '';
    public $dateTo = '';
    public $filterType = '';
    public $filterProduct = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render()
    {
        $query = InventoryTransaction::with(['product', 'creator'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterProduct) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', "%{$this->filterProduct}%")
                  ->orWhere('code', 'like', "%{$this->filterProduct}%");
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(25);

        // Tổng hợp nhập/xuất trong khoảng thời gian
        $summary = InventoryTransaction::selectRaw("
                SUM(CASE WHEN type = 'import' THEN quantity ELSE 0 END) as total_import,
                SUM(CASE WHEN type = 'export' THEN ABS(quantity) ELSE 0 END) as total_export,
                SUM(CASE WHEN type = 'adjust' THEN quantity ELSE 0 END) as total_adjust
            ")
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59'])
            ->first();

        return view('livewire.warehouse.reports', [
            'transactions' => $transactions,
            'summary' => $summary,
        ]);
    }
}
