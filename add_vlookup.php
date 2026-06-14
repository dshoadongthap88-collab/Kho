<?php
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$file = 'D:/ex.vinalpha/BaoCaoNXT_DA_09062026.xlsx';
$spreadsheet = IOFactory::load($file);
$xuatSheet = $spreadsheet->getSheetByName('xuất kho');
$summarySheet = $spreadsheet->getSheetByName('Báo cáo tồn kho tổng hợp');

// Verify summary sheet structure
echo "=== Summary row 7 (headers) ===\n";
for ($c = 1; $c <= 15; $c++) {
    $letter = Coordinate::stringFromColumnIndex($c);
    $val = $summarySheet->getCellByColumnAndRow($c, 7)->getValue();
    if ($val) echo "  {$letter}7: $val\n";
}
echo "\n=== Summary row 8 (sub-headers) ===\n";
for ($c = 1; $c <= 15; $c++) {
    $letter = Coordinate::stringFromColumnIndex($c);
    $val = $summarySheet->getCellByColumnAndRow($c, 8)->getValue();
    if ($val) echo "  {$letter}8: $val\n";
}

// Summary column mapping (confirmed from previous run):
// A=Stt, B=Mã VT, C=Tên VT, D=Nhãn hiệu, E=Đvt, F=Định mức, G=Đơn trọng, H=Tổng SL
// I=Tồn đầu kỳ, J=Nhập, K=Xuất, L=Tồn cuối kỳ (total columns)
// M=Tồn đầu (project), N=Nhập (project), O=Xuất (project), P=Tồn cuối (project)
// Q=next project col
// Vị trí kho is in column I (Đơn trọng)? No, let me check what's in column I for data rows
echo "\n=== Summary row 10 (first data row) ===\n";
for ($c = 1; $c <= 16; $c++) {
    $letter = Coordinate::stringFromColumnIndex($c);
    $val = $summarySheet->getCellByColumnAndRow($c, 10)->getValue();
    if ($val !== null && $val !== '') echo "  {$letter}10: $val\n";
}

// xuất kho structure: A=stt, B=Ngày Nhập, C=Mã VT, D=tên VT, E=đvt, F=số lượng, G=tên ncc
// We need to add VLOOKUP in D (tên VT), E (đvt), and a new column for vị trí

// First, insert a new column for "Vị trí kho" after column E (đvt)
// Current: A, B, C, D, E, F, G
// After insert at F: A, B, C, D, E, F(new), G(old F), H(old G)
$xuatSheet->insertNewColumnBefore('F', 1);

// Update header row 1
$xuatSheet->setCellValueByColumnAndRow(6, 1, 'Vị trí kho');

// Now columns are: A=stt, B=Ngày Nhập, C=Mã VT, D=tên VT, E=đvt, F=Vị trí, G=số lượng, H=tên ncc

// Add VLOOKUP formulas for rows 2 onward
$highestRow = $xuatSheet->getHighestRow();
$summaryRef = "'Báo cáo tồn kho tổng hợp'!\$B:\$I";

$count = 0;
for ($row = 2; $row <= $highestRow; $row++) {
    // D (col 4) = Tên VT: VLOOKUP from summary col E (5th col in B:I range)
    $xuatSheet->setCellValueByColumnAndRow(4, $row, "=IFERROR(VLOOKUP(C{$row},{$summaryRef},4,FALSE),\"\")");

    // E (col 5) = Đvt: VLOOKUP from summary col E (5th col in B:I range)
    $xuatSheet->setCellValueByColumnAndRow(5, $row, "=IFERROR(VLOOKUP(C{$row},{$summaryRef},5,FALSE),\"\")");

    // F (col 6) = Vị trí: VLOOKUP from summary col I (9th col in B:I range, which is col 8 in 1-indexed from B)
    // B=1, C=2, D=3, E=4, F(định mức)=5, G(đơn trọng)=6, H(tổng SL)=7, I(tồn đầu)=8
    // Actually the vị trí is warehouse_location from the inventory table, which maps to column I (Đơn trọng in the header?)
    // Let me use column I (9th column from A, which is index 8 from B)
    $xuatSheet->setCellValueByColumnAndRow(6, $row, "=IFERROR(VLOOKUP(C{$row},{$summaryRef},8,FALSE),\"\")");

    $count++;
}
echo "Added VLOOKUP formulas for $count rows\n";

// Add data validation for Mã VT column (C) - list from summary sheet product codes
// Collect product codes from summary
$summaryHighestRow = $summarySheet->getHighestRow();
$productCodes = [];
for ($r = 10; $r <= $summaryHighestRow; $r++) {
    $code = $summarySheet->getCellByColumnAndRow(2, $r)->getValue();
    if ($code && $code !== 'Tổng cộng' && !empty(trim($code))) {
        $productCodes[] = (string)trim($code);
    }
}
echo "Product codes for validation: " . count($productCodes) . "\n";

// Create data validation with product code list
$validation = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
$validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
$validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
$validation->setAllowBlank(true);
$validation->setShowInputMessage(true);
$validation->setShowErrorMessage(true);
$validation->setShowDropDown(true);
$validation->setPrompt('Chọn mã vật tư từ danh sách');
$validation->setError('Mã vật tư không hợp lệ');
$validation->setFormula1("'" . implode(',', array_slice($productCodes, 0, 100)) . "'");

// Apply validation to C2:C1000 (allow plenty of rows for future entries)
$xuatSheet->setDataValidation('C2:C1000', $validation);
echo "Added data validation for Mã VT column\n";

// Also add VLOOKUP to nhập kho sheet for consistency
$nhapSheet = $spreadsheet->getSheetByName('nhập kho');
$nhapHighestRow = $nhapSheet->getHighestRow();

for ($row = 2; $row <= $nhapHighestRow; $row++) {
    // D (col 4) = Tên VT
    $nhapSheet->setCellValueByColumnAndRow(4, $row, "=IFERROR(VLOOKUP(C{$row},{$summaryRef},4,FALSE),\"\")");
    // E (col 5) = Đvt
    $nhapSheet->setCellValueByColumnAndRow(5, $row, "=IFERROR(VLOOKUP(C{$row},{$summaryRef},5,FALSE),\"\")");
    // F (col 6) = Vị trí
    $nhapSheet->setCellValueByColumnAndRow(6, $row, "=IFERROR(VLOOKUP(C{$row},{$summaryRef},8,FALSE),\"\")");
}
echo "Added VLOOKUP formulas to nhập kho sheet ($nhapHighestRow rows)\n";

// Save
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($file);
echo "\nSaved: $file\n";

// Verify
echo "\n=== xuất kho row 2 (formulas) ===\n";
$sh = $xuatSheet;
echo "C2 (Mã VT): " . $sh->getCellByColumnAndRow(3, 2)->getValue() . "\n";
echo "D2 (Tên VT): " . $sh->getCellByColumnAndRow(4, 2)->getValue() . "\n";
echo "E2 (Đvt): " . $sh->getCellByColumnAndRow(5, 2)->getValue() . "\n";
echo "F2 (Vị trí): " . $sh->getCellByColumnAndRow(6, 2)->getValue() . "\n";
echo "G2 (SL): " . $sh->getCellByColumnAndRow(7, 2)->getValue() . "\n";
echo "H2 (NCC): " . $sh->getCellByColumnAndRow(8, 2)->getValue() . "\n";

echo "\n=== nhập kho row 2 (formulas) ===\n";
$sh2 = $nhapSheet;
echo "C2 (Mã VT): " . $sh2->getCellByColumnAndRow(3, 2)->getValue() . "\n";
echo "D2 (Tên VT): " . $sh2->getCellByColumnAndRow(4, 2)->getValue() . "\n";
echo "E2 (Đvt): " . $sh2->getCellByColumnAndRow(5, 2)->getValue() . "\n";
echo "F2 (Vị trí): " . $sh2->getCellByColumnAndRow(6, 2)->getValue() . "\n";
