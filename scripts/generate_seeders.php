<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

try {
    $tables = DB::select('SHOW TABLES');
    $exclude = ['migrations', 'failed_jobs', 'password_resets', 'password_reset_tokens', 'personal_access_tokens'];
    $tablesToSeed = [];

    foreach ($tables as $table) {
        $tableArr = (array) $table;
        $tableName = array_values($tableArr)[0];
        if (!in_array($tableName, $exclude)) {
            $tablesToSeed[] = $tableName;
        }
    }

    if (empty($tablesToSeed)) {
        echo "No tables found to seed.\n";
        exit;
    }

    $tablesString = implode(',', $tablesToSeed);
    echo "Generating seeds for: $tablesString\n";

    Artisan::call('iseed', [
        'tables' => $tablesString,
        '--force' => true
    ]);

    echo Artisan::output();
    
    // Update DatabaseSeeder.php to call the generated seeders
    Artisan::call('iseed', ['tables' => $tablesString, '--force' => true]); // this automatically updates DatabaseSeeder if configured, but let's just do it
    echo "Seeders generated successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
