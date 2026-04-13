<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use App\Models\Product;

class MaterialRequirement extends Component
{
    public $targetProducts = []; // ['id' => uniqid(), 'product_id' => XX, 'quantity' => YY]
    public $newProductId = '';
    public $newQuantity = 1;
    
    public $materialNeeds = []; // Bảng tính: [material_id => [...details...]]

    public function addTarget()
    {
        $this->validate([
            'newProductId' => 'required|exists:products,id',
            'newQuantity' => 'required|numeric|min:1'
        ]);

        $product = Product::find($this->newProductId);

        // Sinh ID tự động để dễ xoá khỏi mảng 
        $this->targetProducts[] = [
            'id' => uniqid('tgt_'),
            'product_id' => $this->newProductId,
            'name' => $product->name,
            'code' => $product->code,
            'quantity' => $this->newQuantity
        ];

        $this->newProductId = '';
        $this->newQuantity = 1;
        
        $this->calculateNeeds();
    }

    public function removeTarget($id)
    {
        $this->targetProducts = array_filter($this->targetProducts, fn($t) => $t['id'] !== $id);
        $this->calculateNeeds();
    }

    public function calculateNeeds()
    {
        $this->materialNeeds = [];

        if (empty($this->targetProducts)) {
            return;
        }

        // Tạo mảng dồn nhu cầu rác
        $needs = [];

        foreach ($this->targetProducts as $target) {
            $product = Product::with('boms.material.inventory')->find($target['product_id']);
            if (!$product || $product->boms->isEmpty()) continue;

            foreach ($product->boms as $bom) {
                $matId = $bom->material_id;
                $reqQty = floatval($bom->quantity) * floatval($target['quantity']);

                if (isset($needs[$matId])) {
                    $needs[$matId]['required'] += $reqQty;
                } else {
                    $mat = $bom->material;
                    $inStock = $mat->inventory ? floatval($mat->inventory->quantity) : 0;
                    
                    $needs[$matId] = [
                        'material_id' => $matId,
                        'code' => $mat->code,
                        'name' => $mat->name,
                        'unit' => $mat->unit,
                        'in_stock' => $inStock,
                        'required' => $reqQty,
                        'shortage' => 0
                    ];
                }
            }
        }

        // Tính lượng thiếu hụt cuối cùng
        foreach ($needs as &$mat) {
            $shortage = $mat['required'] - $mat['in_stock'];
            $mat['shortage'] = $shortage > 0 ? $shortage : 0;
        }

        $this->materialNeeds = $needs;
    }

    public function sendToPurchase()
    {
        if (empty($this->materialNeeds)) {
            session()->flash('error', 'Không có dữ liệu nguyên vật liệu để đề xuất mua.');
            return;
        }

        $missingItems = array_filter($this->materialNeeds, fn($m) => $m['shortage'] > 0);

        if (empty($missingItems)) {
            session()->flash('error', 'Nguyên vật liệu hiện tại đủ để sản xuất, không cần duyệt mua thêm.');
            return;
        }

        // Tạo 1 nhà cung cấp nháp nếu chưa có để pass qua ràng buộc database
        $supplier = \App\Models\Supplier::firstOrCreate(
            ['code' => 'NCC-PENDING'],
            [
                'name' => 'Chờ bổ sung Danh tính NCC',
                'email' => 'none@temp.local',
                'phone' => '000000000',
                'address' => 'Chưa xác định',
                'type' => 'supplier',
                'status' => 'active'
            ]
        );

        // Khởi tạo PO tự động
        $lastOrder = \App\Models\PurchaseOrder::latest('id')->first();
        $sequence = ($lastOrder ? intval(preg_replace('/[^0-9]/', '', $lastOrder->po_number)) : 0) + 1;
        $poNumber = 'PO-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);

        $order = \App\Models\PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $supplier->id,
            'order_date' => now(),
            'expected_delivery_date' => now()->addDays(3),
            'total_amount' => 0, // sẽ update sau
            'status' => 'pending', // Trạng thái 'pending' bây giờ dịch là 'Đã trình'
            'notes' => 'Tự động trình từ bảng MRP. Xin bổ sung đầy đủ giá và NCC thủ công trước khi Duyệt.',
            'user_id' => auth()->id() ?? 1,
        ]);

        $totalAmount = 0;
        foreach ($missingItems as $item) {
            $product = \App\Models\Product::find($item['material_id']);
            $unitPrice = $product ? $product->price : 0;
            $lineTotal = $item['shortage'] * $unitPrice;
            $totalAmount += $lineTotal;

            $order->items()->create([
                'product_id' => $item['material_id'],
                'quantity' => $item['shortage'],
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }

        $order->update(['total_amount' => $totalAmount]);

        // Làm trống bảng khi gửi đi
        $this->reset(['targetProducts', 'materialNeeds']);

        session()->flash('message', 'Phiếu Đề xuất Mua hàng tự động (PO) phần hàng thiếu đã được Trình. Vui lòng Bổ sung thông tin Nhà CC và duyệt!');
        return redirect()->route('warehouse.purchase-request');
    }

    public function render()
    {
        // Danh sách các thành phẩm có định mức (BOM)
        $hasBomProducts = Product::whereHas('boms')->where('status', 'active')->get();

        return view('livewire.warehouse.material-requirement', [
            'hasBomProducts' => $hasBomProducts
        ])->layout('components.warehouse-layout', ['title' => 'Kế hoạch Nhu cầu NVL (MRP)']);
    }
}
