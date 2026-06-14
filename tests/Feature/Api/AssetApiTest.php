<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Asset;
use App\Models\MaintenanceTicket;
use App\Models\AssetMeterReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'code' => 'USR001',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'permissions' => ['*'],
            'status' => 'active',
        ]);
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    private function withAuth(array $headers = []): array
    {
        return array_merge($headers, ['Authorization' => 'Bearer ' . $this->token]);
    }

    public function test_can_list_assets(): void
    {
        Asset::create(['asset_code' => 'TS-001', 'name' => 'Máy phát điện', 'status' => 'active']);
        Asset::create(['asset_code' => 'TS-002', 'name' => 'Xe nâng', 'status' => 'active']);

        $response = $this->getJson('/api/assets', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_create_asset(): void
    {
        $response = $this->postJson('/api/assets', [
            'asset_code' => 'TS-001',
            'name' => 'Máy phát điện',
            'department' => 'Kỹ thuật',
            'machine_type' => 'generator',
            'model' => 'CAT-350',
            'serial_number' => 'SN123456',
            'manufacturer' => 'Caterpillar',
            'installation_date' => '2025-01-15',
            'status' => 'active',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo tài sản thành công']);

        $this->assertDatabaseHas('assets', ['asset_code' => 'TS-001', 'name' => 'Máy phát điện']);
    }

    public function test_can_show_asset(): void
    {
        $asset = Asset::create(['asset_code' => 'TS-001', 'name' => 'Máy phát điện', 'status' => 'active']);

        $response = $this->getJson("/api/assets/{$asset->id}", $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonPath('data.asset_code', 'TS-001');
    }

    public function test_can_update_asset(): void
    {
        $asset = Asset::create(['asset_code' => 'TS-001', 'name' => 'Máy phát điện', 'status' => 'active']);

        $response = $this->putJson("/api/assets/{$asset->id}", [
            'asset_code' => 'TS-001',
            'name' => 'Máy phát điện Cũ',
            'status' => 'maintenance',
        ], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Cập nhật tài sản thành công']);

        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Máy phát điện Cũ']);
    }

    public function test_can_delete_asset(): void
    {
        $asset = Asset::create(['asset_code' => 'TS-001', 'name' => 'Máy phát điện', 'status' => 'active']);

        $response = $this->deleteJson("/api/assets/{$asset->id}", [], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Xóa tài sản thành công']);

        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_can_create_maintenance_ticket(): void
    {
        $asset = Asset::create(['asset_code' => 'TS-001', 'name' => 'Máy phát điện', 'status' => 'active']);

        $response = $this->postJson('/api/assets/maintenance-tickets', [
            'asset_id' => $asset->id,
            'maintenance_date' => '2026-06-09',
            'type' => 'repair',
            'description' => 'Sửa chữa định kỳ',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo phiếu bảo trì thành công']);

        $this->assertDatabaseHas('maintenance_tickets', ['asset_id' => $asset->id, 'type' => 'repair']);
    }

    public function test_can_list_maintenance_tickets(): void
    {
        $asset = Asset::create(['asset_code' => 'TS-001', 'name' => 'Máy phát điện', 'status' => 'active']);
        MaintenanceTicket::create([
            'ticket_code' => 'MT-20260609-0001',
            'asset_id' => $asset->id,
            'maintenance_date' => '2026-06-09',
            'type' => 'repair',
            'status' => 'open',
        ]);
        MaintenanceTicket::create([
            'ticket_code' => 'MT-20260609-0002',
            'asset_id' => $asset->id,
            'maintenance_date' => '2026-06-09',
            'type' => 'maintenance',
            'status' => 'closed',
        ]);

        $response = $this->getJson('/api/assets/maintenance-tickets', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_create_meter_reading(): void
    {
        $asset = Asset::create(['asset_code' => 'TS-001', 'name' => 'Máy phát điện', 'status' => 'active']);

        $response = $this->postJson('/api/assets/meter-readings', [
            'asset_id' => $asset->id,
            'reading_date' => '2026-06-09',
            'odometer_reading' => 15000,
            'engine_hours' => 5000,
            'notes' => 'Đọc chỉ số định kỳ',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo chỉ số đồng hồ thành công']);

        $this->assertDatabaseHas('asset_meter_readings', ['asset_id' => $asset->id]);
    }

    public function test_asset_endpoints_require_auth(): void
    {
        $response = $this->getJson('/api/assets');
        $response->assertStatus(401);
    }
}