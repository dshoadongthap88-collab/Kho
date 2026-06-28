<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetDailyOdo;
use App\Models\MaintenanceTicket;
use App\Services\MaintenanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceLogicTest extends TestCase
{
    use RefreshDatabase;

    protected MaintenanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MaintenanceService();
    }

    public function test_shift_log_updates_asset_current_hours()
    {
        $asset = Asset::create([
            'asset_code' => 'TEST-001',
            'name' => 'Test Asset',
            'status' => 'active',
            'current_hours' => 100,
            'current_odo' => 0,
            'hours_per_shift' => 8,
            'maintenance_cycle_hours' => 250,
            'last_maintenance_hours' => 0,
        ]);

        $this->service->logDailyShifts($asset, 2, '2026-06-22'); // 2 shifts = 16 hours

        $asset->refresh();
        $this->assertEquals(116, $asset->current_hours);

        $this->assertDatabaseHas('asset_daily_odos', [
            'asset_id' => $asset->id,
            'shifts_count' => 2,
            'hours_diff' => 16,
        ]);
    }

    public function test_maintenance_ticket_auto_generated_when_cycle_reached()
    {
        $asset = Asset::create([
            'asset_code' => 'TEST-002',
            'name' => 'Test Asset 2',
            'status' => 'active',
            'current_hours' => 240,
            'current_odo' => 0,
            'hours_per_shift' => 8,
            'maintenance_cycle_hours' => 250,
            'last_maintenance_hours' => 0,
        ]);

        // Logging 2 shifts (16 hours). New hours = 256. 256 - 0 >= 250 => Should trigger.
        $this->service->logDailyShifts($asset, 2, '2026-06-22');

        $this->assertDatabaseHas('maintenance_tickets', [
            'asset_id' => $asset->id,
            'status' => 'pending',
            'type' => 'auto_generated',
        ]);
    }

    public function test_completing_ticket_updates_last_maintenance_markers()
    {
        $asset = Asset::create([
            'asset_code' => 'TEST-003',
            'name' => 'Test Asset 3',
            'status' => 'active',
            'current_hours' => 256,
            'current_odo' => 1000,
            'hours_per_shift' => 8,
            'maintenance_cycle_hours' => 250,
            'last_maintenance_hours' => 0,
            'last_maintenance_odo' => 0,
        ]);

        $ticket = MaintenanceTicket::create([
            'ticket_code' => 'MT-TEST',
            'asset_id' => $asset->id,
            'maintenance_date' => '2026-06-22',
            'status' => 'pending',
        ]);

        $this->service->completeMaintenanceTicket(
            $ticket,
            '2026-06-23',
            'Thay dầu máy',
            ['Thay lọc dầu', 'Thay dầu 15W40'],
            2500000
        );

        $ticket->refresh();
        $this->assertEquals('completed', $ticket->status);
        $this->assertEquals(2500000, $ticket->total_cost);

        $asset->refresh();
        $this->assertEquals(256, $asset->last_maintenance_hours);
        $this->assertEquals(1000, $asset->last_maintenance_odo);
    }
}
