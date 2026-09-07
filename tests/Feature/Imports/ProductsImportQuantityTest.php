<?php

namespace Tests\Feature\Imports;

use App\Imports\ProductsImport;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductsImportQuantityTest extends TestCase
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

    public function test_import_excel_updates_inventory_quantity_for_existing_material(): void
    {
        $product = Product::create([
            'code' => '0708091050',
            'name' => 'Hóc môn',
            'unit' => 'Cái',
            'status' => 'active',
            'type' => 'material',
            'house_id' => 1,
        ]);

        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 0,
            'warehouse_location' => 'KHO CŨ',
            'house_id' => 1,
        ]);

        $import = new ProductsImport();
        $import->collection(collect([
            ['Mã vật tư', 'Tên vật tư', 'ĐVT', 'Vị trí', 'Tồn kho'],
            ['0708091050', 'Hóc môn', 'Cái', 'KHO MỚI', 25],
        ]));

        $product->refresh();
        $product->load('inventory');

        $this->assertSame('KHO MỚI', $product->inventory->warehouse_location);
        $this->assertSame(25.0, (float) $product->inventory->quantity);
    }
}
