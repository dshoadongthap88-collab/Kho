<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Warehouse\ProductCatalog;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductCatalogEditTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
        session(['current_house' => 1]);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function test_existing_code_can_be_kept_while_other_material_fields_are_saved(): void
    {
        $product = Product::create([
            'code' => '0708091050',
            'name' => 'Hóc môn',
            'status' => 'active',
            'type' => 'material',
            'house_id' => 1,
        ]);

        $component = new ProductCatalog();
        $component->activeTab = 'materials';
        $component->isEdit = true;
        $component->productId = $product->id;
        $component->code = '0708091050';
        $component->name = 'Hóc môn đã sửa';
        $component->status = 'active';
        $component->type = 'product_purchased';
        $component->min_stock = 50;
        $component->max_stock = 300;
        $component->save();

        $product->refresh();

        $this->assertSame('0708091050', $product->code);
        $this->assertSame('Hóc môn đã sửa', $product->name);
        $this->assertSame(50.0, (float) $product->min_stock);
        $this->assertSame(300.0, (float) $product->max_stock);
    }
}
