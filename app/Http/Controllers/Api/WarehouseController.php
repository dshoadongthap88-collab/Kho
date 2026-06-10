<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::query();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if (!empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%")
                    ->orWhere('address', 'like', "%{$request->search}%");
            });
        }

        $warehouses = $query->orderBy('name')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $warehouses->items(),
                'pagination' => [
                    'current_page' => $warehouses->currentPage(),
                    'last_page' => $warehouses->lastPage(),
                    'total' => $warehouses->total(),
                    'per_page' => $warehouses->perPage(),
                ],
            ],
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive,closed',
        ]);

        $warehouse = Warehouse::create(array_merge(
            $request->only(['code', 'name', 'address', 'manager_name', 'phone', 'status']),
            ['created_by' => Auth::id()]
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo chi nhánh kho thành công',
            'data' => $warehouse,
        ], 201);
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load('creator');

        return response()->json([
            'status' => 'success',
            'data' => $warehouse,
        ], 200);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'code' => 'nullable|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive,closed',
        ]);

        $warehouse->update($request->only(['code', 'name', 'address', 'manager_name', 'phone', 'status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật chi nhánh kho thành công',
            'data' => $warehouse,
        ], 200);
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa chi nhánh kho thành công',
        ], 200);
    }
}
