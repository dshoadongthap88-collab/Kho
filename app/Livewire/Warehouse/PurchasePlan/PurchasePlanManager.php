<?php

namespace App\Livewire\Warehouse\PurchasePlan;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PurchasePlan;
use App\Models\Product;

class PurchasePlanManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public $selected = [];
    public $selectAll = false;

    // Dữ liệu cho modal thêm đề xuất thủ công
    public $new_product_id = '';
    public $new_quantity = 1;
    public $new_notes = '';

    // Dữ liệu cho modal cập nhật
    public $updateId = null;
    public $delivered_quantity = 0;
    public $expected_delivery_date = null;

    protected $updatesQueryString = ['search', 'statusFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selected = $this->getFilteredPlans()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function getFilteredPlans()
    {
        return PurchasePlan::with('product')
            ->where('is_archived', false)
            ->when($this->search, function($q) {
                $q->whereHas('product', function($q2) {
                    $q2->where('code', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc');
    }

    public function autoSuggest()
    {
        // Lấy tất cả vật tư (không giới hạn)
        $products = Product::with('inventory')->get();
        
        $count = 0;
        foreach ($products as $product) {
            $currentStock = $product->inventory ? $product->inventory->quantity : 0;
            // Kiểm tra sắp hết tồn (<= tồn tối thiểu)
            if ($currentStock <= $product->min_stock) {
                // Kiểm tra xem đã có kế hoạch mua hàng đang pending hoặc unreceived/partial chưa
                $existing = PurchasePlan::where('product_id', $product->id)
                    ->whereNotIn('status', ['completed'])
                    ->first();
                    
                if (!$existing) {
                    PurchasePlan::create([
                        'product_id' => $product->id,
                        'proposed_quantity' => $product->min_stock > 0 ? $product->min_stock : 1,
                        'status' => 'pending',
                        'notes' => 'Tự động đề xuất do tồn kho (' . $currentStock . ') <= tồn tối thiểu (' . $product->min_stock . ')',
                    ]);
                    $count++;
                }
            }
        }
        
        session()->flash('message', "Đã tự động đề xuất mua cho $count vật tư.");
    }

    public function openAddModal()
    {
        $this->resetValidation();
        $this->new_product_id = '';
        $this->new_quantity = 1;
        $this->new_notes = 'Đề xuất thủ công';
        $this->dispatch('open-modal', 'add-plan-modal');
    }

    public function saveNewPlan()
    {
        $this->validate([
            'new_product_id' => 'required|exists:products,id',
            'new_quantity' => 'required|numeric|min:1',
        ]);

        PurchasePlan::create([
            'product_id' => $this->new_product_id,
            'proposed_quantity' => $this->new_quantity,
            'status' => 'pending',
            'notes' => $this->new_notes,
        ]);

        $this->dispatch('close-modal', 'add-plan-modal');
        session()->flash('message', 'Đã thêm đề xuất mua hàng thủ công.');
    }

    public function updateProposedQuantity($id, $newQuantity)
    {
        $plan = PurchasePlan::findOrFail($id);
        
        // Chỉ cho phép sửa nếu đang ở trạng thái pending hoặc chưa hoàn thành (tuỳ logic, giả sử không phải completed là được sửa)
        if ($plan->status !== 'completed' && is_numeric($newQuantity) && $newQuantity > 0) {
            $plan->proposed_quantity = $newQuantity;
            
            // Cập nhật lại số lượng còn thiếu và ghi chú nếu đã giao 1 phần
            if ($plan->delivered_quantity > 0) {
                if ($plan->delivered_quantity < $plan->proposed_quantity) {
                    $plan->status = 'partial';
                    $missing = $plan->proposed_quantity - $plan->delivered_quantity;
                    $plan->notes = "Yêu cầu NCC giao bổ sung $missing sản phẩm.";
                } else {
                    $plan->status = 'completed';
                    $plan->notes = 'Hoàn thành.';
                }
            }
            
            $plan->save();
            session()->flash('message', 'Đã cập nhật số lượng đề xuất.');
        }
    }

    public function printSelected()
    {
        if (empty($this->selected)) {
            session()->flash('message', 'Vui lòng chọn ít nhất 1 mục để in.');
            return;
        }

        // Chuyển hướng sang route in
        $ids = implode(',', $this->selected);
        return redirect()->route('purchase-plan.print', ['ids' => $ids]);
    }

    public function placeOrder($id)
    {
        $plan = PurchasePlan::findOrFail($id);
        if ($plan->status === 'pending') {
            $plan->status = 'ordered';
            $plan->save();
        }
    }

    public function openUpdateModal($id)
    {
        $this->resetValidation();
        $plan = PurchasePlan::findOrFail($id);
        $this->updateId = $id;
        $this->delivered_quantity = $plan->delivered_quantity;
        $this->expected_delivery_date = $plan->expected_delivery_date ? $plan->expected_delivery_date->format('Y-m-d') : null;
        $this->dispatch('open-modal', 'update-delivery-modal');
    }

    public function saveDeliveryUpdate()
    {
        $this->validate([
            'delivered_quantity' => 'required|numeric|min:0',
        ]);

        $plan = PurchasePlan::findOrFail($this->updateId);
        $plan->delivered_quantity = $this->delivered_quantity;
        $plan->expected_delivery_date = $this->expected_delivery_date;

        // Tính toán trạng thái và ghi chú tự động
        if ($plan->delivered_quantity <= 0) {
            $plan->status = 'unreceived';
            $plan->notes = 'Chưa giao hàng.';
        } elseif ($plan->delivered_quantity < $plan->proposed_quantity) {
            $plan->status = 'partial';
            $missing = $plan->proposed_quantity - $plan->delivered_quantity;
            $plan->notes = "Yêu cầu NCC giao bổ sung $missing sản phẩm.";
        } else {
            $plan->status = 'completed';
            $plan->notes = 'Hoàn thành.';
        }

        $plan->save();
        
        $this->dispatch('close-modal', 'update-delivery-modal');
        $this->updateId = null;
        
        session()->flash('message', 'Đã cập nhật tình trạng giao hàng.');
    }

    public function closeDay()
    {
        // Lấy tất cả danh sách đang chưa lưu trữ
        $activePlans = PurchasePlan::with('product')->where('is_archived', false)->get();
        
        if ($activePlans->isEmpty()) {
            session()->flash('message', 'Không có dữ liệu nào để chốt sổ.');
            return;
        }

        // Định dạng thành Markdown
        $markdown = "\n\n## Chốt sổ ngày " . now()->format('d/m/Y H:i:s') . "\n";
        $markdown .= "| Ngày ĐX | Mã VT | Tên Vật Tư | ĐVT | SL Đề Xuất | Đã Giao | Còn Thiếu | Trạng Thái | Ghi Chú |\n";
        $markdown .= "|---------|--------|------------|-----|-------------|---------|------------|------------|----------|\n";
        
        foreach ($activePlans as $plan) {
            $missing = $plan->proposed_quantity - $plan->delivered_quantity;
            $missing = $missing > 0 ? $missing : 0;
            $statusText = match($plan->status) {
                'pending' => 'Đề xuất',
                'ordered' => 'Đã đặt',
                'unreceived' => 'Chưa giao',
                'partial' => 'Giao thiếu',
                'completed' => 'Đủ hàng',
                default => $plan->status
            };
            
            $markdown .= sprintf(
                "| %s | %s | %s | %s | %s | %s | %s | %s | %s |\n",
                $plan->created_at->format('d/m/Y'),
                $plan->product->code,
                $plan->product->name,
                $plan->product->unit ?? 'Cái',
                number_format($plan->proposed_quantity, 0),
                number_format($plan->delivered_quantity, 0),
                number_format($missing, 0),
                $statusText,
                $plan->notes
            );
            
            // Đánh dấu là đã lưu trữ
            $plan->is_archived = true;
            $plan->save();
        }

        // Ghi vào cuối file markdown
        $filePath = 'd:\Project\docs\cau_hinh_lich_su_mua_hang.md';
        if (file_exists($filePath)) {
            file_put_contents($filePath, $markdown, FILE_APPEND);
        } else {
            file_put_contents($filePath, "# Lịch sử chốt sổ kế hoạch mua hàng" . $markdown);
        }

        $this->selected = [];
        $this->selectAll = false;
        
        session()->flash('message', 'Đã chốt sổ ' . $activePlans->count() . ' đề xuất và lưu vào tài liệu thành công!');
    }

    public function delete($id)
    {
        PurchasePlan::findOrFail($id)->delete();
        session()->flash('message', 'Đã xóa đề xuất mua hàng.');
    }

    public function render()
    {
        $plans = $this->getFilteredPlans()->paginate(15);
        $allProducts = Product::orderBy('name')->get();

        return view('livewire.warehouse.purchase-plan.purchase-plan-manager', [
            'plans' => $plans,
            'allProducts' => $allProducts
        ]);
    }
}
