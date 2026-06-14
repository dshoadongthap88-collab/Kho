<?php
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Table\Table as ExcelTable;

$file = 'D:/ex.vinalpha/BaoCaoNXT_DA_09062026.xlsx';
$spreadsheet = IOFactory::load($file);
$summarySheet = $spreadsheet->getSheetByName('Báo cáo tồn kho tổng hợp');
$nhapSheet = $spreadsheet->getSheetByName('nhập kho');
$xuatSheet = $spreadsheet->getSheetByName('xuất kho');

// STEP 1: Excel Tables for auto-expand
$nhapHighestRow = $nhapSheet->getHighestRow();
$nhapTable = new ExcelTable('nhap_kho_table', $nhapSheet, "A1:H{$nhapHighestRow}");
$nhapTable->setShowHeaderRow(true);
$nhapTable->setShowRowStripes(true);
$nhapSheet->addTable($nhapTable);
echo "Table nhap: A1:H{$nhapHighestRow}\n";

$xuatHighestRow = $xuatSheet->getHighestRow();
$xuatTable = new ExcelTable('xuat_kho_table', $xuatSheet, "A1:H{$xuatHighestRow}");
$xuatTable->setShowHeaderRow(true);
$xuatTable->setShowRowStripes(true);
$xuatSheet->addTable($xuatTable);
echo "Table xuat: A1:H{$xuatHighestRow}\n";

// STEP 2: Create kiểm kho sheet
$kiemKhoSheet = $spreadsheet->getSheetByName('kiem kho');
if ($kiemKhoSheet) {
    $idx = array_search($kiemKhoSheet, $spreadsheet->getAllSheets());
    $spreadsheet->removeSheetByIndex($idx);
}
$kiemKhoSheet = $spreadsheet->createSheet();
$kiemKhoSheet->setTitle('kiem kho');

$headers = ['STT','Ngay kiem','Vi tri','Ma vat tu','Ten vat tu','Dvt','Ton he thong','Ton thuc te','Chenh lech','Ghi chu'];
foreach ($headers as $i => $h) {
    $kiemKhoSheet->setCellValueByColumnAndRow($i+1, 1, $h);
}
$kiemKhoSheet->getStyle('A1:J1')->getFont()->setBold(true);
$kiemKhoSheet->getStyle('A1:J1')->getFill()->setFillType('solid');
$kiemKhoSheet->getStyle('A1:J1')->getFill()->getStartColor()->setRGB('4472C4');
$kiemKhoSheet->getStyle('A1:J1')->getFont()->getColor()->setRGB('FFFFFF');

// Collect products by location from summary
$summaryHighestRow = $summarySheet->getHighestRow();
$productsByLocation = [];
for ($row = 10; $row <= $summaryHighestRow; $row++) {
    $code = $summarySheet->getCellByColumnAndRow(2, $row)->getValue();
    $name = $summarySheet->getCellByColumnAndRow(3, $row)->getValue();
    $unit = $summarySheet->getCellByColumnAndRow(5, $row)->getValue();
    $loc = $summarySheet->getCellByColumnAndRow(6, $row)->getValue();
    if ($code && $code !== 'Tong cong' && !empty(trim($code))) {
        $code = (string)trim($code);
        $locKey = ($loc && !empty(trim($loc))) ? (string)trim($loc) : 'Chua xac dinh';
        $productsByLocation[$locKey][] = [
            'code' => $code,
            'name' => (string)trim($name),
            'unit' => (string)trim($unit),
            'location' => $locKey
        ];
    }
}
$locations = array_keys($productsByLocation);
sort($locations);
echo "Locations: " . count($locations) . "\n";

// Generate 10 products per location, no duplicate codes
$rowNum = 2;
$stt = 1;
$usedCodes = [];
foreach ($locations as $location) {
    if (!isset($productsByLocation[$location])) continue;
    $products = $productsByLocation[$location];
    $count = 0;
    foreach ($products as $p) {
        if (in_array($p['code'], $usedCodes)) continue;
        if ($count >= 10) break;
        $kiemKhoSheet->setCellValueByColumnAndRow(1, $rowNum, $stt);
        $kiemKhoSheet->setCellValueByColumnAndRow(2, $rowNum, date('Y-m-d'));
        $kiemKhoSheet->setCellValueByColumnAndRow(3, $rowNum, $location);
        $kiemKhoSheet->setCellValueByColumnAndRow(4, $rowNum, $p['code']);
        $kiemKhoSheet->setCellValueByColumnAndRow(5, $rowNum, $p['name']);
        $kiemKhoSheet->setCellValueByColumnAndRow(6, $rowNum, $p['unit']);
        $kiemKhoSheet->setCellValueByColumnAndRow(7, $rowNum, "=IFERROR(VLOOKUP(D{$rowNum},'Bao cao ton kho tong hop'!\$B:\$M,13,FALSE),0)");
        $kiemKhoSheet->setCellValueByColumnAndRow(9, $rowNum, "=IFERROR(H{$rowNum}-G{$rowNum},\"\")");
        $usedCodes[] = $p['code'];
        $rowNum++;
        $stt++;
        $count++;
    }
}
$kkLastRow = $rowNum - 1;
echo "Kiem kho rows: " . ($stt - 1) . "\n";

// Conditional formatting for chenh lech
if ($kkLastRow >= 2) {
    $condLoss = new Conditional();
    $condLoss->setConditionType(Conditional::CONDITION_CELLIS);
    $condLoss->setOperatorType(Conditional::OPERATOR_LESSTHAN);
    $condLoss->addCondition("I2=0");
    $condLoss->getStyle()->getFont()->getColor()->setRGB('FFFFFF');
    $condLoss->getStyle()->getFill()->setFillType('solid');
    $condLoss->getStyle()->getFill()->getStartColor()->setRGB('FF4444');
    $kiemKhoSheet->getStyle("I2:I{$kkLastRow}")->setConditionalStyles([$condLoss]);

    $condSurplus = new Conditional();
    $condSurplus->setConditionType(Conditional::CONDITION_CELLIS);
    $condSurplus->setOperatorType(Conditional::OPERATOR_GREATERTHAN);
    $condSurplus->addCondition("I2=0");
    $condSurplus->getStyle()->getFont()->getColor()->setRGB('FFFFFF');
    $condSurplus->getStyle()->getFill()->setFillType('solid');
    $condSurplus->getStyle()->getFill()->getStartColor()->setRGB('00B050');
    $kiemKhoSheet->getStyle("I2:I{$kkLastRow}")->addConditionalStyle($condSurplus);
}
