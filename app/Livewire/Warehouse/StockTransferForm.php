<?php

namespace App\Livewire\Warehouse;

use App\Models\Product;
use App\Models\Project;
use App\Models\User;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class StockTransferForm extends Component
{
    public $to_project_id = null;
    public $note = '';
    public $sender_phone = '';
    public $receiver_id = null;
    public $receiver_phone = '';
    public $items = [];
    public $available_projects = [];
    public $users = [];
    public $searchQuery = ''; // Search query for autocomplete
    public $searchResults = []; // Search results for autocomplete

    public function mount()
    {
        $currentHouse = session('current_house', 1);
        $this->available_projects = Project::where('id', '!=', $currentHouse)->get();
        if ($this->available_projects->isNotEmpty()) {
            $this->to_project_id = $this->available_projects->first()->id;
        }
        $this->users = User::all();
        $this->sender_phone = auth()->user()->phone ?? '';

        $this->addItem();
    }

    public function addItem()
    {
        $this->items[] = [
            'product_code' => '',
            'quantity' => 1,
            'stock' => 0,
            'location' => '',
            'note' => ''
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function updatedSearchQuery($value)
    {
        if (empty($value)) {
            $this->searchResults = [];
            return;
        }

        $products = Product::with('inventory')->where('code', 'like', "%{$value}%")
            ->orWhere('name', 'like', "%{$value}%")
            ->limit(15)
            ->get();

        $this->searchResults = $products->map(function ($p) {
            $qty = $p->inventory ? $p->inventory->quantity : 0;
            return [
                'code' => $p->code,
                'name' => $p->name,
                'unit' => $p->unit,
                'stock' => $qty,
            ];
        })->toArray();
    }

    public function addSelectedProduct($productCode)
    {
        $product = Product::with('inventory')->where('code', $productCode)->first();
        $stock = $product && $product->inventory ? $product->inventory->quantity : 0;
        $location = $product ? ($product->inventory?->warehouse_location ?? $product->location ?? '') : '';

        $exists = false;
        foreach ($this->items as $index => $item) {
            if ($item['product_code'] === $productCode) {
                $this->items[$index]['quantity']++;
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $lastIndex = count($this->items) - 1;
            if ($lastIndex >= 0 && empty($this->items[$lastIndex]['product_code'])) {
                $this->items[$lastIndex]['product_code'] = $productCode;
                $this->items[$lastIndex]['quantity'] = 1;
                $this->items[$lastIndex]['stock'] = $stock;
                $this->items[$lastIndex]['location'] = $location;
            } else {
                $this->items[] = [
                    'product_code' => $productCode,
                    'quantity' => 1,
                    'stock' => $stock,
                    'location' => $location,
                    'note' => ''
                ];
            }
        }
        
        $this->searchQuery = '';
        $this->searchResults = [];
    }

    public function updated($property, $value)
    {
        if (str_starts_with($property, 'items.') && str_ends_with($property, '.product_code')) {
            $index = explode('.', $property)[1];
            $productCode = $value;
            if (str_contains($productCode, ' - ')) {
                $productCode = trim(explode(' - ', $productCode)[0]);
            }
            $product = Product::with('inventory')->where('code', $productCode)->first();
            if ($product) {
                $this->items[$index]['stock'] = $product->inventory ? $product->inventory->quantity : 0;
                $this->items[$index]['location'] = $product->inventory?->warehouse_location ?? $product->location ?? '';
            } else {
                $this->items[$index]['stock'] = 0;
                $this->items[$index]['location'] = '';
            }
        }
    }

    public function updatedReceiverId($value)
    {
        $user = User::find($value);
        if ($user) {
            $this->receiver_phone = $user->phone ?? '';
        } else {
            $this->receiver_phone = '';
        }
    }

    public function save()
    {
        $this->validate([
            'to_project_id' => 'required',
            'sender_phone' => 'nullable|string',
            'receiver_id' => 'required',
            'receiver_phone' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_code' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.location' => 'nullable|string',
            'items.*.note' => 'nullable|string',
        ]);

        $currentHouse = session('current_house', 1);
        if ($currentHouse == $this->to_project_id) {
            session()->flash('error', 'Không thể chuyển kho cho chính chi nhánh hiện tại.');
            return;
        }

        try {
            DB::beginTransaction();

            $transfer = StockTransfer::create([
                'transfer_code' => 'TF-' . date('Ymd') . '-' . rand(1000, 9999),
                'transfer_date' => now(),
                'from_project_id' => $currentHouse,
                'to_project_id' => $this->to_project_id,
                'sender_phone' => $this->sender_phone,
                'receiver_id' => $this->receiver_id,
                'receiver_phone' => $this->receiver_phone,
                'status' => 'pending',
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
                    'note' => "Chuyển đi, chờ chi nhánh nhận xác nhận",
                    'created_by' => auth()->id(),
                ]);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_code' => $product->code,
                    'quantity' => $itemData['quantity'],
                    'location' => $itemData['location'] ?? null,
                    'note' => $itemData['note'] ?? null,
                ]);
            }

            DB::commit();

            try {
                $senderName = auth()->user()->name ?? 'Thủ kho';
                $senderId = auth()->id();
                
                $fromProject = Project::find($currentHouse);
                $toProject = Project::find($this->to_project_id);
                $fromProjectName = $fromProject ? $fromProject->name : 'Chi nhánh gửi';
                $toProjectName = $toProject ? $toProject->name : 'Chi nhánh nhận';

                $systemMsg = "🔔 [CHUYỂN KHO] Kho {$fromProjectName} vừa tạo phiếu chuyển hàng tới Kho {$toProjectName}. Mã phiếu: {$transfer->transfer_code}";
                
                \App\Models\ChatMessage::create([
                    'user_id' => $senderId,
                    'type' => 'system',
                    'content' => $systemMsg,
                    'is_read' => false,
                ]);
            } catch (\Exception $ex) {
            }

            session()->flash('success', 'Tạo phiếu chuyển kho thành công! Đang chờ chi nhánh nhận xác nhận.');
            return redirect()->route('warehouse.stock-transfer.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Lỗi: ' . $e->getMessage());
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
