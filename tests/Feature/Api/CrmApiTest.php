<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmApiTest extends TestCase
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

    public function test_can_list_customers(): void
    {
        Supplier::create(['name' => 'Khách A', 'type' => 'customer', 'status' => 'active']);
        Supplier::create(['name' => 'Khách B', 'type' => 'customer', 'status' => 'active']);

        $response = $this->getJson('/api/crm/customers', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_create_customer(): void
    {
        $response = $this->postJson('/api/crm/customers', [
            'name' => 'Công ty ABC',
            'address' => '123 Đường XYZ',
            'phone' => '0909123456',
            'contact_person' => 'Nguyễn Văn A',
            'email' => 'abc@example.com',
            'status' => 'active',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo khách hàng thành công']);

        $this->assertDatabaseHas('suppliers', ['name' => 'Công ty ABC', 'type' => 'customer']);
    }

    public function test_can_show_customer(): void
    {
        $customer = Supplier::create(['name' => 'Khách A', 'type' => 'customer', 'status' => 'active']);

        $response = $this->getJson("/api/crm/customers/{$customer->id}", $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonPath('data.name', 'Khách A');
    }

    public function test_can_update_customer(): void
    {
        $customer = Supplier::create(['name' => 'Khách Cũ', 'type' => 'customer', 'status' => 'active']);

        $response = $this->putJson("/api/crm/customers/{$customer->id}", [
            'name' => 'Khách Mới',
            'status' => 'inactive',
        ], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Cập nhật khách hàng thành công']);

        $this->assertDatabaseHas('suppliers', ['id' => $customer->id, 'name' => 'Khách Mới']);
    }

    public function test_can_delete_customer(): void
    {
        $customer = Supplier::create(['name' => 'Khách Xóa', 'type' => 'customer', 'status' => 'active']);

        $response = $this->deleteJson("/api/crm/customers/{$customer->id}", [], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Xóa khách hàng thành công']);

        $this->assertSoftDeleted('suppliers', ['id' => $customer->id]);
    }

    public function test_can_list_suppliers(): void
    {
        Supplier::create(['name' => 'NCC A', 'type' => 'supplier', 'status' => 'active']);
        Supplier::create(['name' => 'NCC B', 'type' => 'supplier', 'status' => 'active']);

        $response = $this->getJson('/api/crm/suppliers', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_create_supplier(): void
    {
        $response = $this->postJson('/api/crm/suppliers', [
            'name' => 'Công ty XYZ',
            'address' => '456 Đường ABC',
            'phone' => '0909988776',
            'contact_person' => 'Trần Văn B',
            'email' => 'xyz@example.com',
            'status' => 'active',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo nhà cung cấp thành công']);

        $this->assertDatabaseHas('suppliers', ['name' => 'Công ty XYZ', 'type' => 'supplier']);
    }

    public function test_can_show_supplier(): void
    {
        $supplier = Supplier::create(['name' => 'NCC A', 'type' => 'supplier', 'status' => 'active']);

        $response = $this->getJson("/api/crm/suppliers/{$supplier->id}", $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonPath('data.name', 'NCC A');
    }

    public function test_can_update_supplier(): void
    {
        $supplier = Supplier::create(['name' => 'NCC Cũ', 'type' => 'supplier', 'status' => 'active']);

        $response = $this->putJson("/api/crm/suppliers/{$supplier->id}", [
            'name' => 'NCC Mới',
            'status' => 'inactive',
        ], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Cập nhật nhà cung cấp thành công']);

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'NCC Mới']);
    }

    public function test_can_delete_supplier(): void
    {
        $supplier = Supplier::create(['name' => 'NCC Xóa', 'type' => 'supplier', 'status' => 'active']);

        $response = $this->deleteJson("/api/crm/suppliers/{$supplier->id}", [], $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Xóa nhà cung cấp thành công']);

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    public function test_can_create_purchase_order(): void
    {
        $supplier = Supplier::create(['name' => 'NCC A', 'type' => 'supplier', 'status' => 'active']);
        $product = Product::create(['code' => 'VT001', 'name' => 'Vật tư A', 'unit' => 'cái', 'price' => 1000, 'status' => 'active']);

        $response = $this->postJson('/api/crm/purchase-orders', [
            'supplier_id' => $supplier->id,
            'order_date' => '2026-06-09',
            'expected_delivery_date' => '2026-06-15',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 1000],
            ],
            'notes' => 'Đơn hàng thử',
        ], $this->withAuth());

        $response->assertStatus(201)
            ->assertJson(['status' => 'success', 'message' => 'Tạo đơn hàng mua thành công']);

        $this->assertDatabaseHas('purchase_orders', ['supplier_id' => $supplier->id, 'status' => 'pending']);
        $this->assertDatabaseCount('purchase_order_items', 1);
    }

    public function test_can_list_purchase_orders(): void
    {
        $supplier = Supplier::create(['name' => 'NCC A', 'type' => 'supplier', 'status' => 'active']);
        PurchaseOrder::create([
            'po_number' => 'PO-20260609-0001',
            'supplier_id' => $supplier->id,
            'order_date' => '2026-06-09',
            'expected_delivery_date' => '2026-06-15',
            'total_amount' => 5000,
            'status' => 'pending',
        ]);
        PurchaseOrder::create([
            'po_number' => 'PO-20260609-0002',
            'supplier_id' => $supplier->id,
            'order_date' => '2026-06-09',
            'expected_delivery_date' => '2026-06-15',
            'total_amount' => 3000,
            'status' => 'confirmed',
        ]);

        $response = $this->getJson('/api/crm/purchase-orders', $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data.items');
    }

    public function test_can_show_purchase_order(): void
    {
        $supplier = Supplier::create(['name' => 'NCC A', 'type' => 'supplier', 'status' => 'active']);
        $po = PurchaseOrder::create([
            'po_number' => 'PO-20260609-0001',
            'supplier_id' => $supplier->id,
            'order_date' => '2026-06-09',
            'expected_delivery_date' => '2026-06-15',
            'total_amount' => 5000,
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/crm/purchase-orders/{$po->id}", $this->withAuth());

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonPath('data.po_number', 'PO-20260609-0001');
    }

    public function test_crm_endpoints_require_auth(): void
    {
        $response = $this->getJson('/api/crm/customers');
        $response->assertStatus(401);
    }
}