<?php

namespace App\Livewire\Warehouse\Reports;

use Livewire\Component;
use App\Models\StockInItem;
use App\Models\StockOutItem;
use App\Models\StockOut;
use App\Models\StockTransferItem;
use App\Models\StockRecovery;
use Carbon\Carbon;

class DailyReport extends Component
{
    public $date;
    public $printDetailed = false;

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
    }

    public function getReportDataProperty()
    {
        $date = Carbon::parse($this->date);
        
        // 1. Tổng hợp số lượng mã vật tư nhập kho, xuất kho, chuyển kho, thu hồi
        // Nhập kho
        $stockInCount = StockInItem::whereHas('stockIn', function($q) use ($date) {
            $q->whereDate('stock_in_date', $date);
        })->distinct('product_id')->count('product_id');

        // Xuất kho
        $stockOutCount = StockOutItem::whereHas('stockOut', function($q) use ($date) {
            $q->whereDate('created_at', $date);
        })->distinct('product_id')->count('product_id');

        // Chuyển kho
        $stockTransferCount = StockTransferItem::whereHas('stockTransfer', function($q) use ($date) {
            $q->whereDate('transfer_date', $date);
        })->distinct('product_id')->count('product_id');

        // Thu hồi
        $stockRecoveryCount = StockRecovery::whereDate('recovery_date', $date)
            ->distinct('product_id')
            ->count('product_id');

        // 2. Tổng số đơn xuất 1 ngày
        $totalStockOutOrders = StockOut::whereDate('created_at', $date)->count();

        // 3. Phân loại mã xuất kho (Tài sản vs Vật tư)
        // Mã tài sản xuất kho (asset_code is not null in StockOut)
        $assetExportCount = StockOut::whereDate('created_at', $date)
            ->whereNotNull('asset_code')
            ->where('asset_code', '!=', '')
            ->distinct('asset_code')
            ->count('asset_code');

        // Mã vật tư xuất kho
        $materialExportCount = $stockOutCount;

        return [
            'stockInCount' => $stockInCount,
            'stockOutCount' => $stockOutCount,
            'stockTransferCount' => $stockTransferCount,
            'stockRecoveryCount' => $stockRecoveryCount,
            'totalStockOutOrders' => $totalStockOutOrders,
            'assetExportCount' => $assetExportCount,
            'materialExportCount' => $materialExportCount,
        ];
    }

    public function render()
    {
        return view('livewire.warehouse.reports.daily-report', [
            'reportData' => $this->reportData
        ]);
    }
    // Zalo selection
    public $includeDailyReport = true;
    public $includeDetailReport = true;

    public function generateZaloMessage()
    {
        if (!$this->includeDailyReport && !$this->includeDetailReport) {
            session()->flash('error', 'Vui lòng chọn ít nhất 1 loại báo cáo để gửi Zalo.');
            return;
        }

        $url = route('warehouse.reports.daily.print', [
            'date' => $this->date,
            'detailed' => $this->includeDetailReport ? 1 : 0,
            'zalo' => 1
        ]);

        $this->dispatch('zalo-pdf-generated', url: $url);
    }
}
