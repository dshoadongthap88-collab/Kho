<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class ProductCatalog extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
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
    public $quantity = 0;

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

    public function render()
    {
        $products = Product::query()
            ->with('inventory')
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('brand', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(15);

        return view('livewire.warehouse.product-catalog', [
            'products' => $products
        ]);
    }
}
