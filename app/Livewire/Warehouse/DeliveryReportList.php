<?php

namespace App\Livewire\Warehouse;

use App\Models\DeliveryReport;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class DeliveryReportList extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $filterStatus = '';

    // Modal state
    public $showConfirmModal = false;
    public $selectedReportId;
    public $paymentStatus = 'paid';
    public $photo;
    public $notes = '';

    protected $queryString = ['search', 'filterStatus'];

    protected $rules = [
        'paymentStatus' => 'required|in:unpaid,debt,paid,bank_transfer',
        'photo' => 'nullable|image|max:5120', // Tối đa 5MB
        'notes' => 'nullable|string|max:1000',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openConfirmModal($id)
    {
        $this->resetValidation();
        $this->reset(['photo', 'notes']);
        $this->selectedReportId = $id;

        $report = DeliveryReport::findOrFail($id);
        $this->paymentStatus = ($report->payment_status !== 'unpaid' && $report->payment_status !== 'pending') ? $report->payment_status : 'paid';
        if ($report->notes) {
            $this->notes = $report->notes;
        }

        $this->showConfirmModal = true;
    }

    public function saveCompletion()
    {
        $this->validate();

        $report = DeliveryReport::with('stockOut.items')->findOrFail($this->selectedReportId);

        $photoPath = $report->photo_path;
        if ($this->photo) {
            $photoPath = $this->photo->store('deliveries', 'public');
        }

        // Luôn tính toán lại tổng tiền từ StockOut để đảm bảo chính xác
        $calculatedTotal = $report->stockOut ? $report->stockOut->items->sum('total_amount') : 0;
        
        $updateData = [
            'status' => 'delivered',
            'payment_status' => $this->paymentStatus,
            'photo_path' => $photoPath,
            'notes' => $this->notes,
            'delivered_at' => now(),
            'total_amount' => $calculatedTotal, // Cập nhật/Ghi đè số tiền thực tế
        ];

        // Nếu xác nhận là Đã thanh toán (Tiền mặt/Chuyển khoản) thì coi như đã trả đủ
        if (in_array($this->paymentStatus, ['paid', 'bank_transfer'])) {
            $updateData['paid_amount'] = $calculatedTotal;
        }

        $report->update($updateData);

        // Xử lý cộng công nợ nếu payment_status là 'debt'
        if ($this->paymentStatus === 'debt' && $report->stockOut) {
            // Tính tổng tiền phiếu xuất
            $totalAmount = $report->stockOut->items->sum('total_amount');
            
            // Tìm khách hàng theo customer_name
            // Lưu ý: customer_name có thể thừa chuỗi "(Phòng ban)". Ta tách chuỗi đầu tiên
            $rawName = explode(' (', $report->customer_name)[0];
            
            $customer = \App\Models\Supplier::where('name', trim($rawName))->where('type', 'customer')->first();
            if (!$customer) {
                // Thử tìm theo like
                $customer = \App\Models\Supplier::where('name', 'like', '%' . trim($rawName) . '%')->first();
            }

            if ($customer) {
                // Cộng dồn
                $customer->total_debt += $totalAmount;
                $customer->save();
            }
        }

        session()->flash('message', 'Xác nhận hoàn tất giao hàng thành công!');
        $this->showConfirmModal = false;
    }

    public function render()
    {
        $query = DeliveryReport::with('stockOut');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('stockOut', function($subQ) {
                      $subQ->where('code', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Ưu tiên hiện các đơn "pending" và "delivering" lên đầu (kèm nháy đỏ)
        $reports = $query->orderByRaw("FIELD(status, 'pending', 'delivering', 'delivered', 'cancelled')")
                         ->latest()
                         ->paginate(15);

        return view('livewire.warehouse.delivery-report-list', [
            'reports' => $reports,
        ]);
    }
}
