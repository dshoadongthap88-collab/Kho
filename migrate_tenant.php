<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$databases = ['laravel_2', 'laravel_3', 'laravel_4', 'laravel_5'];

foreach ($databases as $db) {
    \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $db);
    \Illuminate\Support\Facades\DB::purge('tenant');
    \Illuminate\Support\Facades\Config::set('database.default', 'tenant');

    \Illuminate\Support\Facades\Artisan::call('migrate', ['--database' => 'tenant', '--force' => true]);
    echo "--- Output for $db ---\n";
    echo \Illuminate\Support\Facades\Artisan::output();
    echo "Migrated $db successfully.\n\n";
}
