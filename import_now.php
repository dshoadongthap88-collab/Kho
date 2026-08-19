<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Maatwebsite\Excel\Facades\Excel;
use App\Imports\InventoryImport;

try {
    Excel::import(new InventoryImport(), __DIR__.'/docs/import_hang_hoa.csv');
    echo "Import success!\n";
    $product = \App\Models\Product::where('code', 'VAP2023')->first();
    echo "DB Updated:\n";
    echo "Mã: " . $product->code . "\n";
    echo "Tên: " . $product->name . "\n";
    echo "Đơn vị tính: " . $product->unit . "\n";
    echo "Loại: " . $product->type . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
