<?php

namespace App\Livewire\Warehouse;

use App\Models\InventoryTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\TransactionExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class TransactionDetailReport extends Component
{
    use WithPagination;

    public $dateFrom = '';
    public $dateTo = '';
    public $filterType = '';
    public $filterProduct = '';
    public $filterAssetCode = '';
    public $filterUser = '';
    public $selectedIds = [];

    public function toggleSelectAll($idsOnPage)
    {
        $idsOnPage = collect($idsOnPage)->map(fn($id) => (string)$id)->toArray();
        $isAllSelectedOnPage = count(array_intersect($idsOnPage, $this->selectedIds)) === count($idsOnPage);

        if ($isAllSelectedOnPage) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, $idsOnPage));
        } else {
            $this->selectedIds = array_values(array_unique(array_merge($this->selectedIds, $idsOnPage)));
        }
    }

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function exportExcel()
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

        if ($this->filterAssetCode) {
            $query->whereHasMorph('reference', [\App\Models\StockOut::class], function($q) {
                $q->where('asset_code', 'like', "%{$this->filterAssetCode}%");
            });
        }

        if ($this->filterUser) {
            $query->whereHas('creator', function($q) {
                $q->where('name', 'like', "%{$this->filterUser}%");
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get();
        return Excel::download(new TransactionExport($data), 'bao_cao_chi_tiet_giao_dich_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        $query = InventoryTransaction::with(['product', 'creator', 'product.inventory', 'reference'])
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

        if ($this->filterAssetCode) {
            $query->whereHasMorph('reference', [\App\Models\StockOut::class], function($q) {
                $q->where('asset_code', 'like', "%{$this->filterAssetCode}%");
            });
        }

        if ($this->filterUser) {
            $query->whereHas('creator', function($q) {
                $q->where('name', 'like', "%{$this->filterUser}%");
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('livewire.warehouse.transaction-detail-report', [
            'transactions' => $transactions,
        ]);
    }
}
