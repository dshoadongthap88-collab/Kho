<?php

namespace App\Livewire\Warehouse;

use App\Models\StockTransfer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\InventoryTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class StockTransferList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTransfers = [];
    public $showDetailModal = false;
    public $selectedTransferId = null;
    public $rejectReason = '';

    public function updatedSelectedTransfers()
    {
    }

    #[\Livewire\Attributes\Computed]
    public function selectedTransferDetail()
    {
        if (!$this->selectedTransferId) {
            return null;
        }
        return StockTransfer::with(['creator', 'items', 'fromProject', 'toProject'])->find($this->selectedTransferId);
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
                ->map(fn($id) => (string)$id)
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

    public function confirmTransfer($id)
    {
        $transfer = StockTransfer::with('items')->find($id);
        if (!$transfer) return;

        if ($transfer->status !== 'pending') {
            session()->flash('error', 'Phiếu này đã được xử lý.');
            return;
        }

        $currentHouse = session('current_house', 1);
        if ($transfer->to_project_id != $currentHouse) {
            session()->flash('error', 'Bạn không có quyền xác nhận phiếu này (Phiếu gửi cho chi nhánh khác).');
            return;
        }

        try {
            DB::beginTransaction();

            foreach ($transfer->items as $itemData) {
                $productCode = $itemData->product_code;

                $targetProduct = Product::where('code', $productCode)->first();
                
                if (!$targetProduct) {
                    // Nếu chưa có SP ở nhà nhận, tự tạo SP mới (đáng lẽ phải lấy data từ nguồn, nhưng do ở đây ta chỉ có product_code)
                    $targetProduct = Product::create([
                        'code' => $productCode,
                        'name' => 'Sản phẩm chuyển từ kho khác',
                        'unit' => 'Cái',
                        'price' => 0,
                    ]);
                }

                $targetInventory = Inventory::firstOrCreate(
                    ['product_id' => $targetProduct->id],
                    ['quantity' => 0]
                );

                $targetInventory->increment('quantity', $itemData->quantity);

                InventoryTransaction::create([
                    'product_id' => $targetProduct->id,
                    'type' => 'transfer_in',
                    'quantity' => $itemData->quantity,
                    'note' => "Nhận từ chi nhánh {$transfer->from_project_id} (Phiếu: {$transfer->transfer_code})",
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'created_by' => auth()->id(),
                ]);
            }

            $transfer->update([
                'status' => 'completed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            DB::commit();

            // Đổi trạng thái ở DB nguồn
            $this->updateSourceHouseStatus($transfer);

            session()->flash('success', 'Đã xác nhận nhận hàng thành công!');
            $this->closeDetailModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    private function updateSourceHouseStatus($transfer, $action = 'confirm', $reason = null)
    {
        $sourceHouse = $transfer->from_project_id;
        $oldDb = Config::get('database.connections.tenant.database');
        $newDb = $sourceHouse == 1 ? 'laravel' : 'laravel_' . $sourceHouse;
        
        try {
            Config::set('database.connections.tenant.database', $newDb);
            DB::purge('tenant');

            $sourceTransfer = StockTransfer::with('items')->where('transfer_code', $transfer->transfer_code)->first();
            if ($sourceTransfer) {
                if ($action === 'confirm') {
                    // Trừ tồn kho nguồn
                    foreach ($sourceTransfer->items as $itemData) {
                        $sourceProduct = Product::where('code', $itemData->product_code)->first();
                        if ($sourceProduct) {
                            $sourceInventory = Inventory::firstOrCreate(
                                ['product_id' => $sourceProduct->id],
                                ['quantity' => 0]
                            );
                            $sourceInventory->decrement('quantity', $itemData->quantity);

                            InventoryTransaction::create([
                                'product_id' => $sourceProduct->id,
                                'type' => 'transfer_out',
                                'quantity' => -$itemData->quantity,
                                'reference_type' => StockTransfer::class,
                                'reference_id' => $sourceTransfer->id,
                                'note' => "Chi nhánh nhận đã xác nhận",
                                'created_by' => auth()->id(),
                            ]);
                        }
                    }

                    $sourceTransfer->update([
                        'status' => 'completed',
                        'confirmed_by' => auth()->id(),
                        'confirmed_at' => now(),
                    ]);
                } elseif ($action === 'reject') {
                    $sourceTransfer->update([
                        'status' => 'rejected',
                        'reject_reason' => $reason,
                        'cancelled_by' => auth()->id(),
                        'cancelled_at' => now(),
                    ]);
                } elseif ($action === 'revert') {
                    // Cộng lại tồn kho nguồn
                    foreach ($sourceTransfer->items as $itemData) {
                        $sourceProduct = Product::where('code', $itemData->product_code)->first();
                        if ($sourceProduct) {
                            $sourceInventory = Inventory::firstOrCreate(
                                ['product_id' => $sourceProduct->id],
                                ['quantity' => 0]
                            );
                            $sourceInventory->increment('quantity', $itemData->quantity);

                            InventoryTransaction::create([
                                'product_id' => $sourceProduct->id,
                                'type' => 'transfer_in',
                                'quantity' => $itemData->quantity,
                                'reference_type' => StockTransfer::class,
                                'reference_id' => $sourceTransfer->id,
                                'note' => "Hoàn tác xuất chuyển kho",
                                'created_by' => auth()->id(),
                            ]);
                        }
                    }

                    $sourceTransfer->update([
                        'status' => 'reverted',
                        'cancelled_by' => auth()->id(),
                        'cancelled_at' => now(),
                    ]);
                }
            }

            // Gửi thông báo lại cho chi nhánh gửi
            try {
                $senderId = auth()->id();
                $systemMsg = "";
                if ($action === 'confirm') {
                    $systemMsg = "✅ [CHUYỂN KHO] Phiếu {$transfer->transfer_code} đã được chi nhánh nhận XÁC NHẬN thành công.";
                } elseif ($action === 'reject') {
                    $systemMsg = "❌ [CHUYỂN KHO] Phiếu {$transfer->transfer_code} đã bị chi nhánh nhận TỪ CHỐI. Lý do: {$reason}";
                } elseif ($action === 'revert') {
                    $systemMsg = "⚠️ [CHUYỂN KHO] Phiếu {$transfer->transfer_code} đã bị chi nhánh nhận HỦY NHẬP (Hoàn tác).";
                }
                
                if ($systemMsg) {
                    \App\Models\ChatMessage::create([
                        'user_id' => $senderId,
                        'type' => 'system',
                        'content' => $systemMsg,
                        'is_read' => false,
                    ]);
                }
            } catch (\Exception $ex) { }

        } catch (\Exception $e) {
            // Không break transaction nếu lỗi
        }

        Config::set('database.connections.tenant.database', $oldDb);
        DB::purge('tenant');
    }

    public function submitReject()
    {
        $this->validate(['rejectReason' => 'required']);
        $this->rejectTransfer($this->selectedTransferId, $this->rejectReason);
        $this->rejectReason = '';
    }

    public function rejectTransfer($id, $reason)
    {
        $transfer = StockTransfer::find($id);
        if (!$transfer) return;

        if ($transfer->status !== 'pending') {
            session()->flash('error', 'Phiếu này đã được xử lý.');
            return;
        }

        $currentHouse = session('current_house', 1);
        if ($transfer->to_project_id != $currentHouse) {
            session()->flash('error', 'Bạn không có quyền từ chối phiếu này.');
            return;
        }

        try {
            DB::beginTransaction();

            $transfer->update([
                'status' => 'rejected',
                'reject_reason' => $reason,
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
            ]);

            DB::commit();

            $this->updateSourceHouseStatus($transfer, 'reject', $reason);

            session()->flash('success', 'Đã từ chối phiếu chuyển kho.');
            $this->closeDetailModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function revertTransfer($id)
    {
        $transfer = StockTransfer::with('items')->find($id);
        if (!$transfer) return;

        if ($transfer->status !== 'completed') {
            session()->flash('error', 'Chỉ có thể hủy nhập phiếu đã hoàn thành.');
            return;
        }

        $currentHouse = session('current_house', 1);
        if ($transfer->to_project_id != $currentHouse) {
            session()->flash('error', 'Bạn không có quyền hủy nhập phiếu này.');
            return;
        }

        try {
            DB::beginTransaction();

            foreach ($transfer->items as $itemData) {
                $targetProduct = Product::where('code', $itemData->product_code)->first();
                if ($targetProduct) {
                    $targetInventory = Inventory::firstOrCreate(
                        ['product_id' => $targetProduct->id],
                        ['quantity' => 0]
                    );

                    $targetInventory->decrement('quantity', $itemData->quantity);

                    InventoryTransaction::create([
                        'product_id' => $targetProduct->id,
                        'type' => 'transfer_out',
                        'quantity' => -$itemData->quantity,
                        'note' => "Hoàn tác nhận chuyển kho (Phiếu: {$transfer->transfer_code})",
                        'reference_type' => StockTransfer::class,
                        'reference_id' => $transfer->id,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            $transfer->update([
                'status' => 'reverted',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
            ]);

            DB::commit();

            $this->updateSourceHouseStatus($transfer, 'revert');

            session()->flash('success', 'Đã hủy nhập (hoàn tác) thành công.');
            $this->closeDetailModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function printSelected()
    {
        if (empty($this->selectedTransfers)) return;
        $ids = implode(',', array_map('strval', $this->selectedTransfers));
        $url = route('warehouse.stock-transfer.print-bulk', ['ids' => $ids]);
        $this->dispatch('open-print-window', url: $url);
        session()->flash('success', count($this->selectedTransfers) . ' phiếu đã được đưa vào hàng đợi in.');
        $this->selectedTransfers = [];
    }

    public function deleteSelected()
    {
        if (empty($this->selectedTransfers)) return;
        DB::table('stock_transfer_items')->whereIn('stock_transfer_id', $this->selectedTransfers)->delete();
        StockTransfer::whereIn('id', $this->selectedTransfers)->delete();
        session()->flash('success', 'Các phiếu đã được xóa thành công.');
        $this->selectedTransfers = [];
    }

    public function printSingle($id)
    {
        $transfer = StockTransfer::find($id);
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
        $currentHouse = session('current_house', 1);
        $transfers = StockTransfer::with(['creator', 'items.product', 'fromProject', 'toProject'])
            ->where('transfer_code', 'like', '%' . $this->search . '%')
            ->where(function($q) use ($currentHouse) {
                $q->where('from_project_id', $currentHouse)
                  ->orWhere('to_project_id', $currentHouse);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.warehouse.stock-transfer-list', [
            'transfers' => $transfers,
            'currentHouse' => $currentHouse
        ])->layout('components.warehouse-layout', ['title' => 'Lịch sử chuyển kho']);
    }
}
