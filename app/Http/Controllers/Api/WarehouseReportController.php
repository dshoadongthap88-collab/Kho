<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WarehouseReportService;
use Illuminate\Http\Request;

class WarehouseReportController extends Controller
{
    public function __construct(protected WarehouseReportService $reportService)
    {
    }

    public function dashboard()
    {
        $summary = $this->reportService->getDashboardSummary();

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ], 200);
    }

    public function allWarehouses(Request $request)
    {
        $filters = $request->only(['status', 'search']);
        $report = $this->reportService->getAllWarehousesReport($filters);

        return response()->json([
            'status' => 'success',
            'data' => $report,
        ], 200);
    }

    public function inventoryByWarehouse(Request $request, $warehouseId)
    {
        $filters = $request->only(['search', 'status']);
        $inventory = $this->reportService->getInventoryByWarehouse($warehouseId, $filters);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $inventory->items(),
                'pagination' => [
                    'current_page' => $inventory->currentPage(),
                    'last_page' => $inventory->lastPage(),
                    'total' => $inventory->total(),
                    'per_page' => $inventory->perPage(),
                ],
            ],
        ], 200);
    }

    public function stockMovements(Request $request)
    {
        $filters = $request->only(['type', 'product_id', 'warehouse_location', 'date_from', 'date_to', 'search']);
        $movements = $this->reportService->getStockMovementsReport($filters);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $movements->items(),
                'pagination' => [
                    'current_page' => $movements->currentPage(),
                    'last_page' => $movements->lastPage(),
                    'total' => $movements->total(),
                    'per_page' => $movements->perPage(),
                ],
            ],
        ], 200);
    }

    public function monthlyStats()
    {
        $stats = $this->reportService->getMonthlyStats();

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ], 200);
    }
}
