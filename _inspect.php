<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (DB::select('DESCRIBE stock_transfers') as $r) {
    echo $r->Field . PHP_EOL;
}
