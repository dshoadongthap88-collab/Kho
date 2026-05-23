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
    public $filterMode = 'all';

    public $excelFile;
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedIds = []; 
    public $printItems = []; // Thêm mảng chứa dữ liệu để in
    public $minStocks = []; 

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
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'quantity' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'type' => 'required|in:product,product_produced,product_purchased',
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
                    'status' => $this->status,
                    'location' => $this->location,
                    'batch_number' => $this->batch_number ?: '',
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

                session()->flash('message', 'Cập nhật vật tư thành công.');
            } else {
                $product = Product::create([
                    'code' => $this->code,
                    'name' => $this->name,
                    'brand' => $this->brand,
                    'box_spec' => $this->box_spec,
                    'carton_spec' => $this->carton_spec,
                    'status' => $this->status,
                    'location' => $this->location,
                    'batch_number' => $this->batch_number ?: '',
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

                session()->flash('message', 'Thêm vật tư mới thành công.');
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
            session()->flash('message', 'Đã xoá vật tư thành công.');
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa vật tư này vì đã có dữ liệu liên quan (phiếu nhập/xuất, định mức BOM...).');
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedIds)) return;

        try {
            $count = 0;
            foreach ($this->selectedIds as $id) {
                $product = Product::find($id);
                if ($product) {
                    $product->delete();
                    $count++;
                }
            }
            
            if ($count > 0) {
                session()->flash('message', "Đã xóa thành công {$count} vật tư.");
            }
            $this->selectedIds = [];
        } catch (\Exception $e) {
            session()->flash('error', 'Một số vật tư không thể xóa do có dữ liệu liên quan (như phiếu nhập, phiếu xuất hoặc định mức BOM).');
        }
    }

    public function exportExcel()
    {
        // Tôi sẽ tạo class ProductExport sau
        session()->flash('info', 'Tính năng Xuất Excel danh mục đang được chuẩn bị.');
    }

    public function printLabels()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một vật tư để in.');
            return;
        }

        $this->printItems = Product::whereIn('id', $this->selectedIds)
            ->with('inventory')
            ->get();

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

    public function saveMinStocks()
    {
        try {
            $count = 0;
            $warnings = [];

            foreach ($this->minStocks as $productId => $value) {
                $product = Product::with('inventory')->find($productId);
                if ($product) {
                    $newVal = (float)($value ?: 0);
                    $currentQty = $product->inventory?->quantity ?? 0;

                    // Kiểm tra cảnh báo: min_stock > số lượng tồn hiện tại
                    if ($newVal > $currentQty && $newVal > 0) {
                        $warnings[] = $product->code . ' - ' . $product->name . ' (min: ' . $newVal . ', tồn: ' . $currentQty . ')';
                    }

                    if ($product->min_stock != $newVal) {
                        $product->update(['min_stock' => $newVal]);
                        $count++;
                    }
                }
            }

            if (count($warnings) > 0) {
                session()->flash('warning', 'Đã lưu thành công định mức tồn tối thiểu cho ' . $count . ' vật tư. Cảnh báo: Tồn tối thiểu lớn hơn số lượng tồn hiện tại cho ' . count($warnings) . ' vật tư: ' . implode(', ', $warnings));
            } else {
                session()->flash('message', "Đã lưu thành công định mức tồn tối thiểu cho {$count} vật tư!");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $products = Product::query()
            ->with(['inventory'])
            ->where(function($q) {
                $q->where('type', '!=', 'material')
                  ->orWhereNull('type');
            })
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
            // Lọc theo filterMode
            ->when($this->filterMode === 'low_stock', function($q) {
                $q->whereHas('inventory', function($iq) {
                    $iq->whereColumn('quantity', '<=', 'products.min_stock');
                })
                ->where('min_stock', '>', 0);
            })
            ->latest()
            ->paginate(8);

        // Khởi tạo các giá trị tồn tối thiểu cho ô nhập liệu trên trang hiện tại
        foreach ($products as $product) {
            if (!isset($this->minStocks[$product->id])) {
                $this->minStocks[$product->id] = $product->min_stock > 0 ? $product->min_stock : '';
            }
        }

        return view('livewire.warehouse.product-catalog', [
            'products' => $products,
            'allProductIdsOnPage' => $products->pluck('id')->toArray()
        ]);
    }
}
