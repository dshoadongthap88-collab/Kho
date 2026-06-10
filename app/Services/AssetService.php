<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\MaintenanceTicket;
use App\Models\MaintenanceItem;
use App\Models\AssetMeterReading;
use Illuminate\Support\Facades\DB;

class AssetService
{
    public function getAssets(array $filters = [])
    {
        $query = Asset::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['machine_type'])) {
            $query->where('machine_type', $filters['machine_type']);
        }

        return $query->orderBy('asset_code')->paginate(20);
    }

    public function createAsset(array $data): Asset
    {
        return Asset::create($data);
    }

    public function updateAsset(Asset $asset, array $data): Asset
    {
        $asset->update($data);
        return $asset;
    }

    public function getMaintenanceTickets(array $filters = [])
    {
        $query = MaintenanceTicket::with('asset');

        if (!empty($filters['asset_id'])) {
            $query->where('asset_id', $filters['asset_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('maintenance_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('maintenance_date', '<=', $filters['to_date']);
        }

        return $query->orderBy('maintenance_date', 'desc')->paginate(20);
    }

    public function createMaintenanceTicket(array $data): MaintenanceTicket
    {
        return DB::transaction(function () use ($data) {
            $ticket = MaintenanceTicket::create([
                'ticket_code' => $this->generateTicketCode(),
                'asset_id' => $data['asset_id'],
                'maintenance_date' => $data['maintenance_date'] ?? now()->toDateString(),
                'type' => $data['type'] ?? 'repair',
                'description' => $data['description'] ?? null,
                'status' => 'open',
                'created_by' => $data['created_by'],
            ]);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $ticket->items()->create($item);
                }
            }

            return $ticket->load('items', 'asset');
        });
    }

    public function createMeterReading(array $data): AssetMeterReading
    {
        return AssetMeterReading::create($data);
    }

    private function generateTicketCode(): string
    {
        $date = now()->format('Ymd');
        $last = MaintenanceTicket::whereDate('created_at', today())->count() + 1;
        return 'MT-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}