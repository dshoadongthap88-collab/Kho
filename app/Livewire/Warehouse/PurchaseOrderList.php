<?php

namespace App\Livewire\Warehouse;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class PurchaseOrderList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $showModal = false;
    public $isEdit = false;
    public $orderId;

    // Checkboxes cho việc in
    public $selectedOrders = [];

    // Form fields
    public $po_number;
    public $supplier_id;
    public $order_date;
    public $expected_delivery_date;
    public $total_amount = 0;
    public $status = 'pending';
    public $notes;

    // Order items
    public $items = [];
    public $newItemProductId;
    public $newItemQuantity;
    public $newItemUnitPrice;

    // Office purchase modal
    public $showOfficeModal = false;
    public $officeItems = [];
    public $officeItemName = '';
    public $officeItemQuantity = '';
    public $officeItemPrice = '';

    protected $queryString = ['search', 'filterStatus'];

    public function mount()
    {
        if (session()->has('mrp_missing_items')) {
            $missingItems = session('mrp_missing_items');
            $this->openModal(); // Tự động mở form
            
            foreach ($missingItems as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $this->items[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price ?? 0,
                        'line_total' => $item['quantity'] * ($product->price ?? 0),
                    ];
                }
            }
            $this->calculateTotal();
            $this->notes = 'Chuyển tự động từ phân tích Kế hoạch Nhu cầu NVL (MRP).';
            
            // Xoá session để nếu user mở form khác nó ko tự điền lại
            session()->forget('mrp_missing_items');
        }
    }

    public function rules()
    {
        return [
            'po_number' => 'required|string|max:50',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:order_date',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,received,cancelled',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Tự động generate số PO (thứ tự tiếp theo)
     */
    private function generatePONumber()
    {
        $lastOrder = PurchaseOrder::latest('id')->first();
        $sequence = ($lastOrder ? intval(preg_replace('/[^0-9]/', '', $lastOrder->po_number)) : 0) + 1;
        return 'PO-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['po_number', 'supplier_id', 'order_date', 'expected_delivery_date', 'total_amount', 'status', 'notes', 'orderId', 'items', 'newItemProductId', 'newItemQuantity', 'newItemUnitPrice']);
        
        if ($id) {
            $this->isEdit = true;
            $this->orderId = $id;
            $order = PurchaseOrder::findOrFail($id);
            $this->po_number = $order->po_number;
            $this->supplier_id = $order->supplier_id;
            $this->order_date = $order->order_date?->format('Y-m-d');
            $this->expected_delivery_date = $order->expected_delivery_date?->format('Y-m-d');
            $this->total_amount = $order->total_amount;
            $this->status = $order->status;
            $this->notes = $order->notes;
            
            // Load items
            $this->items = $order->items->map(fn($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ])->toArray();
        } else {
            $this->isEdit = false;
            // Auto generate số PO
            $this->po_number = $this->generatePONumber();
            // Ngày đặt là ngày hiện tại
            $this->order_date = now()->format('Y-m-d');
            // Ngày dự kiến giao là 3 ngày sau
            $this->expected_delivery_date = now()->addDays(3)->format('Y-m-d');
            $this->status = 'pending';
        }

        $this->showModal = true;
    }

    /**
     * Khi thay đổi ngày đặt hàng, cập nhật ngày dự kiến giao
     */
    public function updatedOrderDate()
    {
        if (!$this->isEdit) {
            $this->expected_delivery_date = Carbon::parse($this->order_date)->addDays(3)->format('Y-m-d');
        }
    }

    /**
     * Khi chọn sản phẩm, auto lấy giá từ danh mục
     */
    public function updatedNewItemProductId()
    {
        if ($this->newItemProductId) {
            $product = Product::find($this->newItemProductId);
            if ($product) {
                $this->newItemUnitPrice = $product->price ?? 0;
            }
        }
    }

    public function addItem()
    {
        if (!$this->newItemProductId || !$this->newItemQuantity) {
            session()->flash('error', 'Vui lòng chọn sản phẩm và nhập số lượng.');
            return;
        }

        // Nếu đơn giá chưa được set, tự động lấy từ sản phẩm
        if (!$this->newItemUnitPrice) {
            $product = Product::find($this->newItemProductId);
            $this->newItemUnitPrice = $product->price ?? 0;
        }

        if ($this->newItemUnitPrice <= 0) {
            session()->flash('error', 'Sản phẩm chưa có giá.');
            return;
        }

        $lineTotal = $this->newItemQuantity * $this->newItemUnitPrice;
        
        $this->items[] = [
            'product_id' => $this->newItemProductId,
            'quantity' => $this->newItemQuantity,
            'unit_price' => $this->newItemUnitPrice,
            'line_total' => $lineTotal,
        ];

        $this->calculateTotal();
        $this->reset(['newItemProductId', 'newItemQuantity', 'newItemUnitPrice']);
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_amount = collect($this->items)->sum('line_total');
    }

    public function save()
    {
        $this->validate();

        if (empty($this->items)) {
            session()->flash('error', 'Vui lòng thêm ít nhất một mục hàng.');
            return;
        }

        if ($this->isEdit) {
            $order = PurchaseOrder::find($this->orderId);
            $order->update([
                'po_number' => $this->po_number,
                'supplier_id' => $this->supplier_id,
                'order_date' => $this->order_date,
                'expected_delivery_date' => $this->expected_delivery_date,
                'total_amount' => $this->total_amount,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);
            
            // Delete old items and create new ones
            $order->items()->delete();
        } else {
            $order = PurchaseOrder::create([
                'po_number' => $this->po_number,
                'supplier_id' => $this->supplier_id,
                'order_date' => $this->order_date,
                'expected_delivery_date' => $this->expected_delivery_date,
                'total_amount' => $this->total_amount,
                'status' => $this->status,
                'notes' => $this->notes,
                'user_id' => auth()->id(), // Lưu user_id của user đang đăng nhập
            ]);
        }

        // Create items
        foreach ($this->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['line_total'],
            ]);
        }

        session()->flash('message', $this->isEdit ? 'Cập nhật đơn hàng thành công.' : 'Tạo đơn hàng mới thành công.');
        $this->showModal = false;
    }

    /**
     * Xác nhận đơn hàng (chuyển sang trạng thái confirmed)
     */
    public function confirmOrder($id)
    {
        $order = PurchaseOrder::findOrFail($id);
        $order->update(['status' => 'confirmed']);
        session()->flash('message', 'Đã xác nhận đơn hàng: ' . $order->po_number);
    }

    public function delete($id)
    {
        $order = PurchaseOrder::findOrFail($id);
        $order->items()->delete();
        $order->delete();
        session()->flash('message', 'Đã xoá đơn hàng.');
    }

    public function printSelected()
    {
        if (empty($this->selectedOrders)) {
            session()->flash('error', 'Vui lòng đánh dấu chọn (tick) ít nhất một phiếu đề xuất để in.');
            return;
        }

        // Tạm mượn chức năng window.print của trình duyệt (có thể mở rộng thành trang view in ấn riêng)
        $this->dispatch('trigger-print');
    }

    public function openOfficeModal()
    {
        $this->resetValidation();
        $this->reset([
            'po_number', 'supplier_id', 'order_date', 'expected_delivery_date', 
            'total_amount', 'status', 'notes', 'orderId', 'officeItems', 
            'officeItemName', 'officeItemQuantity', 'officeItemPrice'
        ]);
        $this->isEdit = false;
        $this->po_number = $this->generatePONumber();
        $this->order_date = now()->format('Y-m-d');
        $this->expected_delivery_date = now()->addDays(3)->format('Y-m-d');
        $this->status = 'pending';
        $this->showOfficeModal = true;
    }

    public function addOfficeItem()
    {
        if (empty($this->officeItemName) || empty($this->officeItemQuantity)) {
            $this->addError('officeItem', 'Vui lòng nhập tên vật tư và số lượng.');
            return;
        }

        $price = $this->officeItemPrice ? $this->officeItemPrice : 0;
        $lineTotal = $this->officeItemQuantity * $price;

        $this->officeItems[] = [
            'name' => trim($this->officeItemName),
            'quantity' => $this->officeItemQuantity,
            'unit_price' => $price,
            'line_total' => $lineTotal,
        ];

        $this->total_amount = collect($this->officeItems)->sum('line_total');
        $this->reset(['officeItemName', 'officeItemQuantity', 'officeItemPrice']);
        $this->resetValidation('officeItem');
    }

    public function removeOfficeItem($index)
    {
        unset($this->officeItems[$index]);
        $this->officeItems = array_values($this->officeItems);
        $this->total_amount = collect($this->officeItems)->sum('line_total');
    }

    public function saveOfficePurchase()
    {
        $this->validate([
            'po_number' => 'required|string|max:50',
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:order_date',
        ]);

        if (empty($this->officeItems)) {
            session()->flash('error', 'Vui lòng thêm ít nhất một vật tư.');
            return;
        }

        $order = PurchaseOrder::create([
            'po_number' => $this->po_number,
            'supplier_id' => $this->supplier_id,
            'order_date' => $this->order_date,
            'expected_delivery_date' => $this->expected_delivery_date,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'notes' => 'Mua văn phòng phẩm. ' . $this->notes,
            'user_id' => auth()->id(),
        ]);

        foreach ($this->officeItems as $item) {
            // Tự động tạo sản phẩm ẩn
            $product = Product::create([
                'code' => 'VP-' . date('YmdHis') . rand(10, 99),
                'name' => $item['name'],
                'unit' => 'Cái', // mặc định
                'type' => 'office_supply',
                'status' => 'active',
                'price' => $item['unit_price']
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['line_total'],
            ]);
        }

        session()->flash('message', 'Tạo đơn mua hàng văn phòng thành công.');
        $this->showOfficeModal = false;
    }

    public function render()
    {
        $orders = PurchaseOrder::query()->with(['supplier', 'user']);

        // Chỉ áp dụng filter tìm kiếm nếu có giá trị search
        if (!empty($this->search)) {
            $orders->where(function($q) {
                $q->where('po_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('supplier', fn($query) => $query->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        if ($this->filterStatus) {
            $orders->where('status', $this->filterStatus);
        }

        $orders = $orders->latest()->paginate(15);
        $products = Product::with('inventory')->where('status', 'active')->get();

        // Lọc ra các sản phẩm sắp hết hàng hoặc đã hết (tồn kho <= min_stock)
        $lowStockProducts = $products->filter(function($p) {
            return $p->is_low_stock || ($p->inventory?->quantity ?? 0) <= 0;
        });

        return view('livewire.warehouse.purchase-order-list', [
            'orders' => $orders,
            'suppliers' => Supplier::where('status', 'active')->where('type', '!=', 'customer')->get(),
            'products' => $products,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
