<?php
$lines = [
    "1 VAP02348 Mỡ bôi trơn Licas Grease No2 EP2 - Kg 3,400 3,400 - Mới Ñ",
    "2 VAP2179 Dầu hộp số Total Dynatrans ACX 30 - LIT 416 416 - Mới Ñ",
    "Phùng Anh Hảo 18/05/2026 - 08:38; XN: Đặng Hữu Hòa 18/05/2026 - 11:31; PD1: Nguyễn Sơn Hải 18/05/2026 - 22:20"
];

$unitRegex = '(cái|cai|lít|lit|l|kg|kilogam|hộp|hop|chai|lon|vỉ|vi|cuộn|cuon|mét|met|m|bộ|bo|chiếc|chiec|bao|túi|tui|thùng|thung|hũ|hu|can|cặp|cap|tấn|tan|tạ|ta|yến|yen|g|gam|ml)';

foreach ($lines as $line) {
    if (preg_match("/\b([A-Z]{2,}\d+|\w+-\d+|\d+[A-Z]{2,})\b(.*?)\b{$unitRegex}\b\s*(\d+([.,]\d+)?)/i", $line, $matches)) {
        $code = $matches[1];
        $name = trim(preg_replace('/[\/\|\\\[\]\(\)\-\+\*:=\.\?,;]/', ' ', $matches[2]));
        $unit = $matches[3];
        $qtyStr = $matches[4];
        
        $quantity = floatval(str_replace(',', '', $qtyStr));
        
        echo "Line: $line\n";
        echo "Code: $code\n";
        echo "Name: $name\n";
        echo "Unit: $unit\n";
        echo "Qty: $quantity\n";
        echo "-------------------\n";
    } else {
        echo "No match for: $line\n";
        echo "-------------------\n";
    }
}
