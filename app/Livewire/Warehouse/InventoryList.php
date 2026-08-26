<?php

namespace App\Livewire\Warehouse;

use App\Models\Inventory;
use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InventoryList extends Component
{
    use WithPagination, WithFileUploads;

    #[Url(history: true)]
    public $search = '';
    #[Url(history: true)]
    public $filterStatus = ''; // all, sufficient, warning, critical
    #[Url(history: true)]
    public $filterBrand = '';
    #[Url(history: true)]
    public $filterLocation = '';
    public $selectedItems = []; // Array of inventory IDs
    #[Url(history: true)]
    public $perPage = 25;   // so dong moi trang, nguoi dung tu chon
    // Vị trí gõ trực tiếp trên bảng: [product_id => vị trí]
    public $locations = [];
    // Tồn kho gõ trực tiếp trên bảng: [product_id => số lượng]
    public $quantities = [];
    public $sortField = 'products.name';
    public $sortDirection = 'asc';

    public $showEditModal = false;
    public $showImportModal = false;
    public $excelFile;
    
    public $editingInventoryId = null;
    public $editingProductId = null;
    public $editingProductName = '';
    public $editingProductCode = '';
    public $editingBrand = '';
    public $editingBatchNumber = '';
    public $editingExpiryDate = '';
    public $editingUnit = '';
    public $editingMinStock = 0;
    public $editingQuantity = 0;
    public $editingLocation = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus() { $this->resetPage(); }
    public function updatedFilterBrand() { $this->resetPage(); }
    public function updatedFilterLocation() { $this->resetPage(); }

    public function updatedPerPage()
    {
        // Chỉ cho vài mức có sẵn, tránh ai đó sửa URL thành perPage=100000
        $this->perPage = in_array((int) $this->perPage, [10, 25, 50, 100, 200], true)
            ? (int) $this->perPage
            : 25;
        $this->resetPage();
    }

    public function toggleSelectAll($inventoryIds)
    {
        // Chỉ thao tác trên các dòng của trang hiện tại, giữ nguyên lựa chọn ở
        // các trang khác — nhờ vậy có thể chọn dồn qua nhiều trang rồi in một lượt.
        $pageIds  = array_map('intval', $inventoryIds);
        $selected = array_map('intval', $this->selectedItems);

        if (count(array_intersect($pageIds, $selected)) === count($pageIds)) {
            $this->selectedItems = array_values(array_diff($selected, $pageIds));
        } else {
            $this->selectedItems = array_values(array_unique(array_merge($selected, $pageIds)));
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

    public function deleteSelected()
    {
        if (empty($this->selectedItems)) return;
        
        try {
            Inventory::whereIn('product_id', $this->selectedItems)->delete();
            $this->selectedItems = [];
            session()->flash('success', 'Đã xóa tồn kho thành công.');
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra khi xóa tồn kho.');
        }
    }

    /** Sửa một dòng cụ thể từ cột Thao tác, không cần tích chọn trước */
    public function editRow($productId)
    {
        $this->loadForEdit($productId);
    }

    /** Xóa tồn kho của đúng một dòng từ cột Thao tác */
    public function deleteRow($productId)
    {
        try {
            Inventory::where('product_id', $productId)->delete();

            // Bỏ dòng vừa xóa khỏi danh sách đang tích chọn, nếu có
            $this->selectedItems = array_values(
                array_diff(array_map('intval', $this->selectedItems), [(int) $productId])
            );

            // Bỏ luôn khỏi hai mảng ô sửa nhanh, nếu không giá trị cũ còn lại
            // sẽ dựng lại dòng tồn kho vừa xóa khi người dùng bấm LƯU LẠI.
            unset($this->locations[$productId], $this->quantities[$productId]);

            session()->flash('success', 'Đã xóa dữ liệu tồn kho của vật tư.');
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra khi xóa tồn kho.');
        }
    }

    public function openEditModal()
    {
        if (count($this->selectedItems) !== 1) {
            session()->flash('error', 'Vui lòng chọn duy nhất 1 sản phẩm để sửa.');
            return;
        }

        $this->loadForEdit($this->selectedItems[0]);
    }

    /** Nạp dữ liệu một vật tư vào modal sửa */
    private function loadForEdit($productId)
    {
        $product = Product::find($productId);
        if (!$product) return;

        $inventory = $this->inventoryForProduct($productId);
        if (!$inventory) {
            return;   // helper đã báo lỗi rõ cho người dùng
        }

        $this->editingProductId = $product->id;
        $this->editingProductName = $product->name;
        $this->editingProductCode = $product->code;
        $this->editingBrand = $product->brand;
        $this->editingBatchNumber = $product->batch_number;
        $this->editingExpiryDate = $product->expiry_date;
        $this->editingUnit = $product->unit;
        $this->editingMinStock = $product->min_stock;

        $this->editingInventoryId = $inventory->id;
        $this->editingQuantity = $inventory->quantity;
        $this->editingLocation = $inventory->warehouse_location;
        $this->showEditModal = true;
    }

    /**
     * Lưu cột Vị trí sửa trực tiếp trên bảng.
     *
     * Tồn kho là nơi nhập liệu chính, Danh mục vật tư chỉ phản chiếu lại — nên
     * ghi cả inventories.warehouse_location lẫn products.location. Nếu chỉ ghi
     * một bên thì mở Sửa hoặc In bên Danh mục sẽ thấy ô vị trí trống.
     */
    /**
     * Lưu hai cột sửa trực tiếp trên bảng: Vị trí và Tồn kho.
     *
     * Sửa tồn kho là đụng vào số liệu thật nên mỗi lần đổi đều ghi một dòng
     * inventory_transactions kiểu 'adjustment', kèm số cũ → số mới và người
     * sửa. Không có vết thì về sau không truy được vì sao tồn lệch.
     */
    public function saveLocations()
    {
        if (empty($this->locations) && empty($this->quantities)) {
            session()->flash('success', 'Không có thay đổi nào.');
            $this->dispatch('locations-saved');
            return;
        }

        foreach ($this->locations as $value) {
            if (mb_strlen(trim((string) $value)) > 255) {
                session()->flash('error', 'Vị trí không được dài quá 255 ký tự.');
                return;
            }
        }

        // Chặn số âm và chữ trước khi ghi, báo rõ dòng nào sai
        foreach ($this->quantities as $productId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (!is_numeric($value)) {
                session()->flash('error', 'Tồn kho phải là số. Kiểm tra lại ô vừa nhập.');
                return;
            }

            if ((float) $value < 0) {
                session()->flash('error', 'Tồn kho không được là số âm.');
                return;
            }
        }

        $updated   = 0;   // số vật tư đổi vị trí
        $updatedSl = 0;   // số vật tư đổi tồn kho

        DB::transaction(function () use (&$updated, &$updatedSl) {
            // Gom mọi vật tư có thay đổi ở một trong hai cột
            $productIds = array_unique(array_merge(
                array_keys($this->locations),
                array_keys($this->quantities)
            ));

            foreach ($productIds as $productId) {
                $coViTri = array_key_exists($productId, $this->locations);
                $coSl    = array_key_exists($productId, $this->quantities);

                $viTri = $coViTri ? trim((string) $this->locations[$productId]) : null;
                $viTri = ($viTri === '') ? null : $viTri;

                $sl = null;
                if ($coSl && $this->quantities[$productId] !== null && $this->quantities[$productId] !== '') {
                    $sl = (float) $this->quantities[$productId];
                }

                // Vật tư chưa có dòng tồn kho vẫn gõ được — dòng sẽ được tạo khi
                // lưu. Nếu cả hai ô đều trống thì không tạo gì.
                $canTao   = ($viTri !== null) || ($sl !== null && $sl > 0);
                $inventory = $this->inventoryForProduct($productId, $canTao);

                if (!$inventory) {
                    continue;
                }

                $thayDoi = [];

                if ($coViTri && $inventory->warehouse_location !== $viTri) {
                    $thayDoi['warehouse_location'] = $viTri;
                    $updated++;
                }

                if ($sl !== null && (float) $inventory->quantity !== $sl) {
                    $slCu = (float) $inventory->quantity;
                    $thayDoi['quantity'] = $sl;
                    $updatedSl++;

                    // Ghi vết điều chỉnh: số cũ → số mới, ai sửa, lúc nào
                    DB::table('inventory_transactions')->insert([
                        'product_id'         => $productId,
                        'type'               => 'adjustment',
                        'quantity'           => $sl - $slCu,
                        'transaction_date'   => now(),
                        'warehouse_location' => $thayDoi['warehouse_location'] ?? $inventory->warehouse_location,
                        'reference_type'     => 'manual_adjustment',
                        'note'               => sprintf('Sửa nhanh tồn kho trên bảng: %s → %s',
                            rtrim(rtrim(number_format($slCu, 2, '.', ''), '0'), '.'),
                            rtrim(rtrim(number_format($sl, 2, '.', ''), '0'), '.')),
                        'created_by'         => auth()->id(),
                        'house_id'           => session('current_house') ?? (auth()->user()?->current_house_id),
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                }

                if (empty($thayDoi)) {
                    continue;   // không đổi thì không chạm CSDL
                }

                $inventory->update($thayDoi);

                // Đồng bộ vị trí sang danh mục vật tư
                if (array_key_exists('warehouse_location', $thayDoi)) {
                    Product::where('id', $productId)->update(['location' => $viTri]);
                }
            }
        });

        // Bỏ cache gợi ý vị trí để danh sách ở ô lọc có ngay giá trị mới
        Cache::forget('inventory_locations_' . (session('current_house') ?? 0));

        $phan = [];
        if ($updated > 0) {
            $phan[] = "vị trí cho {$updated} vật tư (đã đồng bộ sang Danh mục vật tư)";
        }
        if ($updatedSl > 0) {
            $phan[] = "tồn kho cho {$updatedSl} vật tư";
        }

        session()->flash('success', $phan
            ? 'Đã lưu ' . implode(' và ', $phan) . '.'
            : 'Không có thay đổi nào.');

        $this->dispatch('locations-saved');
    }

    /**
     * Lấy dòng tồn kho của vật tư trong dự án đang đứng, tạo mới nếu chưa có.
     *
     * Ràng buộc đúng của bảng inventories là (house_id, product_id). Nhưng CSDL
     * production còn sót index unique chỉ trên product_id từ thời một kho, nên
     * dự án thứ hai tạo dòng cho cùng vật tư sẽ dính lỗi 1062 Duplicate entry.
     * Migration 2026_08_23_230000 gỡ index đó; ở đây bắt thêm ngoại lệ để nếu
     * server chưa kịp chạy migration thì báo rõ thay vì nổ ra trang lỗi 500.
     */
    private function inventoryForProduct($productId, bool $createIfMissing = true): ?Inventory
    {
        if ($inventory = Inventory::where('product_id', $productId)->first()) {
            return $inventory;
        }

        // Bản ghi cũ chưa gắn house_id thì nhận về dự án hiện tại
        $houseId = session('current_house') ?? (auth()->user()?->current_house_id);
        if ($houseId) {
            $legacy = Inventory::withoutGlobalScope('house')
                ->where('product_id', $productId)
                ->whereNull('house_id')
                ->first();

            if ($legacy) {
                $legacy->house_id = $houseId;
                $legacy->save();
                return $legacy;
            }
        }

        if (!$createIfMissing) {
            return null;
        }

        try {
            return Inventory::create([
                'product_id'         => $productId,
                'quantity'           => 0,
                'warehouse_location' => null,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            session()->flash('error',
                'Cơ sở dữ liệu còn ràng buộc cũ (inventories_product_id_unique) nên vật tư này '
                . 'chưa thể có tồn kho riêng ở dự án hiện tại. Vui lòng chạy: php artisan migrate');
            return null;
        }
    }

    public function saveEdit()
    {
        $this->validate([
            'editingProductName' => 'required|string|max:255',
            'editingProductCode' => 'required|string|max:100',
            'editingBrand' => 'nullable|string|max:255',
            'editingBatchNumber' => 'nullable|string|max:100',
            'editingExpiryDate' => 'nullable|date',
            'editingUnit' => 'required|string|max:50',
            'editingMinStock' => 'required|numeric|min:0',
            'editingQuantity' => 'required|numeric|min:0',
            'editingLocation' => 'nullable|string|max:255',
        ]);

        if ($this->editingProductId) {
            $product = Product::find($this->editingProductId);
            if ($product) {
                $product->update([
                    'name' => $this->editingProductName,
                    'code' => $this->editingProductCode,
                    'brand' => $this->editingBrand,
                    'batch_number' => $this->editingBatchNumber,
                    'expiry_date' => $this->editingExpiryDate ?: null,
                    'unit' => $this->editingUnit,
                    'min_stock' => $this->editingMinStock,
                    // Ghi luon vi tri sang danh muc vat tu. Man Danh muc doc
                    // products.location o modal Sua va o ban in, nen neu chi ghi
                    // inventories.warehouse_location thi sua vi tri ben Ton kho
                    // xong qua Danh muc se thay o vi tri trong.
                    'location' => $this->editingLocation ?: null,
                ]);
            }
        }

        if ($this->editingInventoryId) {
            $inventory = Inventory::find($this->editingInventoryId);
            if ($inventory) {
                $inventory->update([
                    'quantity' => $this->editingQuantity,
                    'warehouse_location' => $this->editingLocation,
                ]);
            }
        }

        // Nạp lại dữ liệu bảng sau khi sửa.
        //
        // Bắt buộc, không phải cho đẹp: hai mảng ô sửa nhanh trên bảng vẫn đang
        // giữ giá trị CŨ của dòng vừa sửa. render() chỉ nạp giá trị mới cho
        // khoá chưa có, nên giá trị cũ ở lại — bấm LƯU LẠI sau đó sẽ ghi đè
        // ngược, xoá mất thứ vừa lưu trong modal. Đúng triệu chứng "sửa vị trí
        // trong modal xong không thấy lưu lại".
        //
        // Bỏ khoá của dòng vừa sửa để render() lấy lại số mới từ CSDL.
        unset(
            $this->locations[$this->editingProductId],
            $this->quantities[$this->editingProductId]
        );

        // Vị trí mới cần xuất hiện ngay ở ô gợi ý và ô lọc
        Cache::forget('inventory_locations_' . (session('current_house') ?? 0));

        session()->flash('success', 'Đã cập nhật thông tin sản phẩm và tồn kho thành công.');
        $this->showEditModal = false;
        $this->selectedItems = [];
    }

    public function exportExcel()
    {
        $houseId = session('current_house') ?? (auth()->user()?->current_house_id);
        $query = Product::query()
            ->leftJoin('inventories', function($join) use ($houseId) {
                $join->on('products.id', '=', 'inventories.product_id');
                if ($houseId) {
                    $join->where('inventories.house_id', $houseId);
                }
            })
            ->where('products.status', 'active')
            ->select(
                'products.id',
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
        $data = $query->get();

        return Excel::download(new \App\Exports\InventoryExport($data), 'Danh_sach_ton_kho_' . date('Ymd_His') . '.xlsx');
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        // File nhiều nghìn dòng dễ chạm giới hạn thời gian và bộ nhớ mặc định.
        // Nếu hosting khoá ini_set thì hai lệnh này im lặng bỏ qua, không lỗi.
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        try {
            $import = new \App\Imports\InventoryImport;
            Excel::import($import, $this->excelFile);

            $this->reset(['excelFile', 'showImportModal']);
            $this->selectedItems = [];

            // Báo rõ từng con số để không còn cảnh "file 1500 dòng mà chỉ vào 750"
            // mà không biết vì sao.
            $message = sprintf(
                'Đã đọc %d dòng: tạo mới %d vật tư, cập nhật %d vật tư.',
                $import->rowsRead,
                $import->created,
                $import->updated
            );

            if ($import->duplicateRows > 0) {
                $message .= sprintf(
                    ' Có %d dòng trùng mã vật tư — dòng dưới ghi đè dòng trên (tồn kho là ghi đè, không cộng dồn).',
                    $import->duplicateRows
                );
            }

            if ($import->skippedNoCode > 0) {
                $message .= sprintf(' Bỏ qua %d dòng thiếu mã vật tư.', $import->skippedNoCode);
            }

            // Cho người dùng thấy hệ thống đã hiểu cột nào là cột nào, để phát hiện
            // ngay nếu file lạ bị đọc sai cột.
            if (!empty($import->detectedColumns)) {
                $pairs = [];
                foreach ($import->detectedColumns as $field => $columnName) {
                    $pairs[] = $field . ' ← "' . $columnName . '"';
                }
                $message .= ' | Cột đã nhận diện: ' . implode(', ', $pairs);
            }

            session()->flash('success', $message);
        } catch (\Throwable $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $houseId = session('current_house') ?? (auth()->user()?->current_house_id);
        $query = Product::query()
            ->leftJoin('inventories', function($join) use ($houseId) {
                $join->on('products.id', '=', 'inventories.product_id');
                if ($houseId) {
                    $join->where('inventories.house_id', $houseId);
                }
            })
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

        $perPage = in_array((int) $this->perPage, [10, 25, 50, 100, 200], true)
            ? (int) $this->perPage
            : 25;

        $inventories = $query->paginate($perPage);

        // Nạp vị trí cho các dòng đang hiển thị.
        //
        // Chỉ giữ đúng các dòng của trang hiện tại. Bản cũ cộng dồn mãi: mỗi lần
        // gõ tìm kiếm lại thêm khoá mới mà không bỏ khoá cũ, nên mảng phình theo
        // số dòng đã từng xem và đi kèm mọi lần gõ phím — càng gõ càng chậm.
        //
        // Giá trị người dùng đang gõ dở vẫn được giữ: ô nhập dùng wire:model
        // (không .live) nên chỉ gửi lên khi bấm LƯU LẠI, và khoá của dòng đang
        // hiển thị luôn nằm trong danh sách giữ lại bên dưới.
        $moi    = [];
        $moiSl  = [];

        foreach ($inventories as $row) {
            $moi[$row->id] = array_key_exists($row->id, $this->locations)
                ? $this->locations[$row->id]
                : $row->warehouse_location;

            $moiSl[$row->id] = array_key_exists($row->id, $this->quantities)
                ? $this->quantities[$row->id]
                : (float) $row->quantity;
        }

        $this->locations  = $moi;
        $this->quantities = $moiSl;

        $houseKey = session('current_house') ?? 0;

        return view('livewire.warehouse.inventory-list', [
            'inventories' => $inventories,
            // Cache 5 phút — không cần query DB mỗi lần render
            'brands' => \Illuminate\Support\Facades\Cache::remember(
                "inventory_brands_{$houseKey}", 300,
                fn() => Product::whereNotNull('brand')->distinct()->pluck('brand')
            ),
            // Tên phải KHÁC property $locations (mảng ô sửa vị trí trên bảng).
            // Trùng tên thì biến view che mất property, làm datalist gợi ý vị trí
            // nhận nhầm mảng [product_id => vị trí] và ô gợi ý rỗng.
            'locationOptions' => \Illuminate\Support\Facades\Cache::remember(
                "inventory_locations_{$houseKey}", 300,
                fn() => \App\Models\Inventory::whereNotNull('warehouse_location')
                    ->where('warehouse_location', '!=', '')
                    ->distinct()->pluck('warehouse_location')
            ),
        ]);
    }
}
