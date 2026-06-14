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
