<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Services\CrmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmController extends Controller
{
    public function customers(Request $request)
    {
        $service = app(CrmService::class);
        $data = $service->getCustomers($request->only(['search', 'status']));

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                ],
            ],
        ], 200);
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(CrmService::class);
        $customer = $service->createCustomer($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo khách hàng thành công',
            'data' => $customer,
        ], 201);
    }

    public function showCustomer(Supplier $customer)
    {
        $customer->load('purchaseOrders');

        return response()->json([
            'status' => 'success',
            'data' => $customer,
        ], 200);
    }

    public function updateCustomer(Request $request, Supplier $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $customer->update($request->only(['name', 'address', 'phone', 'contact_person', 'email', 'status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật khách hàng thành công',
            'data' => $customer,
        ], 200);
    }

    public function destroyCustomer(Supplier $customer)
    {
        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa khách hàng thành công',
        ], 200);
    }

    public function suppliers(Request $request)
    {
        $service = app(CrmService::class);
        $data = $service->getSuppliers($request->only(['search', 'status']));

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                ],
            ],
        ], 200);
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $service = app(CrmService::class);
        $supplier = $service->createSupplier($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo nhà cung cấp thành công',
            'data' => $supplier,
        ], 201);
    }

    public function showSupplier(Supplier $supplier)
    {
        $supplier->load('purchaseOrders');

        return response()->json([
            'status' => 'success',
            'data' => $supplier,
        ], 200);
    }

    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $supplier->update($request->only(['name', 'address', 'phone', 'contact_person', 'email', 'status']));

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật nhà cung cấp thành công',
            'data' => $supplier,
        ], 200);
    }

    public function destroySupplier(Supplier $supplier)
    {
        $supplier->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa nhà cung cấp thành công',
        ], 200);
    }

    public function purchaseOrders(Request $request)
    {
        $service = app(CrmService::class);
        $data = $service->getPurchaseOrders($request->only(['supplier_id', 'status', 'from_date', 'to_date']));

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                ],
            ],
        ], 200);
    }

    public function storePurchaseOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $service = app(CrmService::class);
        $po = $service->createPurchaseOrder($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo đơn hàng mua thành công',
            'data' => $po,
        ], 201);
    }

    public function showPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('items.product', 'supplier');

        return response()->json([
            'status' => 'success',
            'data' => $purchaseOrder,
        ], 200);
    }
}