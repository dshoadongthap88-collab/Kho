<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

try {
    $tables = DB::select('SHOW TABLES');
    $exclude = ['migrations', 'failed_jobs', 'password_resets', 'password_reset_tokens', 'personal_access_tokens'];
    
    $databaseSeederCalls = [];

    foreach ($tables as $table) {
        $tableArr = (array) $table;
        $tableName = array_values($tableArr)[0];
        
        if (in_array($tableName, $exclude)) {
            continue;
        }

        $className = Str::studly($tableName) . 'TableSeeder';
        $rows = DB::table($tableName)->get();
        
        if ($rows->isEmpty()) {
            continue;
        }
        
        $insertData = "[\n";
        foreach ($rows as $row) {
            $insertData .= "            [\n";
            foreach ((array)$row as $col => $val) {
                if ($val === null) {
                    $insertData .= "                '$col' => null,\n";
                } else {
                    $valEscaped = addslashes((string)$val);
                    $insertData .= "                '$col' => '$valEscaped',\n";
                }
            }
            $insertData .= "            ],\n";
        }
        $insertData .= "        ]";

        $seederContent = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse Illuminate\\Support\\Facades\\DB;\n\nclass $className extends Seeder\n{\n    public function run()\n    {\n        DB::table('$tableName')->truncate();\n        \n        \$data = $insertData;\n        \n        foreach(array_chunk(\$data, 100) as \$chunk) {\n            DB::table('$tableName')->insert(\$chunk);\n        }\n    }\n}\n";
        
        file_put_contents(__DIR__ . '/../database/seeders/' . $className . '.php', $seederContent);
        echo "Created seeder: $className\n";
        
        $databaseSeederCalls[] = "        \$this->call($className::class);";
    }

    echo "Finished creating individual seeders.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
