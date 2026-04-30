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
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class MaterialCatalog extends Component
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
    public $unit;
    public $status = 'active';
    public $location;
    public $batch_number;
    public $expiry_date;
    public $quantity;
    public $min_stock = 0;
    public $type = 'material';
    public $selectedProducts = [];
    public $filterMode = 'all';

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
            'unit' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'location' => 'nullable|string|max:255',
            'batch_number' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'type' => 'required|in:material',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,bmp|max:5120', // Tăng lên 5MB và hỗ trợ nhiều định dạng hơn
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

    public function toggleSelectAll($productIds)
    {
        if (count($this->selectedProducts) === count($productIds)) {
            $this->selectedProducts = [];
        } else {
            $this->selectedProducts = collect($productIds)->map(fn($id) => (string)$id)->toArray();
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['code', 'name', 'brand', 'box_spec', 'carton_spec', 'unit', 'status', 'location', 'quantity', 'productId', 'image', 'type']);
        
        if ($id) {
            $this->isEdit = true;
            $this->productId = $id;
            $product = Product::findOrFail($id);
            $this->code = $product->code;
            $this->name = $product->name;
            $this->brand = $product->brand;
            $this->box_spec = $product->box_spec;
            $this->carton_spec = $product->carton_spec;
            $this->unit = $product->unit;
            $this->status = $product->status;
            $this->location = $product->location;
            $this->batch_number = $product->batch_number;
            $this->expiry_date = $product->expiry_date?->format('Y-m-d');
            $this->quantity = $product->inventory?->quantity ?? 0;
            $this->min_stock = $product->min_stock;
            $this->type = $product->type ?? 'material';
        } else {
            $this->isEdit = false;
            $this->min_stock = 0;
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            $imagePath = null;
            if ($this->image && !is_string($this->image)) {
                // Nén ảnh bằng Intervention Image v3
                $manager = new ImageManager(new Driver());
                $name = time() . '_' . $this->image->getClientOriginalName();
                $tempPath = $this->image->getRealPath();
                
                // Đọc và nén
                $img = $manager->read($tempPath);
                $img->scale(width: 1000); // Giới hạn chiều rộng 1000px
                
                // Lưu vào public storage
                $savePath = 'products/' . $name;
                Storage::disk('public')->put($savePath, (string) $img->toWebp(75)); // Nén về định dạng WebP chất lượng 75%
                $imagePath = $savePath;
            }

            // Đảm bảo quantity là số
            $qty = (float)($this->quantity ?: 0);

            if ($this->isEdit) {
                $product = Product::findOrFail($this->productId);
                $product->update([
                    'code' => $this->code,
                    'name' => $this->name,
                    'brand' => $this->brand,
                    'box_spec' => $this->box_spec,
                    'carton_spec' => $this->carton_spec,
                    'unit' => $this->unit,
                    'status' => $this->status,
                    'location' => $this->location,
                    'batch_number' => $this->batch_number,
                    'expiry_date' => $this->expiry_date ?: null,
                    'min_stock' => (float)($this->min_stock ?: 0),
                    'type' => $this->type,
                ]);
                
                if ($imagePath) {
                    $product->update(['image' => $imagePath]);
                }
                
                // Đồng bộ với bảng Inventory
                $inventory = Inventory::where('product_id', $product->id)->first();
                if ($inventory) {
                    $inventory->update([
                        'quantity' => $qty,
                        'warehouse_location' => $this->location
                    ]);
                } else {
                    Inventory::create([
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'warehouse_location' => $this->location
                    ]);
                }

                session()->flash('message', 'Cập nhật nguyên vật liệu thành công.');
            } else {
                $product = Product::create([
                    'code' => $this->code,
                    'name' => $this->name,
                    'brand' => $this->brand,
                    'box_spec' => $this->box_spec,
                    'carton_spec' => $this->carton_spec,
                    'unit' => $this->unit,
                    'status' => $this->status,
                    'location' => $this->location,
                    'batch_number' => $this->batch_number,
                    'expiry_date' => $this->expiry_date ?: null,
                    'min_stock' => (float)($this->min_stock ?: 0),
                    'type' => $this->type,
                    'image' => $imagePath,
                ]);

                // Tạo luôn record bên Inventory
                Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'warehouse_location' => $this->location
                ]);

                session()->flash('message', 'Thêm nguyên vật liệu mới thành công.');
            }

            $this->reset(['image']); // Xoá ảnh tạm sau khi lưu
            $this->showModal = false;

        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            session()->flash('message', 'Đã xoá nguyên vật liệu thành công.');
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa nguyên vật liệu này vì đã có dữ liệu liên quan (phiếu nhập/xuất, tồn kho...).');
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedProducts)) return;

        try {
            $count = 0;
            foreach ($this->selectedProducts as $id) {
                $product = Product::find($id);
                if ($product) {
                    $product->delete();
                    $count++;
                }
            }
            
            if ($count > 0) {
                session()->flash('message', "Đã xóa thành công {$count} nguyên vật liệu.");
            }
            $this->selectedProducts = [];
        } catch (\Exception $e) {
            session()->flash('error', 'Một số nguyên vật liệu không thể xóa do có dữ liệu liên quan (phiếu nhập/xuất, tồn kho...).');
        }
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
            ->where('type', 'material')
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('brand', 'like', '%' . $this->search . '%')
                  ->orWhere('batch_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterMode === 'expiring', function($q) {
                $q->whereNotNull('expiry_date')
                  ->where('expiry_date', '<=', now()->addMonths(6));
            })
            ->when($this->filterMode === 'low_stock', function($q) {
                $q->whereHas('inventory', function($iq) {
                    $iq->whereColumn('quantity', '<=', 'products.min_stock');
                })->where('min_stock', '>', 0);
            })
            ->latest()
            ->paginate(15);

        return view('livewire.warehouse.material-catalog', [
            'products' => $products,
            'allProductIdsOnPage' => $products->pluck('id')->toArray()
        ]);
    }
}
