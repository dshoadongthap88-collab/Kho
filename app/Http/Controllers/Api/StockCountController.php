<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockCount;
use App\Services\StockCountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockCountController extends Controller
{
    public function index(Request $request)
    {
        $query = StockCount::with('creator');
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $counts = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $counts->items(),
                'pagination' => [
                    'current_page' => $counts->currentPage(),
                    'last_page' => $counts->lastPage(),
                    'total' => $counts->total(),
                    'per_page' => $counts->perPage(),
                ],
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|in:monthly,daily',
            'note' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'location' => 'nullable|string',
        ]);

        $data = $request->only(['type', 'note', 'category_id', 'product_ids', 'location']);
        $data['created_by'] = Auth::id();

        $service = app(StockCountService::class);
        $count = $service->createCount($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo phiếu kiểm kê thành công',
            'data' => $count,
        ], 201);
    }

    public function show(StockCount $count)
    {
        $count->load('items.product', 'creator');

        return response()->json([
            'status' => 'success',
            'data' => $count,
        ], 200);
    }

    public function submitDaily(Request $request)
    {
        $request->validate([
            'count_code' => 'required|string|exists:stock_counts,code',
            'entries' => 'required|array|min:1',
            'entries.*.product_id' => 'required|integer',
            'entries.*.actual_quantity' => 'required|numeric|min:0',
        ]);

        $service = app(StockCountService::class);
        $count = $service->submitDailyCount($request->count_code, $request->entries, Auth::id());

        return response()->json([
            'status' => 'success',
            'message' => 'Gửi kết quả kiểm kê thành công',
            'data' => $count,
        ], 200);
    }

    public function getTodayDaily()
    {
        $service = app(StockCountService::class);
        $count = $service->getTodayDailyItems();

        return response()->json([
            'status' => 'success',
            'data' => $count,
        ], 200);
    }

    public function finalize($id)
    {
        $service = app(StockCountService::class);
        $count = $service->finalizeCount($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Hoàn thành kiểm kê thành công. Tồn kho đã được cập nhật.',
            'data' => $count,
        ], 200);
    }
}
