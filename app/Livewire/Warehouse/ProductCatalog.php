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

    public $activeTab = 'materials';

    public $search = '';
    public $showModal = false;
    public $showImportModal = false;
    public $isEdit = false;
    public $productId;
    public $confirmDuplicate = false;

    public $image;

    // Form fields
    public $code;
    public $name;
    public $brand;
    public $description;
    public $status = 'active';
    public $location;
    public $category_id;
    public $batch_number;
    public $expiry_date;
    public $quantity;
    public $min_stock = 0;
    public $type = 'product_purchased';
    
    // Form fields (Asset)
    public $equipment_code;
    public $asset_code;
    public $machine_type;
    public $manager;
    public $warranty_status = 'Còn bảo hành';

    public $filterMode = 'all';

    public $excelFile;
    
    public $selectedIds = []; 
    public $printItems = []; // Thêm mảng chứa dữ liệu để in
    public $minStocks = [];
    public $maxStocks = []; 

    protected $queryString = ['search', 'filterMode', 'activeTab'];

    public function rules()
    {
        if ($this->activeTab === 'materials') {
            return [
                'code' => ['required', Rule::unique('products', 'code')->ignore($this->productId)],
                'name' => 'required|string|max:255',
                'brand' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
                'category_id' => 'nullable|exists:categories,id',
                'location' => 'nullable|string|max:255',
                'batch_number' => 'nullable|string|max:255',
                'expiry_date' => 'nullable|date',
                'quantity' => 'nullable|numeric|min:0',
                'min_stock' => 'nullable|numeric|min:0',
                'type' => 'required|in:product,product_produced,product_purchased',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,bmp|max:5120',
            ];
        } else {
            return [
                'equipment_code' => ['required', Rule::unique('assets', 'equipment_code')->ignore($this->productId)],
                'asset_code' => ['nullable', Rule::unique('assets', 'asset_code')->ignore($this->productId)],
                'name' => 'required|string|max:255',
                'machine_type' => 'nullable|string|max:255',
                'manager' => 'nullable|string|max:255',
                'warranty_status' => 'nullable|string|max:255',
            ];
        }
    }

    public function messages()
    {
        return [
            'code.unique' => 'Mã vật tư này đã tồn tại trong hệ thống.',
            'equipment_code.unique' => 'Mã thiết bị này đã tồn tại trong hệ thống.',
            'asset_code.unique' => 'Mã tài sản này đã tồn tại trong hệ thống.',
        ];
    }


    public function setFilterMode($mode)
    {
        \Log::info('Filter mode triggered: ' . $mode);
        $this->filterMode = $mode;
        $this->resetPage();
        session()->flash('message', 'Đã chuyển sang chế độ: ' . ($mode == 'all' ? 'Tất cả' : 'Sắp hết tồn'));
    }

    private function applyLowStockFilter($query)
    {
        return $query->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
                     ->where(function($sq) {
                         $sq->whereRaw('COALESCE(inventories.quantity, 0) <= products.min_stock');
                     })
                     ->select('products.*');
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
        $this->confirmDuplicate = false;
        $this->reset(['code', 'name', 'brand', 'description', 'status', 'location', 'quantity', 'productId', 'image', 'type', 'category_id', 'equipment_code', 'asset_code', 'machine_type', 'manager', 'warranty_status']);
        
        $this->warranty_status = 'Còn bảo hành';
        if ($this->activeTab === 'materials') {
            if ($id) {
                $this->isEdit = true;
                $this->productId = $id;
                $product = Product::findOrFail($id);
                $this->code = $product->code;
                $this->name = $product->name;
                $this->brand = $product->brand;
                $this->description = $product->description;
                $this->status = $product->status;
                $this->location = $product->location;
                $this->category_id = $product->category_id;
                $this->batch_number = $product->batch_number;
                $this->expiry_date = $product->expiry_date?->format('Y-m-d');
                $this->quantity = $product->inventory?->quantity ?? 0;
                $this->min_stock = $product->min_stock;
                $this->type = in_array($product->type, ['product_produced', 'product_purchased']) ? $product->type : 'product_purchased';
            } else {
                $this->isEdit = false;
                $this->min_stock = 0;
            }
        } else {
            // tab equipments
            if ($id) {
                $this->isEdit = true;
                $this->productId = $id;
                $asset = \App\Models\Asset::findOrFail($id);
                $this->asset_code = $asset->asset_code;
                $this->equipment_code = $asset->equipment_code;
                $this->name = $asset->name;
                $this->machine_type = $asset->machine_type;
                $this->manager = $asset->manager;
                $this->warranty_status = $asset->warranty_status ?: 'Còn bảo hành';
            } else {
                $this->isEdit = false;
            }
        }

        $this->showModal = true;
    }

    public function confirmSave()
    {
        $this->confirmDuplicate = true;
        $this->save();
    }

    public function save()
    {
        // Loại bỏ khoảng trắng thừa (data rác)
        $this->code = trim($this->code ?? '');
        $this->name = preg_replace('/\s+/', ' ', trim($this->name ?? '')); // Xóa khoảng trắng giữa các từ nếu có quá nhiều
        $this->brand = trim($this->brand ?? '');
        $this->description = trim($this->description ?? '');
        $this->location = trim($this->location ?? '');
        $this->batch_number = trim($this->batch_number ?? '');
        
        $this->equipment_code = trim($this->equipment_code ?? '');
        $this->asset_code = trim($this->asset_code ?? '');
        $this->machine_type = trim($this->machine_type ?? '');
        $this->manager = trim($this->manager ?? '');

        $this->validate();

        if (!$this->isEdit && !$this->confirmDuplicate) {
            $exists = false;
            if ($this->activeTab === 'materials') {
                $exists = \App\Models\Product::where('name', $this->name)->exists();
            } else {
                $exists = \App\Models\Asset::where('name', $this->name)->exists();
            }

            if ($exists) {
                $this->dispatch('confirm-duplicate');
                return;
            }
        }

        try {
            if ($this->activeTab === 'equipments') {
                if ($this->isEdit) {
                    $asset = \App\Models\Asset::findOrFail($this->productId);
                    $asset->update([
                        'equipment_code' => $this->equipment_code,
                        'asset_code' => $this->asset_code,
                        'name' => $this->name,
                        'machine_type' => $this->machine_type,
                        'manager' => $this->manager,
                        'warranty_status' => $this->warranty_status,
                    ]);
                    session()->flash('message', 'Cập nhật thiết bị thành công.');
                } else {
                    \App\Models\Asset::create([
                        'equipment_code' => $this->equipment_code,
                        'asset_code' => $this->asset_code,
                        'name' => $this->name,
                        'machine_type' => $this->machine_type,
                        'manager' => $this->manager,
                        'warranty_status' => $this->warranty_status,
                        'department' => 'KHO',
                        'model' => 'N/A'
                    ]);
                    session()->flash('message', 'Thêm thiết bị mới thành công.');
                }
                $this->showModal = false;
                $this->dispatch('item-saved');
                return;
            }

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
                    'description' => $this->description,
                    'status' => $this->status,
                    'category_id' => $this->category_id ?: null,
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

                // Cập nhật lại mảng minStocks hiển thị trên bảng
                $this->minStocks[$product->id] = $this->min_stock > 0 ? $this->min_stock : '';

                session()->flash('message', 'Cập nhật vật tư thành công.');
            } else {
                $product = Product::create([
                    'code' => $this->code,
                    'name' => $this->name,
                    'brand' => $this->brand,
                    'description' => $this->description,
                    'status' => $this->status,
                    'category_id' => $this->category_id ?: null,
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

            $this->reset(['image', 'confirmDuplicate']); // Xoá ảnh tạm sau khi lưu
            $this->showModal = false;
            $this->dispatch('item-saved');
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
                if ($this->activeTab === 'materials') {
                    $item = Product::find($id);
                } else {
                    $item = \App\Models\Asset::find($id);
                }
                
                if ($item) {
                    $item->delete();
                    $count++;
                }
            }
            
            if ($count > 0) {
                $type = $this->activeTab === 'materials' ? 'vật tư' : 'thiết bị';
                session()->flash('message', "Đã xóa thành công {$count} {$type}.");
            }
            $this->selectedIds = [];
        } catch (\Exception $e) {
            session()->flash('error', 'Một số mục không thể xóa do có dữ liệu liên quan.');
        }
    }

    public function deleteEquipment($id)
    {
        try {
            $asset = \App\Models\Asset::findOrFail($id);
            $asset->delete();
            session()->flash('message', 'Đã xoá thiết bị thành công.');
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa thiết bị này vì đã có dữ liệu liên quan.');
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

    public function printAll()
    {
        if ($this->activeTab === 'materials') {
            $this->printItems = Product::query()
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
                
                
                ->when($this->filterMode === 'low_stock', function($q) {
                    return $this->applyLowStockFilter($q);
                })
                ->orderBy('products.created_at', 'desc')
                ->get();
        } else {
            $this->printItems = \App\Models\Asset::query()
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('asset_code', 'like', '%' . $this->search . '%')
                      ->orWhere('equipment_code', 'like', '%' . $this->search . '%')
                      ->orWhere('manager', 'like', '%' . $this->search . '%');
                })
                
                
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $this->dispatch('trigger-print');
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            if ($this->activeTab === 'materials') {
                Excel::import(new ProductsImport, $this->excelFile);
            } else {
                Excel::import(new \App\Imports\AssetsImport, $this->excelFile);
            }
            
            $this->reset(['excelFile', 'showImportModal']);
            session()->flash('message', 'Nhập dữ liệu từ Excel thành công!');
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    
    public function saveMaxStocks()
    {
        try {
            $count = 0;
            $warnings = [];

            foreach ($this->maxStocks as $productId => $value) {
                $product = \App\Models\Product::with('inventory')->find($productId);
                if ($product) {
                    $newVal = (float)($value ?: 0);
                    $currentQty = (float)($product->inventory?->quantity ?? 0);

                    if ($newVal > 0 && $newVal < $currentQty) {
                        $warnings[] = "{$product->code} (tồn: {$currentQty}, max: {$newVal})";
                    }

                    if ($product->max_stock != $newVal) {
                        $product->update(['max_stock' => $newVal]);
                        $count++;
                    }
                }
            }

            if (count($warnings) > 0) {
                session()->flash('warning', 'Đã lưu ' . $count . ' vật tư. CẢNH BÁO: ' . count($warnings) . ' mặt hàng đang vượt mức tồn tối đa: ' . implode(', ', $warnings));
            } else {
                session()->flash('message', "Đã lưu thành công định mức tồn tối đa cho {$count} vật tư!");
            }
            $this->dispatch('max-stocks-saved');
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
                    $currentQty = (float)($product->inventory?->quantity ?? 0);

                    // Cảnh báo: min_stock > số lượng tồn hiện tại
                    if ($newVal > $currentQty) {
                        $warnings[] = "{$product->code} (tồn: {$currentQty}, min: {$newVal})";
                    }

                    if ($product->min_stock != $newVal) {
                        $product->update(['min_stock' => $newVal]);
                        $count++;
                    }
                }
            }

            if (count($warnings) > 0) {
                session()->flash('warning', 'Đã lưu ' . $count . ' vật tư. CẢNH BÁO: ' . count($warnings) . ' mặt hàng đang dưới mức tồn tối thiểu: ' . implode(', ', $warnings));
            } else {
                session()->flash('message', "Đã lưu thành công định mức tồn tối thiểu cho {$count} vật tư!");
            }
            $this->dispatch('min-stocks-saved');
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }


    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $products = collect();
        $equipments = collect();
        
        if ($this->activeTab === 'materials') {
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
                
                
                ->when($this->filterMode === 'low_stock', function($q) {
                    return $this->applyLowStockFilter($q);
                })
                ->orderBy('products.created_at', 'desc')
                ->paginate(8);

            // Khởi tạo các giá trị tồn tối thiểu cho ô nhập liệu trên trang hiện tại
            foreach ($products as $product) {
                if (!isset($this->minStocks[$product->id])) {
                    $this->minStocks[$product->id] = $product->min_stock > 0 ? $product->min_stock : '';
                }
                if (!isset($this->maxStocks[$product->id])) {
                    $this->maxStocks[$product->id] = $product->max_stock > 0 ? $product->max_stock : '';
                }
            }
        } else {
            // Tab equipments
            $equipments = \App\Models\Asset::query()
                ->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('asset_code', 'like', '%' . $this->search . '%')
                      ->orWhere('equipment_code', 'like', '%' . $this->search . '%')
                      ->orWhere('manager', 'like', '%' . $this->search . '%');
                })
                
                
                ->orderBy('created_at', 'desc')
                ->paginate(8);
        }

        $categories = \App\Models\Category::where('status', 'active')->orderBy('name')->get();

        return view('livewire.warehouse.product-catalog', [
            'products' => $products,
            'equipments' => $equipments,
            'categories' => $categories,
            'allProductIdsOnPage' => $this->activeTab === 'materials' ? $products->pluck('id')->toArray() : $equipments->pluck('id')->toArray()
        ]);
    }
}
