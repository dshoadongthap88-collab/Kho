<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$data = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), __DIR__.'/docs/import_hang_hoa.csv');
print_r($data);
