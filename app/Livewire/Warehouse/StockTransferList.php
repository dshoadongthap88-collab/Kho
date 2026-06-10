<?php

namespace App\Livewire\Warehouse;

use App\Models\StockTransfer;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class StockTransferList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransfers = [];
    public $showDetailModal = false;
    public $selectedTransferId = null;

    // Thêm hàm này để đảm bảo Livewire theo dõi mảng chính xác
    public function updatedSelectedTransfers()
    {
        // Log hoặc xử lý khi mảng thay đổi nếu cần
    }

    #[\Livewire\Attributes\Computed]
    public function selectedTransferDetail()
    {
        if (!$this->selectedTransferId) {
            return null;
        }
        return StockTransfer::with(['creator', 'items.product'])->find($this->selectedTransferId);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function toggleSelect($id)
    {
        $id = (string)$id;
        if (in_array($id, $this->selectedTransfers)) {
            $this->selectedTransfers = array_values(array_diff($this->selectedTransfers, [$id]));
        } else {
            $this->selectedTransfers[] = $id;
        }
    }

    public function selectAll($value)
    {
        if ($value) {
            $this->selectedTransfers = StockTransfer::where('transfer_code', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->map(fn($id) => (string)$id) // Livewire checkbox value thường là string
                ->toArray();
        } else {
            $this->selectedTransfers = [];
        }
    }

    public function viewDetail($id)
    {
        $this->selectedTransferId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransferId = null;
    }

    public function printSelected()
    {
        if (empty($this->selectedTransfers)) {
            return;
        }

        // Đảm bảo tất cả ID là string để implode hoạt động chính xác
        $ids = implode(',', array_map('strval', $this->selectedTransfers));
        $url = route('warehouse.stock-transfer.print-bulk', ['ids' => $ids]);

        $this->dispatch('open-print-window', url: $url);

        session()->flash('success', count($this->selectedTransfers) . ' phiếu đã được đưa vào hàng đợi in.');
        $this->selectedTransfers = [];
    }

    public function deleteSelected()
    {
        if (empty($this->selectedTransfers)) {
            return;
        }

        $count = count($this->selectedTransfers);

        // Xóa các items trước để tránh lỗi ràng buộc khóa ngoại
        DB::table('stock_transfer_items')->whereIn('stock_transfer_id', $this->selectedTransfers)->delete();

        // Xóa các phiếu chuyển kho
        StockTransfer::whereIn('id', $this->selectedTransfers)->delete();

        session()->flash('success', $count . ' phiếu đã được xóa thành công.');
        $this->selectedTransfers = [];
    }

    public function printSingle($id)
    {
        $transfer = StockTransfer::with(['creator', 'items.product'])->find($id);
        if ($transfer) {
            session()->flash('success', 'Phiếu ' . $transfer->transfer_code . ' đã được đưa vào hàng đợi in.');
        }
    }

    public function deleteTransfer($id)
    {
        $transfer = StockTransfer::find($id);
        if ($transfer) {
            $transferCode = $transfer->transfer_code;
            $transfer->items()->delete();
            $transfer->delete();

            session()->flash('success', 'Phiếu ' . $transferCode . ' đã được xóa thành công.');
            $this->closeDetailModal();
            $this->resetPage();
        }
    }

    public function render()
    {
        $transfers = StockTransfer::with(['creator', 'items.product'])
            ->where('transfer_code', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.warehouse.stock-transfer-list', [
            'transfers' => $transfers
        ])->layout('components.warehouse-layout', ['title' => 'Lịch sử chuyển kho']);
    }
}
