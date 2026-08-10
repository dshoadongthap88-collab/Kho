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
    public $dateFrom;
    public $dateTo;
    public $printDetailed = false;

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::today()->format('Y-m-d');
    }

    public function getReportDataProperty()
    {
        $start = Carbon::parse($this->dateFrom)->startOfDay();
        $end = Carbon::parse($this->dateTo)->endOfDay();
        
        // 1. Tổng hợp số lượng mã vật tư nhập kho, xuất kho, chuyển kho, thu hồi
        // Nhập kho
        $stockInCount = StockInItem::whereHas('stockIn', function($q) use ($start, $end) {
            $q->whereBetween('stock_in_date', [$start, $end]);
        })->distinct('product_id')->count('product_id');

        // Xuất kho
        $stockOutCount = StockOutItem::whereHas('stockOut', function($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })->distinct('product_id')->count('product_id');

        // Chuyển kho
        $stockTransferCount = StockTransferItem::whereHas('stockTransfer', function($q) use ($start, $end) {
            $q->whereBetween('transfer_date', [$start, $end]);
        })->distinct('product_id')->count('product_id');

        // Thu hồi
        $stockRecoveryCount = StockRecovery::whereBetween('recovery_date', [$start, $end])
            ->distinct('product_id')
            ->count('product_id');

        // 2. Tổng số đơn xuất 1 khoảng thời gian
        $totalStockOutOrders = StockOut::whereBetween('created_at', [$start, $end])->count();

        // 3. Phân loại mã xuất kho (Tài sản vs Vật tư)
        // Mã tài sản xuất kho (asset_code is not null in StockOut)
        $assetExportCount = StockOut::whereBetween('created_at', [$start, $end])
            ->whereNotNull('asset_code')
            ->where('asset_code', '!=', '')
            ->distinct('asset_code')
            ->count('asset_code');

        // Mã vật tư xuất kho
        $materialExportCount = $stockOutCount;

        // Thống kê Nhập Kho chi tiết
        $totalStockInOrders = \App\Models\StockIn::whereBetween('stock_in_date', [$start, $end])->count();
        
        $supplierDeliveryCount = \App\Models\StockIn::whereBetween('stock_in_date', [$start, $end])
            ->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->distinct('supplier_name')
            ->count('supplier_name');

        return [
            'stockInCount' => $stockInCount,
            'stockOutCount' => $stockOutCount,
            'stockTransferCount' => $stockTransferCount,
            'stockRecoveryCount' => $stockRecoveryCount,
            'totalStockOutOrders' => $totalStockOutOrders,
            'assetExportCount' => $assetExportCount,
            'materialExportCount' => $materialExportCount,
            'totalStockInOrders' => $totalStockInOrders,
            'supplierDeliveryCount' => $supplierDeliveryCount,
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
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'detailed' => $this->includeDetailReport ? 1 : 0,
            'zalo' => 1
        ]);

        $this->dispatch('zalo-pdf-generated', url: $url);
    }
}
