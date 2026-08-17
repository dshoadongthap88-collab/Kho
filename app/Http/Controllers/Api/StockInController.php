<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\StockInItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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

    /**
     * Nhập kho thông minh bằng file Excel/CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $service = app(InventoryService::class);

        try {
            DB::beginTransaction();

            $data = Excel::toArray(new \stdClass(), $request->file('file'));
            if (empty($data) || empty($data[0])) {
                throw new \Exception("File không có dữ liệu hợp lệ.");
            }

            $rows = $data[0];
            if (count($rows) < 2) {
                throw new \Exception("File cần có ít nhất 1 dòng tiêu đề và 1 dòng dữ liệu.");
            }

            // Quét 15 dòng đầu tìm dòng tiêu đề
            $headerRowIndex = 0;
            $headerMap = [];
            
            for ($i = 0; $i < min(15, count($rows)); $i++) {
                $foundMapping = false;
                $tempMap = [];
                foreach ($rows[$i] as $colIndex => $cellValue) {
                    $cell = mb_strtolower(trim($cellValue ?? ''));
                    if (preg_match('/mã.*vật.*tư|mã.*hàng|mã/i', $cell)) {
                        $tempMap['code'] = $colIndex;
                        $foundMapping = true;
                    } elseif (preg_match('/tên.*vật.*tư|tên.*hàng|tên/i', $cell)) {
                        $tempMap['name'] = $colIndex;
                        $foundMapping = true;
                    } elseif (preg_match('/đơn.*vị|đvt/i', $cell)) {
                        $tempMap['unit'] = $colIndex;
                    } elseif (preg_match('/số.*lượng|sl/i', $cell)) {
                        $tempMap['quantity'] = $colIndex;
                    } elseif (preg_match('/đơn.*giá|giá/i', $cell)) {
                        $tempMap['price'] = $colIndex;
                    } elseif (preg_match('/nhãn.*hiệu|hãng/i', $cell)) {
                        $tempMap['brand'] = $colIndex;
                    }
                }
                if ($foundMapping && isset($tempMap['code'])) {
                    $headerRowIndex = $i;
                    $headerMap = $tempMap;
                    break;
                }
            }

            if (empty($headerMap) || !isset($headerMap['code'])) {
                throw new \Exception("Không thể tự động nhận diện các cột dữ liệu (cần ít nhất cột 'Mã').");
            }

            // Khởi tạo phiếu nhập
            $date = date('Ymd');
            $count = StockIn::count() + 1;
            
            $stockIn = StockIn::create([
                'code' => 'SI-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT),
                'supplier_name' => $request->input('supplier_name', 'Nhập thông minh qua File'),
                'type' => 'import_material',
                'status' => 'completed',
                'note' => $request->input('note', 'Nhập kho thông minh từ file Excel/CSV'),
                'created_by' => $request->user()->id ?? null,
            ]);

            // Duyệt dữ liệu
            $importedCount = 0;
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $itemCode = isset($headerMap['code']) ? trim($row[$headerMap['code']] ?? '') : '';
                if (empty($itemCode)) continue;

                $itemName = isset($headerMap['name']) ? trim($row[$headerMap['name']] ?? '') : $itemCode;
                $quantity = isset($headerMap['quantity']) ? (float) ($row[$headerMap['quantity']] ?? 0) : 0;
                $unit = isset($headerMap['unit']) ? trim($row[$headerMap['unit']] ?? '') : 'CAI';
                $price = isset($headerMap['price']) ? (float) ($row[$headerMap['price']] ?? 0) : 0;
                $brand = isset($headerMap['brand']) ? trim($row[$headerMap['brand']] ?? '') : null;

                if ($quantity <= 0) continue;

                // Tự động tìm hoặc thêm mới
                $product = Product::where('code', $itemCode)->first();
                if (!$product) {
                    $product = Product::create([
                        'code' => $itemCode,
                        'name' => $itemName,
                        'unit' => $unit,
                        'brand' => $brand,
                        'price' => $price > 0 ? $price : 0,
                        'status' => 'active',
                        'type' => 'material'
                    ]);
                }

                StockInItem::create([
                    'stock_in_id' => $stockIn->id,
                    'product_id' => $product->id,
                    'batch_number' => '',
                    'warehouse_location' => $product->location ?? '',
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'vat_rate' => 0,
                    'total_amount' => $quantity * $price,
                ]);

                $service->import(
                    $product->id,
                    $quantity,
                    'stock_in',
                    $stockIn->id,
                    'Nhập kho thông minh qua file',
                    '',
                    null,
                    $product->location ?? ''
                );

                $importedCount++;
            }

            if ($importedCount == 0) {
                throw new \Exception("Không tìm thấy dữ liệu hợp lệ để nhập kho.");
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Đã nhập kho thông minh thành công $importedCount dòng dữ liệu.",
                'data' => [
                    'receipt_code' => $stockIn->code,
                    'stock_in_id' => $stockIn->id
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi import file: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Xem chi tiết Phiếu nhập
     */
    public function show($id)
    {
        $stockIn = StockIn::with(['creator', 'items.product'])->find($id);

        if (!$stockIn) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy phiếu nhập.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $stockIn
        ], 200);
    }

    /**
     * Cập nhật Phiếu nhập
     */
    public function update(Request $request, $id)
    {
        $stockIn = StockIn::find($id);

        if (!$stockIn) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy phiếu nhập.'
            ], 404);
        }

        $request->validate([
            'supplier_name' => 'nullable|string',
            'note' => 'nullable|string',
            'status' => 'nullable|string'
        ]);

        $stockIn->update($request->only(['supplier_name', 'note', 'status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật phiếu nhập thành công.',
            'data' => $stockIn
        ], 200);
    }

    /**
     * Xóa Phiếu nhập (Thường chỉ cho phép xóa khi phiếu ở trạng thái draft/pending hoặc tuỳ logic nghiệp vụ)
     */
    public function destroy($id)
    {
        $stockIn = StockIn::find($id);

        if (!$stockIn) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy phiếu nhập.'
            ], 404);
        }

        // Logic check nếu phiếu đã hoàn thành thì không cho xóa (tùy nghiệp vụ)
        if ($stockIn->status === 'completed') {
             // Tuỳ vào quy trình, nếu cần revert kho thì phải gọi InventoryService
             // Ở đây mình tạm thời không cho xoá phiếu đã completed hoặc sẽ implement revert sau
             return response()->json([
                 'status' => 'error',
                 'message' => 'Không thể xoá phiếu nhập đã hoàn thành. Hãy dùng tính năng xuất trả/điều chỉnh.'
             ], 400);
        }

        DB::beginTransaction();
        try {
            $stockIn->items()->delete();
            $stockIn->delete();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Xoá phiếu nhập thành công.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi xoá phiếu nhập: ' . $e->getMessage()
            ], 400);
        }
    }
}
