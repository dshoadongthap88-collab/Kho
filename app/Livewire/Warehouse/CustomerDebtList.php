<?php

namespace App\Livewire\Warehouse;

use App\Models\DeliveryReport;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerDebtList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterPayment = ''; 
    
    // Thu nợ Modal
    public $showPayModal = false;
    public $currentReportId;
    public $payAmount;
    public $maxPayAmount;
    public $isEditMode = false;
    public $editDueDate = '';

    public $showStockOutModal = false;
    public $selectedStockOut = null;
    
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedIds = [];
    public $printItems = []; // Danh sách các hóa đơn công nợ để in hàng loạt

    protected $queryString = ['search', 'filterPayment', 'dateFrom', 'dateTo'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function viewStockOutDetails($id)
    {
        $this->selectedStockOut = \App\Models\StockOut::with('items.product', 'creator')->find($id);
        if ($this->selectedStockOut) {
            $this->showStockOutModal = true;
        }
    }

    public function openPayModal($id)
    {
        $this->isEditMode = false;
        $report = DeliveryReport::findOrFail($id);
        $this->currentReportId = $id;
        $this->maxPayAmount = $report->total_amount - $report->paid_amount;
        $this->payAmount = $this->maxPayAmount;
        $this->editDueDate = $report->due_date ?? '';
        $this->showPayModal = true;
    }

    public function openEditModal($id)
    {
        $this->isEditMode = true;
        $report = DeliveryReport::findOrFail($id);
        $this->currentReportId = $id;
        $this->maxPayAmount = $report->total_amount;
        $this->payAmount = floatval($report->paid_amount);
        $this->editDueDate = $report->due_date ?? '';
        $this->showPayModal = true;
    }

    public function markAsFullyPaid($id)
    {
        $report = DeliveryReport::findOrFail($id);
        $report->update([
            'paid_amount' => $report->total_amount,
            'payment_status' => 'paid'
        ]);
        session()->flash('message', 'Đã xác nhận hóa đơn thanh toán thành công!');
    }

    public function receivePayment()
    {
        $this->validate([
            'payAmount' => 'required|numeric|min:0|max:' . $this->maxPayAmount,
        ], [
            'payAmount.required' => 'Vui lòng nhập số tiền.',
            'payAmount.min' => 'Số tiền không hợp lệ.',
            'payAmount.max' => 'Số tiền thu không vượt quá số nợ (' . number_format($this->maxPayAmount) . ')',
        ]);

        $report = DeliveryReport::findOrFail($this->currentReportId);
        
        if ($this->isEditMode) {
            $newPaidAmount = $this->payAmount;
        } else {
            $newPaidAmount = floatval($report->paid_amount) + floatval($this->payAmount);
        }

        $newPaymentStatus = $report->payment_status;

        if ($newPaidAmount >= $report->total_amount) {
            $newPaymentStatus = 'paid';
        } elseif ($newPaidAmount > 0 && $newPaidAmount < $report->total_amount) {
            $newPaymentStatus = 'debt'; // Vẫn giữ/chuyển qua trạng thái nợ nếu chưa đủ
        }

        $report->update([
            'paid_amount' => $newPaidAmount,
            'payment_status' => $newPaymentStatus,
            'due_date' => $this->editDueDate ?: null,
        ]);

        session()->flash('message', $this->isEditMode ? 'Đã cập nhật số dư nợ thành công!' : 'Đã ghi nhận thanh toán ' . number_format($this->payAmount) . ' VNĐ thành công!');
        $this->showPayModal = false;
    }

    public function exportExcel()
    {
        $query = DeliveryReport::with('stockOut')
                    ->whereIn('payment_status', ['debt', 'unpaid', 'paid', 'bank_transfer'])
                    ->where('status', 'delivered');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('stockOut', function($subQ) {
                      $subQ->where('code', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->dateFrom) {
            $query->where('delivered_at', '>=', $this->dateFrom . ' 00:00:00');
        }
        if ($this->dateTo) {
            $query->where('delivered_at', '<=', $this->dateTo . ' 23:59:59');
        }

        $data = $query->latest('delivered_at')->get();
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CustomerDebtExport($data), 'so_no_khach_hang_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function printSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một hóa đơn để in.');
            return;
        }

        $this->printItems = DeliveryReport::whereIn('id', $this->selectedIds)
            ->with(['stockOut.items.product'])
            ->get();

        $this->dispatch('trigger-print');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedIds)) return;
        // Xóa báo cáo giao hàng (con nợ)
        DeliveryReport::whereIn('id', $this->selectedIds)->delete();
        session()->flash('message', 'Đã xóa ' . count($this->selectedIds) . ' bản ghi nợ.');
        $this->selectedIds = [];
    }

    public function toggleSelectAll($idsOnPage)
    {
        if (count($this->selectedIds) >= count($idsOnPage)) {
            $this->selectedIds = [];
        } else {
            $this->selectedIds = collect($idsOnPage)->map(fn($id) => (string)$id)->toArray();
        }
    }

    public function render()
    {
        // Lấy các hóa đơn ĐÃ GIAO (hoặc đang quản lý thanh toán)
        $query = DeliveryReport::with('stockOut', 'stockOut.creator')
                    ->whereIn('payment_status', ['debt', 'unpaid', 'paid', 'bank_transfer'])
                    ->where('status', 'delivered');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('stockOut', function($subQ) {
                      $subQ->where('code', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->dateFrom) {
            $query->where('delivered_at', '>=', $this->dateFrom . ' 00:00:00');
        }
        if ($this->dateTo) {
            $query->where('delivered_at', '<=', $this->dateTo . ' 23:59:59');
        }

        if ($this->filterPayment === 'unpaid_or_debt') {
            // Lọc các hóa đơn có paid_amount < total_amount
            $query->whereRaw('paid_amount < total_amount');
        } elseif ($this->filterPayment === 'paid') {
            $query->whereRaw('paid_amount >= total_amount');
        }

        $debts = $query->latest('delivered_at')->paginate(20);

        // Tự động sửa lỗi dữ liệu (Data Repair) cho các bản ghi cũ bị thiếu số tiền (0)
        foreach($debts as $debt) {
            if ($debt->total_amount <= 0 && $debt->stockOut) {
                $actualTotal = $debt->stockOut->items->sum('total_amount');
                if ($actualTotal > 0) {
                    $debt->update(['total_amount' => $actualTotal]);
                    // Nếu đã thanh toán, cập nhật luôn paid_amount
                    if (in_array($debt->payment_status, ['paid', 'bank_transfer'])) {
                        $debt->update(['paid_amount' => $actualTotal]);
                    }
                }
            }
        }

        return view('livewire.warehouse.customer-debt-list', [
            'debts' => $debts
        ]);
    }
}
