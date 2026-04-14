<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockOut;
use App\Models\StockOutItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    /**
     * Danh sách phiếu xuất
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);
        $stockOuts = StockOut::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $stockOuts->items(),
                'pagination' => [
                    'current_page' => $stockOuts->currentPage(),
                    'last_page' => $stockOuts->lastPage(),
                    'total' => $stockOuts->total(),
                    'per_page' => $stockOuts->perPage()
                ]
            ]
        ], 200);
    }

    /**
     * Tạo mới Phiếu xuất kho
     */
    public function store(Request $request)
    {
        $request->validate([
            'purpose' => 'nullable|string',
            'production_order_code' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'sometimes|exists:products,id',
            'items.*.item_code' => 'sometimes|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.batch_number' => 'nullable|string',
            'items.*.warehouse_location' => 'nullable|string',
        ]);

        $service = app(InventoryService::class);

        try {
            DB::beginTransaction();

            // Khởi tạo thông tin xuất
            $date = date('Ymd');
            $count = StockOut::count() + 1;
            
            $note = $request->input('purpose');
            if ($request->input('production_order_code')) {
                $note = "Cho lệnh sản xuất: " . $request->input('production_order_code') . " - " . $note;
            }

            $stockOut = StockOut::create([
                'code' => 'SO-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
                'customer_name' => 'Xuất nội bộ/Sản xuất (Mobile)', // Default for mobile
                'type' => 'production', 
                'status' => 'completed',
                'note' => $note,
                'created_by' => $request->user()->id ?? null,
            ]);

            foreach ($request->items as $itemData) {
                // Xác định Product
                $product = null;
                if (!empty($itemData['product_id'])) {
                    $product = Product::find($itemData['product_id']);
                } elseif (!empty($itemData['item_code'])) {
                    $product = Product::where('code', $itemData['item_code'])->first();
                }

                if (!$product) {
                    throw new \Exception("Không tìm thấy sản phẩm có mã: " . ($itemData['item_code'] ?? 'N/A'));
                }

                $quantity = $itemData['quantity'];
                
                // Kiểm tra Validation kho trực tiếp qua Service
                // (InventoryService::export() thường sẽ throw lỗi nếu ko đủ tồn kho)
                
                $batchNumber = $itemData['batch_number'] ?? '';
                $warehouseLocation = $itemData['warehouse_location'] ?? ($product->location ?? '');

                // 1. Lưu detail
                StockOutItem::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id' => $product->id,
                    'batch_number' => $batchNumber,
                    'warehouse_location' => $warehouseLocation,
                    'quantity' => $quantity,
                    'unit_price' => $product->price ?? 0,
                    'vat_rate' => 0,
                    'total_amount' => $quantity * ($product->price ?? 0),
                ]);

                // 2. Xuất kho qua InventoryService để đảm bảo an toàn & Transaction History
                $service->export(
                    $product->id,
                    $quantity,
                    'stock_out',
                    $stockOut->id,
                    $note,
                    $batchNumber,
                    null,
                    $warehouseLocation
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Tạo phiếu xuất kho thành công',
                'data' => [
                    'receipt_code' => $stockOut->code,
                    'stock_out_id' => $stockOut->id
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tạo phiếu xuất: ' . $e->getMessage()
            ], 400);
        }
    }
}
