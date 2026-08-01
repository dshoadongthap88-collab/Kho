<?php

namespace App\Livewire\Hr;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockOutItem;
use App\Models\StockOut;
use App\Models\PurchasePlan;
use App\Models\Project;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseCenter extends Component
{
    use WithPagination;

    // Tab hiện tại
    public $activeTab = 'top-usage'; // top-usage | low-stock | proposals

    // Form tạo đề xuất mua hàng
    public $isModalOpen = false;
    public $proposal_id = null;
    public $proposal_house_id = '';
    public $proposal_product_id = '';
    public $proposal_quantity = '';
    public $proposal_expected_date = '';
    public $proposal_notes = '';

    // Bộ lọc
    public $searchProposal = '';
    public $filterStatus = '';

    protected $rules = [
        'proposal_house_id' => 'required|integer|exists:projects,id',
        'proposal_product_id' => 'required|integer',
        'proposal_quantity' => 'required|numeric|min:0.01',
        'proposal_expected_date' => 'nullable|date',
        'proposal_notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'proposal_house_id.required' => 'Vui lòng chọn dự án.',
        'proposal_product_id.required' => 'Vui lòng chọn mã vật tư.',
        'proposal_quantity.required' => 'Vui lòng nhập số lượng.',
        'proposal_quantity.min' => 'Số lượng phải lớn hơn 0.',
    ];

    public function updatingSearchProposal()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // ==================== CRUD ====================

    public function openCreateModal()
    {
        $this->resetForm();
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function openCreateFromProduct($productId, $houseId, $suggestedQty = 0)
    {
        $this->resetForm();
        $this->resetValidation();
        $this->proposal_product_id = $productId;
        $this->proposal_house_id = $houseId;
        $this->proposal_quantity = $suggestedQty > 0 ? $suggestedQty : '';
        $this->proposal_expected_date = now()->addDays(7)->format('Y-m-d');
        $this->isModalOpen = true;
    }

    public function editProposal($id)
    {
        $plan = PurchasePlan::withoutGlobalScope('house')->findOrFail($id);
        $this->proposal_id = $plan->id;
        $this->proposal_house_id = $plan->house_id ?? '';
        $this->proposal_product_id = $plan->product_id;
        $this->proposal_quantity = $plan->proposed_quantity;
        $this->proposal_expected_date = $plan->expected_delivery_date ? $plan->expected_delivery_date->format('Y-m-d') : '';
        $this->proposal_notes = $plan->notes;
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function saveProposal()
    {
        $this->validate();

        $data = [
            'product_id' => $this->proposal_product_id,
            'proposed_quantity' => $this->proposal_quantity,
            'expected_delivery_date' => $this->proposal_expected_date ?: null,
            'notes' => $this->proposal_notes,
            'status' => 'pending',
            'delivered_quantity' => 0,
            'house_id' => $this->proposal_house_id,
        ];

        if ($this->proposal_id) {
            // Update
            $plan = PurchasePlan::withoutGlobalScope('house')->findOrFail($this->proposal_id);
            unset($data['status']); // Giữ nguyên trạng thái khi sửa
            unset($data['delivered_quantity']);
            $plan->update($data);
            session()->flash('message', 'Cập nhật đề xuất mua hàng thành công!');
        } else {
            // Create - bypass BelongsToHouse auto-assign
            $plan = new PurchasePlan();
            $plan->fill($data);
            $plan->house_id = $this->proposal_house_id;
            $plan->saveQuietly();

            // Ghi lịch sử thủ công
            \App\Models\PurchasePlanHistory::create([
                'purchase_plan_id' => $plan->id,
                'old_status' => null,
                'new_status' => 'pending',
                'old_quantity' => 0,
                'new_quantity' => 0,
                'notes' => 'Khởi tạo đề xuất từ Trung Tâm Mua Hàng HR. ' . ($this->proposal_notes ?? ''),
                'changed_by' => auth()->id(),
            ]);

            session()->flash('message', 'Tạo đề xuất mua hàng thành công!');
        }

        $this->closeModal();
    }

    public function deleteProposal($id)
    {
        $plan = PurchasePlan::withoutGlobalScope('house')->findOrFail($id);
        $plan->delete();
        session()->flash('message', 'Đã xóa đề xuất mua hàng.');
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->proposal_id = null;
        $this->proposal_house_id = '';
        $this->proposal_product_id = '';
        $this->proposal_quantity = '';
        $this->proposal_expected_date = '';
        $this->proposal_notes = '';
    }

    // ==================== DATA ====================

    public function getTopUsageDataProperty()
    {
        $since = Carbon::now()->subDays(60);

        return StockOutItem::withoutGlobalScopes()
            ->select(
                'stock_out_items.product_id',
                DB::raw('SUM(stock_out_items.quantity) as total_qty'),
                DB::raw('COUNT(DISTINCT stock_outs.id) as total_orders')
            )
            ->join('stock_outs', 'stock_out_items.stock_out_id', '=', 'stock_outs.id')
            ->where('stock_outs.created_at', '>=', $since)
            ->groupBy('stock_out_items.product_id')
            ->orderByDesc('total_qty')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                $product = Product::withoutGlobalScope('house')->find($item->product_id);
                // Tìm dự án xuất nhiều nhất
                $topHouse = StockOut::withoutGlobalScopes()
                    ->select('house_id', DB::raw('SUM(1) as cnt'))
                    ->whereHas('items', fn($q) => $q->where('product_id', $item->product_id))
                    ->where('created_at', '>=', Carbon::now()->subDays(60))
                    ->groupBy('house_id')
                    ->orderByDesc('cnt')
                    ->first();

                $projectName = $topHouse ? (Project::find($topHouse->house_id)?->name ?? 'N/A') : 'N/A';

                return [
                    'product_id' => $item->product_id,
                    'code' => $product->code ?? 'N/A',
                    'name' => $product->name ?? 'N/A',
                    'unit' => $product->unit ?? '',
                    'total_qty' => $item->total_qty,
                    'total_orders' => $item->total_orders,
                    'top_project' => $projectName,
                    'house_id' => $topHouse->house_id ?? null,
                ];
            });
    }

    public function getLowStockDataProperty()
    {
        return Inventory::withoutGlobalScope('house')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('products.min_stock', '>', 0)
            ->whereColumn('inventories.quantity', '<', 'products.min_stock')
            ->select(
                'inventories.*',
                'products.code as product_code',
                'products.name as product_name',
                'products.unit as product_unit',
                'products.min_stock',
                'inventories.house_id'
            )
            ->orderByRaw('(products.min_stock - inventories.quantity) DESC')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                $projectName = Project::find($item->house_id)?->name ?? 'N/A';
                return [
                    'product_id' => $item->product_id,
                    'code' => $item->product_code,
                    'name' => $item->product_name,
                    'unit' => $item->product_unit,
                    'current_qty' => $item->quantity,
                    'min_stock' => $item->min_stock,
                    'shortage' => $item->min_stock - $item->quantity,
                    'project' => $projectName,
                    'house_id' => $item->house_id,
                ];
            });
    }

    public function render()
    {
        // Danh sách đề xuất (CRUD)
        $proposalsQuery = PurchasePlan::withoutGlobalScope('house')
            ->with('product')
            ->orderByDesc('created_at');

        if ($this->searchProposal) {
            $search = $this->searchProposal;
            $proposalsQuery->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->withoutGlobalScope('house')
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($this->filterStatus) {
            $proposalsQuery->where('status', $this->filterStatus);
        }

        $proposals = $proposalsQuery->paginate(15);

        // Danh sách dự án (trừ HR)
        $projects = Project::where('id', '!=', 5)->get();

        // Danh sách sản phẩm cross-house
        $allProducts = Product::withoutGlobalScope('house')
            ->select('id', 'code', 'name', 'unit', 'house_id')
            ->orderBy('code')
            ->get();

        return view('livewire.hr.purchase-center', [
            'proposals' => $proposals,
            'projects' => $projects,
            'allProducts' => $allProducts,
        ])->layout('layouts.app', ['title' => 'Trung Tâm Mua Hàng']);
    }
}
