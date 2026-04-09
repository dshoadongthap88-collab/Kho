<?php
require __DIR__ . '/../../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

$codes = ['P001', 'P002'];
foreach ($codes as $code) {
    echo "--- Product: $code ---\n";
    $p = Product::where('code', $code)->first();
    if ($p) {
        echo "Catalog - Batch: " . ($p->batch_number ?? 'NULL') . "\n";
        echo "Catalog - Expiry: " . ($p->expiry_date ? $p->expiry_date->format('Y-m-d') : 'NULL') . "\n";
        echo "Catalog - Location: " . ($p->location ?? 'NULL') . "\n";
        
        $batches = InventoryTransaction::where('product_id', $p->id)
            ->select('batch_number', 'expiry_date', 'warehouse_location', DB::raw('SUM(quantity) as stock'))
            ->groupBy('batch_number', 'expiry_date', 'warehouse_location')
            ->having('stock', '>', 0)
            ->get();
            
        echo "Inventory Batches count: " . $batches->count() . "\n";
        foreach ($batches as $b) {
            echo "  - Batch: {$b->batch_number}, Expiry: {$b->expiry_date}, Location: {$b->warehouse_location}, Stock: {$b->stock}\n";
        }
    } else {
        echo "Product not found.\n";
    }
}
