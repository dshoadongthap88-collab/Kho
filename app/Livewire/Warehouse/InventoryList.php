<?php

namespace App\Livewire\Warehouse;

use App\Models\Inventory;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = ''; // all, sufficient, warning, critical
    public $filterBrand = '';
    public $filterLocation = '';
    public $selectedItems = []; // Array of inventory IDs
    public $sortField = 'products.name';
    public $sortDirection = 'asc';

    protected $queryString = ['search', 'filterStatus', 'filterBrand', 'filterLocation'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus() { $this->resetPage(); }
    public function updatedFilterBrand() { $this->resetPage(); }
    public function updatedFilterLocation() { $this->resetPage(); }

    public function toggleSelectAll($inventoryIds)
    {
        if (count($this->selectedItems) === count($inventoryIds)) {
            $this->selectedItems = [];
        } else {
            $this->selectedItems = $inventoryIds;
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = Product::query()
            ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('products.status', 'active')
            ->select(
                'products.id', // Giữ nguyên 'id' là ID sản phẩm để không hỏng loop Blade
                'inventories.id as inventory_id',
                \Illuminate\Support\Facades\DB::raw('COALESCE(inventories.quantity, 0) as quantity'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(inventories.reserved_quantity, 0) as reserved_quantity'),
                'inventories.warehouse_location',
                'products.name as product_name',
                'products.code as product_code',
                'products.unit',
                'products.brand',
                'products.min_stock',
                'products.batch_number',
                'products.expiry_date'
            );

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('products.name', 'like', "%{$this->search}%")
                  ->orWhere('products.code', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterBrand) {
            $query->where('products.brand', $this->filterBrand);
        }

        if ($this->filterLocation) {
            $query->where('inventories.warehouse_location', 'like', "%{$this->filterLocation}%");
        }

        if ($this->filterStatus === 'critical') {
            $query->whereRaw('(inventories.quantity - inventories.reserved_quantity) < products.min_stock');
        } elseif ($this->filterStatus === 'warning') {
            $query->whereRaw('(inventories.quantity - inventories.reserved_quantity) >= products.min_stock')
                  ->whereRaw('(inventories.quantity - inventories.reserved_quantity) < (products.min_stock * 1.5)');
        } elseif ($this->filterStatus === 'sufficient') {
            $query->whereRaw('(inventories.quantity - inventories.reserved_quantity) >= (products.min_stock * 1.5)');
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        $inventories = $query->paginate(20);

        return view('livewire.warehouse.inventory-list', [
            'inventories' => $inventories,
            'brands' => Product::whereNotNull('brand')->distinct()->pluck('brand'),
            'locations' => Inventory::whereNotNull('warehouse_location')->distinct()->pluck('warehouse_location'),
        ]);
    }
}
