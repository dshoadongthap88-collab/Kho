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
    public $quantity = 0;

    public $excelFile;

    protected $queryString = ['search'];

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
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['code', 'name', 'brand', 'box_spec', 'carton_spec', 'status', 'location', 'quantity', 'productId']);
        
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
        } else {
            $this->isEdit = false;
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

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
            ]);
            
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
                'type' => 'product',
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
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('brand', 'like', '%' . $this->search . '%')
                  ->orWhere('batch_number', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(15);

        return view('livewire.warehouse.product-catalog', [
            'products' => $products
        ]);
    }
}
