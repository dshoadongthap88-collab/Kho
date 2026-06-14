<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsApiTest extends TestCase
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

    // ==================== DASHBOARD ====================

    public function test_can_get_dashboard_summary(): void
    {
        Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);

        $response = $this->getJson('/api/reports/warehouse/dashboard', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure([
                'data' => [
                    'total_warehouses',
                    'total_products',
                    'total_stock_value',
                    'total_import_today',
                    'total_export_today',
                    'total_transfer_today',
                    'pending_transfers',
                    'pending_stock_counts',
                ],
            ]);
    }

    // ==================== ALL WAREHOUSES REPORT ====================

    public function test_can_get_all_warehouses_report(): void
    {
        $wh = Warehouse::create(['code' => 'WH1', 'name' => 'Kho Hóc Môn', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);
        Inventory::create(['product_id' => $product->id, 'quantity' => 5, 'warehouse_location' => $wh->name]);

        $response = $this->getJson('/api/reports/warehouse/all', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name', 'total_products', 'total_quantity', 'total_value'],
                ],
            ]);
    }

    public function test_can_filter_warehouses_report_by_status(): void
    {
        Warehouse::create(['code' => 'WH1', 'name' => 'Kho Active', 'status' => 'active']);
        Warehouse::create(['code' => 'WH2', 'name' => 'Kho Inactive', 'status' => 'inactive']);

        $response = $this->getJson('/api/reports/warehouse/all?status=active', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(1, 'data');
    }

    // ==================== INVENTORY BY WAREHOUSE ====================

    public function test_can_get_inventory_by_warehouse(): void
    {
        $wh = Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);
        Inventory::create(['product_id' => $product->id, 'quantity' => 50, 'warehouse_location' => $wh->name]);

        $response = $this->getJson('/api/reports/warehouse/' . $wh->id . '/inventory', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(1, 'data.items');
    }

    public function test_can_filter_inventory_by_search(): void
    {
        $wh = Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);
        Inventory::create(['product_id' => $product->id, 'quantity' => 50, 'warehouse_location' => $wh->name]);

        $response = $this->getJson('/api/reports/warehouse/' . $wh->id . '/inventory?search=VT001', $this->withAuth());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items');
    }

    // ==================== STOCK MOVEMENTS ====================

    public function test_can_get_stock_movements(): void
    {
        $wh = Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);

        InventoryTransaction::create([
            'product_id' => $product->id,
            'type' => 'import',
            'quantity' => 100,
            'transaction_date' => now(),
            'warehouse_location' => $wh->name,
            'created_by' => $this->user->id,
            'note' => 'Nhập kho',
        ]);

        $response = $this->getJson('/api/reports/warehouse/movements', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(1, 'data.items');
    }

    public function test_can_filter_stock_movements_by_type(): void
    {
        $wh = Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);

        InventoryTransaction::create([
            'product_id' => $product->id,
            'type' => 'import',
            'quantity' => 100,
            'transaction_date' => now(),
            'warehouse_location' => $wh->name,
            'created_by' => $this->user->id,
            'note' => 'Nhập kho',
        ]);
        InventoryTransaction::create([
            'product_id' => $product->id,
            'type' => 'export',
            'quantity' => -50,
            'transaction_date' => now(),
            'warehouse_location' => $wh->name,
            'created_by' => $this->user->id,
            'note' => 'Xuất kho',
        ]);

        $response = $this->getJson('/api/reports/warehouse/movements?type=import', $this->withAuth());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.type', 'import');
    }

    // ==================== MONTHLY STATS ====================

    public function test_can_get_monthly_stats(): void
    {
        $wh = Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);

        InventoryTransaction::create([
            'product_id' => $product->id,
            'type' => 'import',
            'quantity' => 100,
            'transaction_date' => now(),
            'warehouse_location' => $wh->name,
            'created_by' => $this->user->id,
            'note' => 'Nhập kho',
        ]);

        $response = $this->getJson('/api/reports/warehouse/monthly-stats', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(12, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['month', 'month_name', 'import_quantity', 'export_quantity'],
                ],
            ]);
    }

    // ==================== AUTH ====================

    public function test_report_endpoints_require_auth(): void
    {
        $response = $this->getJson('/api/reports/warehouse/dashboard');
        $response->assertStatus(401);
    }
}