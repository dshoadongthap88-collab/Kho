<?php
require 'vendor/autoload.php';
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('docs/TON.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
echo json_encode(array_slice($rows, 0, 5));
