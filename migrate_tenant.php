<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\Config::set('database.connections.tenant.database', 'laravel_5');
\Illuminate\Support\Facades\DB::purge('tenant');
\Illuminate\Support\Facades\Config::set('database.default', 'tenant');

\Illuminate\Support\Facades\Artisan::call('migrate', ['--database' => 'tenant', '--force' => true]);
echo \Illuminate\Support\Facades\Artisan::output();
echo "Migrated laravel_5 successfully.\n";
