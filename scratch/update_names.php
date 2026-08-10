<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(__DIR__.'/../docs/TON.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
array_shift($rows);

$count = 0;
foreach ($rows as $row) {
    $code = trim((string)$row[1]);
    $name = trim((string)$row[2]);
    if ($code && $name) {
        $updated = \App\Models\Product::withoutGlobalScopes()
            ->where('code', $code)
            ->where('name', 'like', 'Sản phẩm %')
            ->update(['name' => $name]);
        $count += $updated;
    }
}
echo "Updated $count products\n";
