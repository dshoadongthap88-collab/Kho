<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Lấy danh sách hàng hóa và số lượng tồn kho hiện tại
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $query = Product::with('inventory')
            ->where('status', 'active');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(20);

        // Map lại dữ liệu trả về cho Mobile gọn nhẹ hơn
        $items = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'item_code' => $product->code,
                'name' => $product->name,
                'unit' => $product->unit,
                'current_stock' => $product->inventory ? floatval($product->inventory->quantity) : 0,
                'warehouse_location' => $product->location
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $items,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'total' => $products->total(),
                    'per_page' => $products->perPage()
                ]
            ]
        ], 200);
    }

    /**
     * Lấy lịch sử giao dịch (thẻ kho) của 1 sản phẩm
     */
    public function history($id)
    {
        // Có thể truyền vào Product ID hoặc Product Code
        $product = Product::where('id', $id)->orWhere('code', $id)->first();

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
        }

        $transactions = InventoryTransaction::where('product_id', $product->id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(30);

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => [
                    'item_code' => $product->code,
                    'name' => $product->name,
                    'current_stock' => $product->inventory ? floatval($product->inventory->quantity) : 0,
                ],
                'history' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'total' => $transactions->total()
                ]
            ]
        ], 200);
    }
}
