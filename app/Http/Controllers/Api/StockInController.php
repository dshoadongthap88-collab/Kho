<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    /**
     * Danh sách phiếu nhập
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 20);
        $stockIns = StockIn::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $stockIns->items(),
                'pagination' => [
                    'current_page' => $stockIns->currentPage(),
                    'last_page' => $stockIns->lastPage(),
                    'total' => $stockIns->total(),
                    'per_page' => $stockIns->perPage()
                ]
            ]
        ], 200);
    }

    /**
     * Tạo mới Phiếu nhập
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'nullable|string',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'sometimes|exists:products,id',
            'items.*.item_code' => 'sometimes|string',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.warehouse_location' => 'nullable|string',
        ]);

        $service = app(InventoryService::class);

        try {
            DB::beginTransaction();

            $date = date('Ymd');
            $count = StockIn::count() + 1;
            
            $stockIn = StockIn::create([
                'code' => 'SI-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
                'supplier_name' => $request->input('supplier_name'),
                'type' => 'import_material', // Mặc định từ Mobile
                'status' => 'completed',
                'note' => $request->input('note'),
                'created_by' => $request->user()->id ?? null,
            ]);

            $receiptCode = $stockIn->code;

            foreach ($request->items as $itemData) {
                // Ưu tiên tìm theo product_id, nếu không thì lấy theo mã item_code
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
                $price = $itemData['price'] ?? ($product->price ?? 0);
                $batchNumber = $itemData['batch_number'] ?? '';
                $warehouseLocation = $itemData['warehouse_location'] ?? ($product->location ?? '');

                // 1. Tạo chi tiết phiếu nhập
                StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $product->id,
                    'batch_number' => $batchNumber,
                    'warehouse_location' => $warehouseLocation,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'vat_rate' => 0,
                    'total_amount' => $quantity * $price,
                ]);

                // 2. Cập nhật Inventory & Tạo Transaction (Gọi thông qua service chung để chuẩn DB)
                $service->import(
                    $product->id,
                    $quantity,
                    'stock_in', // type of transaction
                    $stockIn->id, // reference_id
                    $request->input('note') ?? 'Nhập kho từ Mobile App',
                    $batchNumber,
                    null, // expiry date
                    $warehouseLocation
                );

                // 3. Cập nhật vị trí mặc định cho sản phẩm (nếu thiếu)
                if ($warehouseLocation && !$product->location) {
                    $product->update(['location' => $warehouseLocation]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Tạo phiếu nhập kho thành công',
                'data' => [
                    'receipt_code' => $receiptCode,
                    'stock_in_id' => $stockIn->id
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gặp lỗi trong quá trình tạo phiếu nhập: ' . $e->getMessage()
            ], 400);
        }
    }
}
