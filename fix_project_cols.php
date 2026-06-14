<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$file = 'D:/ex.vinalpha/BaoCaoNXT_DA_09062026.xlsx';
$spreadsheet = IOFactory::load($file);
$summarySheet = $spreadsheet->getSheetByName('Báo cáo tồn kho tổng hợp');

$highestRow = $summarySheet->getHighestRow();
echo "Data rows: 10 to $highestRow\n";

// Project columns: M=Nhập(13), N=Xuất(14), O=Tồn cuối(15)
// Add SUMIF formulas linking to detail sheets for project columns too
$count = 0;
for ($row = 10; $row <= $highestRow; $row++) {
    $code = $summarySheet->getCellByColumnAndRow(2, $row)->getValue();
    if (!$code || $code === 'Tổng cộng' || empty(trim($code))) {
        continue;
    }

    // Project Nhập (M = col 13)
    $summarySheet->setCellValueByColumnAndRow(13, $row, "=SUMIF('nhập kho'!\$C:\$C,B{$row},'nhập kho'!\$F:\$F)");
    // Project Xuất (N = col 14)
    $summarySheet->setCellValueByColumnAndRow(14, $row, "=SUMIF('xuất kho'!\$C:\$C,B{$row},'xuất kho'!\$F:\$F)");
    // Project Tồn cuối (O = col 15): Tồn đầu (L) + Nhập (M) - Xuất (N)
    $summarySheet->setCellValueByColumnAndRow(15, $row, "=L{$row}+M{$row}-N{$row}");
    $count++;
}
echo "Added project column formulas for $count products\n";

// Save
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($file);
echo "Saved: $file\n";

// Verify
echo "\n=== Row 10 sample (all columns) ===\n";
$sh = $summarySheet;
for ($c = 1; $c <= 15; $c++) {
    $letter = Coordinate::stringFromColumnIndex($c);
    $val = $sh->getCellByColumnAndRow($c, 10)->getValue();
    if ($val !== null && $val !== '') echo "  {$letter}10: $val\n";
}
