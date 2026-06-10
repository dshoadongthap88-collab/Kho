<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\MaintenanceTicket;
use App\Models\AssetMeterReading;
use App\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $service = app(AssetService::class);
        $data = $service->getAssets($request->only(['search', 'department', 'status', 'machine_type']));

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

    public function store(Request $request)
    {
        $request->validate([
            'asset_code' => 'required|string|max:50|unique:assets,asset_code',
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'machine_type' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,maintenance,retired',
        ]);

        $service = app(AssetService::class);
        $asset = $service->createAsset($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo tài sản thành công',
            'data' => $asset,
        ], 201);
    }

    public function show(Asset $asset)
    {
        $asset->load('oilBoms', 'meterReadings', 'maintenanceTickets');

        return response()->json([
            'status' => 'success',
            'data' => $asset,
        ], 200);
    }

    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'asset_code' => 'required|string|max:50|unique:assets,asset_code,' . $asset->id,
            'name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'machine_type' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'installation_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,maintenance,retired',
        ]);

        $service = app(AssetService::class);
        $asset = $service->updateAsset($asset, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật tài sản thành công',
            'data' => $asset,
        ], 200);
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Xóa tài sản thành công',
        ], 200);
    }

    public function maintenanceTickets(Request $request)
    {
        $service = app(AssetService::class);
        $data = $service->getMaintenanceTickets($request->only(['asset_id', 'status', 'type', 'from_date', 'to_date']));

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

    public function storeMaintenanceTicket(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_date' => 'nullable|date',
            'type' => 'required|in:repair,maintenance,inspection',
            'description' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.asset_oil_bom_id' => 'required|exists:asset_oil_boms,id',
            'items.*.suggested_qty' => 'required|numeric|min:0.0001',
            'items.*.actual_qty' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        $service = app(AssetService::class);
        $ticket = $service->createMaintenanceTicket($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo phiếu bảo trì thành công',
            'data' => $ticket,
        ], 201);
    }

    public function storeMeterReading(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'reading_date' => 'required|date',
            'odometer_reading' => 'nullable|numeric|min:0',
            'engine_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $service = app(AssetService::class);
        $reading = $service->createMeterReading([
            'asset_id'     => $request->asset_id,
            'reading_date' => $request->reading_date,
            'reading_value' => $request->odometer_reading ?? $request->engine_hours,
            'user_id'      => Auth::id(),
            'note'         => $request->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tạo chỉ số đồng hồ thành công',
            'data' => $reading,
        ], 201);
    }
}