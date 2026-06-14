<?php
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$file = 'D:/ex.vinalpha/BaoCaoNXT_DA_09062026.xlsx';
$spreadsheet = IOFactory::load($file);
$xuatSheet = $spreadsheet->getSheetByName('xuất kho');
$nhapSheet = $spreadsheet->getSheetByName('nhập kho');
$summarySheet = $spreadsheet->getSheetByName('Báo cáo tồn kho tổng hợp');

// Check current xuất kho structure
echo "=== xuất kho row 1 (headers) ===\n";
for ($c = 1; $c <= 10; $c++) {
    $letter = Coordinate::stringFromColumnIndex($c);
    $val = $xuatSheet->getCellByColumnAndRow($c, 1)->getValue();
    if ($val) echo "  {$letter}1: $val\n";
}
echo "\n=== xuất kho row 2 (sample) ===\n";
for ($c = 1; $c <= 10; $c++) {
    $letter = Coordinate::stringFromColumnIndex($c);
    $val = $xuatSheet->getCellByColumnAndRow($c, 2)->getValue();
    if ($val !== null && $val !== '') echo "  {$letter}2: $val\n";
}

echo "\n=== nhập kho row 1 (headers) ===\n";
for ($c = 1; $c <= 10; $c++) {
    $letter = Coordinate::stringFromColumnIndex($c);
    $val = $nhapSheet->getCellByColumnAndRow($c, 1)->getValue();
    if ($val) echo "  {$letter}1: $val\n";
}
