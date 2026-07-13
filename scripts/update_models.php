<?php
$baseDir = __DIR__ . '/../app/Models/';
$models = ['MaintenanceBom', 'MaintenanceBomItem', 'MaintenanceTicket', 'MaintenanceItem', 'MaintenanceRule', 'MaintenancePlan', 'AssetOdoReading', 'AssetDailyOdo'];

foreach ($models as $model) {
    $path = $baseDir . $model . '.php';
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Add use BelongsToHouse trait at the top if not present
        if (strpos($content, 'use App\Traits\BelongsToHouse;') === false) {
            $content = str_replace('use Illuminate\Database\Eloquent\Model;', "use App\Traits\BelongsToHouse;\nuse Illuminate\Database\Eloquent\Model;", $content);
        }
        
        // Add trait inside class
        if (strpos($content, 'use HasFactory;') !== false && strpos($content, 'BelongsToHouse') === false) {
            $content = str_replace('use HasFactory;', 'use HasFactory, BelongsToHouse;', $content);
        } else if (strpos($content, 'use HasFactory, SoftDeletes;') !== false && strpos($content, 'BelongsToHouse') === false) {
            $content = str_replace('use HasFactory, SoftDeletes;', 'use HasFactory, SoftDeletes, BelongsToHouse;', $content);
        }

        // Add house_id to fillable
        if (strpos($content, "'house_id'") === false && strpos($content, 'protected $fillable = [') !== false) {
            $content = preg_replace('/protected \$fillable = \[/', "protected \$fillable = [\n        'house_id',", $content);
        }
        
        file_put_contents($path, $content);
        echo $model . " updated.\n";
    } else {
        echo $model . " not found.\n";
    }
}
