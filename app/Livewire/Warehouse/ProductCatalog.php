<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use Illuminate\Validation\Rule;

class ProductCatalog extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $showModal = false;
    public $showImportModal = false;
    public $isEdit = false;
    public $productId;

    public $image;

    // Form fields
    public $code;
    public $name;
    public $brand;
    public $box_spec;
    public $carton_spec;
    public $status = 'active';
    public $location;
    public $batch_number;
    public $expiry_date;
    public $quantity;
    public $min_stock = 0;
    public $type = 'product_produced';
    public $selectedProducts = [];
    public $filterMode = 'all';

    public $excelFile;
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedIds = []; // Dùng đồng nhất selectedIds thay cho selectedProducts

    protected $queryString = ['search', 'dateFrom', 'dateTo'];

    public function rules()
    {
        return [
            'code' => ['required', Rule::unique('products', 'code')->ignore($this->productId)],
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'box_spec' => 'nullable|string|max:255',
            'carton_spec' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'location' => 'nullable|string|max:255',
            'batch_number' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'type' => 'required|in:product,product_produced,product_purchased',
            'image' => 'nullable|image|max:2048',
        ];
    }

    public function updatedFilterMode()
    {
        $this->resetPage();
    }

    public function selectExpiring()
    {
        $this->selectedProducts = Product::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addMonths(6))
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();
    }

    public function selectLowStock()
    {
        $this->selectedProducts = Product::whereHas('inventory', function($q) {
                $q->whereColumn('quantity', '<=', 'products.min_stock');
            })
            ->where('min_stock', '>', 0)
            ->pluck('id')
            ->map(fn($id) => (string)$id)
            ->toArray();
    }

    public function toggleSelectAll($idsOnPage)
    {
        if (count($this->selectedIds) >= count($idsOnPage)) {
            $this->selectedIds = [];
        } else {
            $this->selectedIds = collect($idsOnPage)->map(fn($id) => (string)$id)->toArray();
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['code', 'name', 'brand', 'box_spec', 'carton_spec', 'status', 'location', 'quantity', 'productId', 'image', 'type']);
        
        if ($id) {
            $this->isEdit = true;
            $this->productId = $id;
            $product = Product::findOrFail($id);
            $this->code = $product->code;
            $this->name = $product->name;
            $this->brand = $product->brand;
            $this->box_spec = $product->box_spec;
            $this->carton_spec = $product->carton_spec;
            $this->status = $product->status;
            $this->location = $product->location;
            $this->batch_number = $product->batch_number;
            $this->expiry_date = $product->expiry_date?->format('Y-m-d');
            $this->quantity = $product->inventory?->quantity ?? 0;
            $this->min_stock = $product->min_stock;
            $this->type = in_array($product->type, ['product_produced', 'product_purchased']) ? $product->type : 'product_produced';
        } else {
            $this->isEdit = false;
            $this->min_stock = 0;
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $imagePath = null;
        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
        }

        if ($this->isEdit) {
            $product = Product::find($this->productId);
            $product->update([
                'code' => $this->code,
                'name' => $this->name,
                'brand' => $this->brand,
                'box_spec' => $this->box_spec,
                'carton_spec' => $this->carton_spec,
                'status' => $this->status,
                'location' => $this->location,
                'batch_number' => $this->batch_number,
                'expiry_date' => $this->expiry_date ?: null,
                'min_stock' => $this->min_stock,
                'type' => $this->type,
            ]);
            
            if ($imagePath) {
                $product->update(['image' => $imagePath]);
            }
            
            // Sync location and quantity with inventory if exists
            if ($product->inventory) {
                $product->inventory->update([
                    'warehouse_location' => $this->location,
                    'quantity' => $this->quantity,
                ]);
            }

            session()->flash('message', 'Cập nhật sản phẩm thành công.');
        } else {
            $product = Product::create([
                'code' => $this->code,
                'name' => $this->name,
                'brand' => $this->brand,
                'box_spec' => $this->box_spec,
                'carton_spec' => $this->carton_spec,
                'status' => $this->status,
                'location' => $this->location,
                'batch_number' => $this->batch_number,
                'expiry_date' => $this->expiry_date ?: null,
                'min_stock' => $this->min_stock,
                'type' => $this->type,
                'image' => $imagePath,
            ]);

            // Create initial inventory record
            Inventory::create([
                'product_id' => $product->id,
                'quantity' => $this->quantity,
                'warehouse_location' => $this->location,
            ]);

            session()->flash('message', 'Thêm sản phẩm mới thành công.');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        session()->flash('message', 'Đã xoá sản phẩm.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedIds)) return;
        Product::whereIn('id', $this->selectedIds)->delete();
        session()->flash('message', 'Đã xóa ' . count($this->selectedIds) . ' sản phẩm.');
        $this->selectedIds = [];
    }

    public function exportExcel()
    {
        // Tôi sẽ tạo class ProductExport sau
        session()->flash('info', 'Tính năng Xuất Excel danh mục đang được chuẩn bị.');
    }

    public function printLabels()
    {
        if (empty($this->selectedIds)) return;
        // Logic in nhãn mã vạch/QR
        $this->dispatch('trigger-print');
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new ProductsImport, $this->excelFile);
            
            $this->reset(['excelFile', 'showImportModal']);
            session()->flash('message', 'Nhập dữ liệu từ Excel thành công!');
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $products = Product::query()
            ->with('inventory')
            ->where('code', 'like', 'SP%')
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('brand', 'like', '%' . $this->search . '%')
                  ->orWhere('batch_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->dateFrom, function($q) {
                $q->where('created_at', '>=', $this->dateFrom . ' 00:00:00');
            })
            ->when($this->dateTo, function($q) {
                $q->where('created_at', '<=', $this->dateTo . ' 23:59:59');
            })
            ->latest()
            ->paginate(15);

        return view('livewire.warehouse.product-catalog', [
            'products' => $products,
            'allProductIdsOnPage' => $products->pluck('id')->toArray()
        ]);
    }
}
