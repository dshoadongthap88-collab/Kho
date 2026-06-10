<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'creator']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $transfers->items(),
                'pagination' => [
                    'current_page' => $transfers->currentPage(),
                    'last_page' => $transfers->lastPage(),
                    'total' => $transfers->total(),
                    'per_page' => $transfers->perPage(),
                ],
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.note' => 'nullable|string',
            'note' => 'nullable|string',
            'transfer_date' => 'nullable|date',
        ]);

        $data = $request->only(['from_warehouse_id', 'to_warehouse_id', 'note', 'transfer_date']);
        $data['items'] = $request->input('items', []);
        $data['created_by'] = Auth::id();

        $service = app(StockTransferService::class);
        $transfer = $service->createTransfer($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo phiếu chuyển kho thành công',
            'data' => $transfer,
        ], 201);
    }

    public function show(StockTransfer $transfer)
    {
        $transfer->load('items.product', 'creator', 'fromWarehouse', 'toWarehouse', 'confirmer');

        return response()->json([
            'status' => 'success',
            'data' => $transfer,
        ], 200);
    }

    public function confirm($id)
    {
        try {
            $service = app(StockTransferService::class);
            $transfer = $service->confirmTransfer($id, Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => 'Xác nhận chuyển kho thành công',
                'data' => $transfer,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function cancel($id)
    {
        try {
            $service = app(StockTransferService::class);
            $transfer = $service->cancelTransfer($id, Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => 'Hủy phiếu chuyển kho thành công',
                'data' => $transfer,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}