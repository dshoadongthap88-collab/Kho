<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$databases = ['laravel_2', 'laravel_3', 'laravel_4', 'laravel_5'];

foreach ($databases as $db) {
    echo "Migrating $db...\n";
    \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $db);
    \Illuminate\Support\Facades\DB::purge('tenant');
    \Illuminate\Support\Facades\Config::set('database.default', 'tenant');

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--database' => 'tenant', '--force' => true]);
        echo \Illuminate\Support\Facades\Artisan::output();
        echo "Migrated $db successfully.\n\n";
    } catch (Exception $e) {
        echo "Error migrating $db: " . $e->getMessage() . "\n\n";
    }
}
