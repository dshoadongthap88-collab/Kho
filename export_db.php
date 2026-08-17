<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
$sql = "";

foreach ($tables as $table) {
    // Skip views or migration table if needed, but let's include everything
    if ($table == 'sqlite_sequence') continue;
    
    // Get table creation
    // To keep it simple, we only export data for now, or we can use SHOW CREATE TABLE
    try {
        $createTable = DB::select("SHOW CREATE TABLE `$table`");
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
    } catch (\Exception $e) {
        // Fallback for sqlite or others if SHOW CREATE TABLE is not supported
    }

    $rows = DB::table($table)->get();
    foreach ($rows as $row) {
        $rowArr = (array) $row;
        $keys = array_keys($rowArr);
        $values = array_values($rowArr);
        
        $escapedValues = array_map(function($value) {
            if (is_null($value)) {
                return 'NULL';
            }
            if (is_numeric($value) && !is_string($value)) { // handle numbers but strings that look like numbers should be quoted
                return $value;
            }
            // Fix JSON data formatting by ensuring properly escaped strings
            $value = str_replace(['\\', "'", "\r", "\n"], ['\\\\', "''", "\\r", "\\n"], (string)$value);
            return "'$value'";
        }, $values);

        $keysString = implode('`, `', $keys);
        $valuesString = implode(', ', $escapedValues);
        
        $sql .= "INSERT INTO `$table` (`$keysString`) VALUES ($valuesString);\n";
    }
    $sql .= "\n";
}

file_put_contents('database/backup_fixed_json.sql', $sql);
echo "Exported to database/backup_fixed_json.sql\n";
