<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $import = new App\Imports\InventoryImport();
    $rows = collect([
        ['STT', 'Mã SP', 'Tên sản phẩm', 'Số lượng', 'Vị trí'],
        ['1', 'SP9999', 'Sản phẩm Test', '50', 'Kho A']
    ]);
    $import->collection($rows);
    $p = App\Models\Product::where('code', 'SP9999')->first();
    echo "Created: " . ($p ? $p->name : 'No') . "\n";
    $inv = App\Models\Inventory::where('product_id', $p->id ?? 0)->first();
    echo "Quantity: " . ($inv ? $inv->quantity : 'No') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
