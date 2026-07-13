<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['maintenance_boms', 'maintenance_bom_items', 'maintenance_tickets', 'maintenance_items', 'maintenance_rules', 'maintenance_plans', 'asset_odo_readings', 'asset_daily_odos'];
foreach($tables as $table) {
    try {
        DB::table($table)->whereNull('house_id')->update(['house_id' => 1]);
        echo $table . " updated.\n";
    } catch (\Exception $e) {
        echo $table . " failed: " . $e->getMessage() . "\n";
    }
}
