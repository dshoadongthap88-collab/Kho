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
    public $filterExportedApp = '';
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

    public function printSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng chọn ít nhất 1 giao dịch để in báo cáo.');
            return;
        }

        return redirect()->route('warehouse.reports.transaction-detail.print', ['ids' => implode(',', $this->selectedIds)]);
    }

    public function deleteSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng chọn ít nhất 1 giao dịch để xóa.');
            return;
        }

        InventoryTransaction::whereIn('id', $this->selectedIds)->delete();
        $this->selectedIds = [];
        session()->flash('message', 'Đã xóa thành công các giao dịch được chọn.');
    }

    public function toggleExportedApp($transactionId)
    {
        $transaction = InventoryTransaction::find($transactionId);
        if ($transaction) {
            $transaction->is_exported_app = !$transaction->is_exported_app;
            $transaction->save();
        }
    }

    public function exportExcel()
    {
        $query = InventoryTransaction::with(['product', 'creator'])
            ->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);

        if ($this->filterType) {
            if ($this->filterType === 'transfer') {
                $query->whereIn('type', ['transfer_in', 'transfer_out']);
            } else {
                $query->where('type', $this->filterType);
            }
        }

        if ($this->filterExportedApp !== '') {
            $query->where('is_exported_app', $this->filterExportedApp === '1');
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
            if ($this->filterType === 'transfer') {
                $query->whereIn('type', ['transfer_in', 'transfer_out']);
            } else {
                $query->where('type', $this->filterType);
            }
        }

        if ($this->filterExportedApp !== '') {
            $query->where('is_exported_app', $this->filterExportedApp === '1');
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
