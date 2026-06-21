<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use App\Models\StockRecovery;
use App\Models\StockOut;
use App\Models\Product;

class StockRecoveryReportList extends Component
{
    public $dateFrom = '';
    public $dateTo = '';
    public $status = '';
    public $stockOutId = '';

    public $searchQuery = '';

    public $showCreateModal = false;
    public $editingRecovery = null;
    public $printingRecoveryId = null;
    public $selectedIds = [];
    public $isPrintingSelected = false;

    public $recoveryNumber = '';
    public $stockOutIdForm = '';
    public $productId = '';
    public $quantity = 1;
    public $unit = '';
    public $recoveryDate = '';
    public $statusForm = 'pending';
    public $notes = '';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->recoveryDate = now()->format('Y-m-d');
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
        $this->generateRecoveryNumber();
    }

    public function edit($id)
    {
        $recovery = StockRecovery::find($id);
        if ($recovery) {
            $this->editingRecovery = $id;
            $this->recoveryNumber = $recovery->recovery_number;
            $this->stockOutIdForm = $recovery->stock_out_id;
            $this->productId = $recovery->product_id;
            $this->quantity = $recovery->quantity;
            $this->unit = $recovery->unit;
            $this->recoveryDate = $recovery->recovery_date->format('Y-m-d');
            $this->statusForm = $recovery->status;
            $this->notes = $recovery->notes;
            $this->showCreateModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'recoveryNumber' => 'required|unique:stock_recoveries,recovery_number' . ($this->editingRecovery ? ",$this->editingRecovery" : ''),
            'stockOutIdForm' => 'nullable|exists:stock_outs,id',
            'productId' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:50',
            'recoveryDate' => 'required|date',
            'statusForm' => 'required|in:pending,approved,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'recovery_number' => $this->recoveryNumber,
            'stock_out_id' => $this->stockOutIdForm ?: null,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'recovery_date' => $this->recoveryDate,
            'status' => $this->statusForm,
            'notes' => $this->notes,
            'created_by' => auth()->id() ?? 1,
        ];

        if ($this->editingRecovery) {
            StockRecovery::find($this->editingRecovery)->update($data);
            session()->flash('message', 'Phiếu thu hồi được cập nhật thành công!');
        } else {
            StockRecovery::create($data);
            session()->flash('message', 'Phiếu thu hồi được tạo thành công!');
        }

        $this->resetForm();
        $this->showCreateModal = false;
    }

    public function destroy($id)
    {
        $recovery = StockRecovery::find($id);
        if ($recovery) {
            $recovery->delete();
            session()->flash('message', 'Phiếu thu hồi đã bị xóa!');
        }
    }

    public function printSingle($id)
    {
        $this->printingRecoveryId = $id;
        $this->isPrintingSelected = false;
        $this->dispatch('trigger-print');
    }

    public function printAll()
    {
        $this->printingRecoveryId = null;
        $this->isPrintingSelected = false;
        $this->dispatch('trigger-print');
    }

    public function printSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một phiếu để in.');
            return;
        }
        $this->printingRecoveryId = null;
        $this->isPrintingSelected = true;
        $this->dispatch('trigger-print');
    }

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

    public function resetForm()
    {
        $this->reset([
            'recoveryNumber',
            'stockOutIdForm',
            'productId',
            'quantity',
            'unit',
            'recoveryDate',
            'statusForm',
            'notes',
            'editingRecovery',
        ]);
        $this->quantity = 1;
        $this->statusForm = 'pending';
        $this->recoveryDate = now()->format('Y-m-d');
    }

    public function generateRecoveryNumber()
    {
        $last = StockRecovery::latest('id')->first();
        $sequence = ($last ? intval(preg_replace('/[^0-9]/', '', $last->recovery_number)) : 0) + 1;
        $this->recoveryNumber = 'SCR-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    public function updatedProductId()
    {
        $product = Product::find($this->productId);
        if ($product) {
            $this->unit = $product->unit ?? '';
        }
    }

    public function getRecoveriesProperty()
    {
        $query = StockRecovery::with(['stockOut', 'product', 'creator']);

        if ($this->dateFrom) {
            $query->whereDate('recovery_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('recovery_date', '<=', $this->dateTo);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->stockOutId) {
            $query->where('stock_out_id', $this->stockOutId);
        }
        if ($this->searchQuery) {
            $query->where(function($q) {
                $q->where('recovery_number', 'like', '%' . $this->searchQuery . '%')
                  ->orWhereHas('product', fn($p) => $p->where('code', 'like', '%' . $this->searchQuery . '%')
                   ->orWhere('name', 'like', '%' . $this->searchQuery . '%'));
            });
        }

        return $query->latest()->get();
    }

    public function getStockOutsProperty()
    {
        return StockOut::orderBy('id', 'desc')->get();
    }

    public function getProductsProperty()
    {
        return Product::where('status', 'active')->orderBy('name')->get();
    }

    public function getSummaryProperty()
    {
        $query = StockRecovery::whereBetween('recovery_date', [$this->dateFrom, $this->dateTo]);

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'total_quantity' => (clone $query)->sum('quantity'),
        ];
    }

    public function render()
    {
        return view('livewire.warehouse.stock-recovery-report-list', [
            'recoveries' => $this->recoveries,
            'stockOuts' => $this->stockOuts,
            'products' => $this->products,
            'summary' => $this->summary,
        ])->layout('components.warehouse-layout', ['title' => 'Thu hồi phế phẩm']);
    }
}