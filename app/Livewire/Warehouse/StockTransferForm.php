<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class StockTransferForm extends Component
{
    public $to_house = 2; // Default to house 2
    public $note = '';
    public $items = [];
    public $available_houses = [1, 2, 3, 4];
    public $searchQuery = ''; // Search query for autocomplete
    public $searchResults = []; // Search results for autocomplete
    public $activeIndex = null; // Currently active input index

    public function mount()
    {
        $current = session('current_house', 1);
        $this->available_houses = array_filter($this->available_houses, fn($h) => $h != $current);
        if (!empty($this->available_houses)) {
            $this->to_house = reset($this->available_houses);
        }
        
        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_code' => '',
            'quantity' => 1
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function searchProduct($index, $value)
    {
        $this->activeIndex = $index;
        if (empty($value)) {
            $this->searchResults = [];
            return;
        }

        $products = Product::where('code', 'like', "%{$value}%")
            ->orWhere('name', 'like', "%{$value}%")
            ->limit(10)
            ->get(['code', 'name', 'unit']);

        $this->searchResults = $products->map(function ($p) {
            return [
                'code' => $p->code,
                'label' => "{$p->code} - {$p->name}",
                'unit' => $p->unit,
            ];
        })->toArray();
    }

    public function selectProduct($index, $productCode)
    {
        $this->items[$index]['product_code'] = $productCode;
        $this->searchResults = [];
        $this->activeIndex = null;
    }

    public function clearSearch()
    {
        $this->searchResults = [];
        $this->activeIndex = null;
    }

    public function save()
    {
        $this->validate([
            'to_house' => 'required|in:1,2,3,4',
            'items' => 'required|array|min:1',
            'items.*.product_code' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $currentHouse = session('current_house', 1);
        if ($currentHouse == $this->to_house) {
            session()->flash('error', 'Không thể chuyển kho cho chính nhà hiện tại.');
            return;
        }

        try {
            DB::beginTransaction();

            // 1. Tạo phiếu chuyển kho ở nhà hiện tại
            $transfer = StockTransfer::create([
                'transfer_code' => 'TF-' . date('Ymd') . '-' . rand(1000, 9999),
                'transfer_date' => now(),
                'from_house' => $currentHouse,
                'to_house' => $this->to_house,
                'status' => 'completed',
                'note' => $this->note,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $itemData) {
                $productCode = $itemData['product_code'];
                if (str_contains($productCode, ' - ')) {
                    $productCode = trim(explode(' - ', $productCode)[0]);
                }

                $product = Product::where('code', $productCode)->first();
                if (!$product) {
                    throw new \Exception("Mã sản phẩm/vật tư {$productCode} không tồn tại trong nhà hiện tại.");
                }

                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $product->id],
                    ['quantity' => 0]
                );

                if ($inventory->quantity < $itemData['quantity']) {
                    throw new \Exception("Sản phẩm {$product->name} ({$product->code}) không đủ tồn kho (Hiện có: {$inventory->quantity}).");
                }

                $inventory->decrement('quantity', $itemData['quantity']);

                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'type' => 'transfer_out',
                    'quantity' => -$itemData['quantity'],
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'note' => "Chuyển sang nhà số {$this->to_house}",
                    'created_by' => auth()->id(),
                ]);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_code' => $product->code,
                    'quantity' => $itemData['quantity'],
                ]);
            }

            DB::commit();

            // 2. Chuyển kết nối sang nhà đích để cộng tồn kho
            $this->processTargetHouse($transfer, $this->to_house);

            // 3. Gửi thông báo chat (bọc trong try-catch riêng để không treo app)
            try {
                $senderName = auth()->user()->name ?? 'Thủ kho';
                $senderId = auth()->id();
                $fromHouseName = $currentHouse == 1 ? 'Hóc Môn' : ($currentHouse == 2 ? 'Hậu Nghĩa' : ($currentHouse == 3 ? 'Cần Giờ' : 'Số 4'));
                $toHouseName = $this->to_house == 1 ? 'Hóc Môn' : ($this->to_house == 2 ? 'Hậu Nghĩa' : ($this->to_house == 3 ? 'Cần Giờ' : 'Số 4'));

                $itemDetails = [];
                foreach ($this->items as $itemData) {
                    $pCode = str_contains($itemData['product_code'], ' - ') ? trim(explode(' - ', $itemData['product_code'])[0]) : $itemData['product_code'];
                    $p = Product::where('code', $pCode)->first();
                    $itemDetails[] = "{$itemData['quantity']}x " . ($p ? $p->name : $pCode);
                }

                $systemMsg = "🚚 [ĐIỀU CHUYỂN KHO] Đã chuyển thành công từ kho {$fromHouseName} sang kho {$toHouseName}: " . implode(', ', $itemDetails) . ". Ghi chú: " . ($this->note ?: 'Không có');
                StockTransferList::broadcastMessage($senderName, $systemMsg, 'system', $senderId);
            } catch (\Exception $ex) {
                // Log error but don't fail the request
            }

            session()->flash('success', 'Chuyển kho thành công!');
            return redirect()->route('warehouse.stock-transfer.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    private function processTargetHouse($transfer, $targetHouse)
    {
        // Lưu trữ connection cũ
        $oldDb = Config::get('database.connections.tenant.database');
        
        // Cấu hình connection mới
        $newDb = $targetHouse == 1 ? 'laravel' : 'laravel_' . $targetHouse;
        Config::set('database.connections.tenant.database', $newDb);
        DB::purge('tenant');

        try {
            DB::beginTransaction();

            foreach ($this->items as $itemData) {
                $productCode = $itemData['product_code'];
                if (str_contains($productCode, ' - ')) {
                    $productCode = trim(explode(' - ', $productCode)[0]);
                }

                // Lấy sản phẩm từ connection của nhà đích
                $targetProduct = Product::where('code', $productCode)->first();
                
                if (!$targetProduct) {
                    // Tự động tạo sản phẩm nếu chưa có
                    // Chuyển kết nối về nhà nguồn để lấy data
                    Config::set('database.connections.tenant.database', $oldDb);
                    DB::purge('tenant');
                    $sourceProduct = Product::where('code', $productCode)->first();
                    
                    // Chuyển lại connection về nhà đích
                    Config::set('database.connections.tenant.database', $newDb);
                    DB::purge('tenant');

                    $targetProduct = Product::create([
                        'code' => $sourceProduct->code,
                        'name' => $sourceProduct->name,
                        'unit' => $sourceProduct->unit,
                        'price' => $sourceProduct->price,
                        'category_id' => $sourceProduct->category_id, // category_id có thể bị sai nếu bảng categories không đồng bộ
                        'type' => $sourceProduct->type,
                        'status' => $sourceProduct->status,
                        'min_stock' => $sourceProduct->min_stock ?? 0,
                    ]);
                }

                // Cộng tồn kho nhà đích
                $targetInventory = Inventory::firstOrCreate(
                    ['product_id' => $targetProduct->id],
                    ['quantity' => 0]
                );

                $targetInventory->increment('quantity', $itemData['quantity']);

                // Ghi nhận transaction ở nhà đích
                InventoryTransaction::create([
                    'product_id' => $targetProduct->id,
                    'type' => 'transfer_in',
                    'quantity' => $itemData['quantity'],
                    'note' => "Nhận từ nhà số " . session('current_house', 1) . " (Phiếu: {$transfer->transfer_code})",
                    // 'reference_type' => StockTransfer::class, // Không dùng reference vì phiếu này không nằm trong DB của nhà đích
                    // 'reference_id' => $transfer->id,
                    'created_by' => auth()->id(), // Tạm dùng user hiện tại
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Khôi phục connection trước khi throw
            Config::set('database.connections.tenant.database', $oldDb);
            DB::purge('tenant');
            throw new \Exception("Lỗi khi xử lý ở nhà nhận: " . $e->getMessage());
        }

        // Khôi phục connection
        Config::set('database.connections.tenant.database', $oldDb);
        DB::purge('tenant');
    }

    public function updatedItems($value, $key)
    {
        if (str_contains($key, 'product_code')) {
            $index = explode('.', $key)[1];
            $this->searchProduct($index, $value);
        }
    }

    public function render()
    {
        $products = Product::whereHas('inventory', function ($q) {
            $q->where('quantity', '>', 0);
        })->get(['code', 'name', 'unit']);

        return view('livewire.warehouse.stock-transfer-form', [
            'products' => $products
        ])->layout('components.warehouse-layout', ['title' => 'Tạo Phiếu Chuyển Kho']);
    }
}
