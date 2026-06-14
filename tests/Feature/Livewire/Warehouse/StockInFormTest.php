<?php

namespace Tests\Feature\Livewire\Warehouse;

use App\Livewire\Warehouse\StockInForm;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StockInFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_stock_in_form()
    {
        $user = User::factory()->create(['code' => 'U' . uniqid()]);
        $this->actingAs($user);

        Livewire::test(StockInForm::class)
            ->assertStatus(200);
    }

    public function test_can_add_item_and_save_stock_in()
    {
        $user = User::factory()->create(['code' => 'U' . uniqid()]);
        $this->actingAs($user);

        $product = Product::create([
            'code' => 'TEST01',
            'name' => 'Test Product',
            'unit' => 'Cái',
            'price' => 10000,
            'status' => 'active'
        ]);

        $component = Livewire::test(StockInForm::class)
            ->set('supplier_name', 'Nhà cung cấp A')
            ->set('type', 'import_material')
            ->set('items.0.product_id', $product->id)
            ->set('items.0.product_search', 'TEST01 - Test Product')
            ->set('items.0.quantity', 5)
            ->set('items.0.unit_price', 10000)
            ->call('save')
            ->assertHasNoErrors();
            
        // dump($component->errors());
        $component->assertSessionHas('success');

        // Verify StockIn was created
        $stockIn = StockIn::first();
        $this->assertNotNull($stockIn);
        $this->assertEquals('Nhà cung cấp A', $stockIn->supplier_name);
        $this->assertEquals('import_material', $stockIn->type);

        // Verify InventoryTransaction was created
        $transaction = InventoryTransaction::where('product_id', $product->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('import', $transaction->type);
        $this->assertEquals(5, $transaction->quantity);

        // Verify Inventory was updated
        $inventory = Inventory::where('product_id', $product->id)->first();
        $this->assertNotNull($inventory);
        $this->assertEquals(5, $inventory->quantity);
    }
}
