<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Trả về dữ liệu tổng quan cho trang chủ của Mobile App
     */
    public function summary()
    {
        $today = Carbon::today();

        // Thống kê lượng phiếu nhập/xuất trong ngày
        // (Sau này có thể nâng cấp thành tính tổng giá trị tiền hoặc số lượng item)
        $totalImportToday = StockIn::whereDate('created_at', $today)->count();
        $totalExportToday = StockOut::whereDate('created_at', $today)->count();

        // Lấy danh sách sản phẩm cấu hình tồn kho tối thiểu
        // Lọc qua Collection vì logic getIsLowStockAttribute nằm ở code PHP lấy từ relation inventory
        $products = Product::with('inventory')->get();
        $lowStockItemsCount = $products->filter(function ($product) {
            return $product->is_low_stock == true;
        })->count();

        // Đếm số lượng đơn mua hàng (Purchase Order) đang chờ xử lý
        // Tuỳ theo trạng thái của hệ thống của bạn (ví dụ: 'pending', 'new')
        $pendingPurchaseOrders = PurchaseOrder::where('status', 'pending')
                                                ->orWhere('status', 'Chờ duyệt') // Support tiếng Việt nếu có
                                                ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_import_today' => $totalImportToday,
                'total_export_today' => $totalExportToday,
                'low_stock_items_count' => $lowStockItemsCount,
                'pending_purchase_orders' => $pendingPurchaseOrders
            ]
        ], 200);
    }
}
