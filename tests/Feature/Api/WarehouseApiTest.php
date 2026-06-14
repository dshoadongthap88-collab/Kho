<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockCount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseApiTest extends TestCase
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

    public function test_can_create_warehouse(): void
    {
        $response = $this->postJson('/api/warehouses', [
            'code' => 'WH001',
            'name' => 'Kho Hóc Môn',
            'address' => '123 Đường ABC',
            'manager_name' => 'Nguyễn Văn A',
            'phone' => '0909123456',
            'status' => 'active',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo chi nhánh kho thành công'])
            ->assertJsonStructure(['data' => ['id', 'code', 'name', 'address', 'status']]);

        $this->assertDatabaseHas('warehouses', ['code' => 'WH001', 'name' => 'Kho Hóc Môn']);
    }

    public function test_can_list_warehouses(): void
    {
        Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        Warehouse::create(['code' => 'WH2', 'name' => 'Kho 2', 'status' => 'active']);

        $response = $this->getJson('/api/warehouses', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_update_warehouse(): void
    {
        $wh = Warehouse::create(['code' => 'WH1', 'name' => 'Kho Cũ', 'status' => 'active']);

        $response = $this->putJson("/api/warehouses/{$wh->id}", [
            'name' => 'Kho Mới',
            'status' => 'inactive',
        ], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Cập nhật chi nhánh kho thành công']);

        $this->assertDatabaseHas('warehouses', ['id' => $wh->id, 'name' => 'Kho Mới']);
    }

    public function test_can_delete_warehouse(): void
    {
        $wh = Warehouse::create(['code' => 'WH1', 'name' => 'Kho Xóa', 'status' => 'active']);

        $response = $this->deleteJson("/api/warehouses/{$wh->id}", [], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Xóa chi nhánh kho thành công']);

        $this->assertSoftDeleted('warehouses', ['id' => $wh->id]);
    }

    public function test_can_create_stock_transfer(): void
    {
        $from = Warehouse::create(['code' => 'WH1', 'name' => 'Kho Gửi', 'status' => 'active']);
        $to = Warehouse::create(['code' => 'WH2', 'name' => 'Kho Nhận', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'items' => [['product_id' => $product->id, 'quantity' => 10]],
            'note' => 'Chuyển thử nghiệm',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo phiếu chuyển kho thành công']);

        $this->assertDatabaseHas('stock_transfers', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'status' => 'pending',
        ]);
    }

    public function test_stock_transfer_requires_different_warehouses(): void
    {
        $wh = Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);

        $response = $this->postJson('/api/stock-transfers', [
            'from_warehouse_id' => $wh->id,
            'to_warehouse_id' => $wh->id,
            'items' => [['product_id' => $product->id, 'quantity' => 10]],
        ], $this->withAuth());

        $response->assertStatus(422)
            ->assertJsonValidationErrors('to_warehouse_id');
    }

    public function test_can_confirm_stock_transfer(): void
    {
        $from = Warehouse::create(['code' => 'WH1', 'name' => 'Kho Gửi', 'status' => 'active']);
        $to = Warehouse::create(['code' => 'WH2', 'name' => 'Kho Nhận', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);
        Inventory::create(['product_id' => $product->id, 'quantity' => 100, 'warehouse_location' => $from->name]);

        $transfer = StockTransfer::create([
            'transfer_code' => 'ST-20260101-0001',
            'transfer_date' => date('Y-m-d'),
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);
        StockTransferItem::create([
            'stock_transfer_id' => $transfer->id,
            'product_id' => $product->id,
            'product_code' => $product->code,
            'product_name' => $product->name,
            'quantity' => 10,
            'unit' => $product->unit,
        ]);

        $response = $this->postJson("/api/stock-transfers/{$transfer->id}/confirm", [], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Xác nhận chuyển kho thành công']);

        $this->assertDatabaseHas('stock_transfers', ['id' => $transfer->id, 'status' => 'completed']);
    }

    public function test_cannot_confirm_completed_transfer(): void
    {
        $from = Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        $to = Warehouse::create(['code' => 'WH2', 'name' => 'Kho 2', 'status' => 'active']);

        $transfer = StockTransfer::create([
            'transfer_code' => 'ST-20260101-0001',
            'transfer_date' => date('Y-m-d'),
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'status' => 'completed',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/stock-transfers/{$transfer->id}/confirm", [], $this->withAuth());

        $response->assertStatus(400)
            ->assertJson(['status' => 'error', 'message' => 'Phiếu chuyển kho đã được xử lý.']);
    }

    public function test_can_cancel_stock_transfer(): void
    {
        $from = Warehouse::create(['code' => 'WH1', 'name' => 'Kho 1', 'status' => 'active']);
        $to = Warehouse::create(['code' => 'WH2', 'name' => 'Kho 2', 'status' => 'active']);

        $transfer = StockTransfer::create([
            'transfer_code' => 'ST-20260101-0001',
            'transfer_date' => date('Y-m-d'),
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'status' => 'pending',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson("/api/stock-transfers/{$transfer->id}/cancel", [], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Hủy phiếu chuyển kho thành công']);

        $this->assertDatabaseHas('stock_transfers', ['id' => $transfer->id, 'status' => 'cancelled']);
    }

    public function test_can_create_stock_count(): void
    {
        Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);
        Product::create(['code' => 'VT002', 'name' => 'Vật tư B', 'unit' => 'hộp', 'price' => 2000, 'status' => 'active']);

        $response = $this->postJson('/api/stock-counts', [
            'type' => 'monthly',
            'note' => 'Kiểm kê tháng 6',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo phiếu kiểm kê thành công']);

        $this->assertDatabaseHas('stock_counts', ['type' => 'monthly']);
        $this->assertDatabaseCount('stock_count_items', 2);
    }

    public function test_can_list_stock_counts(): void
    {
        StockCount::create(['code' => 'SK-20260101-0001', 'type' => 'monthly', 'status' => 'in_progress', 'created_by' => $this->user->id]);
        StockCount::create(['code' => 'SK-20260101-0002', 'type' => 'monthly', 'status' => 'completed', 'created_by' => $this->user->id]);

        $response = $this->getJson('/api/stock-counts', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

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

    public function test_warehouse_endpoints_require_auth(): void
    {
        $response = $this->getJson('/api/warehouses');
        $response->assertStatus(401);
    }
}