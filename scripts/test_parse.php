<?php
$lines = [
    "1 VAP02348 Mỡ bôi trơn Licas Grease No2 EP2 - Kg 3,400 3,400 - Mới Ñ",
    "2 VAP2179 Dầu hộp số Total Dynatrans ACX 30 - LIT 416 416 - Mới Ñ",
    "Phùng Anh Hảo 18/05/2026 - 08:38; XN: Đặng Hữu Hòa 18/05/2026 - 11:31; PD1: Nguyễn Sơn Hải 18/05/2026 - 22:20"
];

foreach ($lines as $line) {
    // 1. Remove STT at the beginning
    $line = preg_replace('/^\s*\d+\s+/', '', $line);
    
    // 2. Find Code
    $code = '';
    if (preg_match('/\b([A-Z]{2,}\d+|\w+-\d+|\d+[A-Z]{2,})\b/i', $line, $matches)) {
        $code = $matches[0];
    }
    
    // 3. Find Unit
    $unit = 'Cái';
    if (preg_match('/\b(cái|cai|lít|lit|l|kg|kilogam|hộp|hop|chai|lon|vỉ|vi|cuộn|cuon|mét|met|m|bộ|bo|chiếc|chiec|bao|túi|tui|thùng|thung|hũ|hu|can|cặp|cap|tấn|tan|tạ|ta|yến|yen|g|gam|ml)\b/i', $line, $matches)) {
        $unit = $matches[0];
    }
    
    // 4. Find Quantity (prefer the last large number)
    $quantity = '';
    if (preg_match_all('/\b(\d+([.,]\d+)?)\b/', $line, $matches)) {
        // filter out dates or small numbers if there are better ones?
        // Actually, if we just take the last number that is not a date part...
        $nums = $matches[0];
        // If there are multiple, maybe pick the largest? Or the last?
        // Usually quantity is 3,400.
        // Let's parse numbers
        $parsedNums = [];
        foreach ($nums as $n) {
            $nNorm = str_replace(',', '', $n);
            $parsedNums[] = floatval($nNorm);
        }
        if (!empty($parsedNums)) {
            // Find the maximum number? 
            $quantity = max($parsedNums);
        }
    }
    
    // 5. Find Name
    // Name is whatever is between Code and Unit, or just strip code and unit
    $name = $line;
    if ($code) $name = str_replace($code, '', $name);
    if ($unit != 'Cái') $name = str_ireplace($unit, '', $name);
    // Remove all numbers
    $name = preg_replace('/\b\d+([.,]\d+)?\b/', '', $name);
    // Remove punctuation
    $name = preg_replace('/[\/\|\\\[\]\(\)\-\+\*:=\.\?,;]/', ' ', $name);
    // Remove "Mới", "Ñ", "Đạt"
    $name = preg_replace('/\b(Mới|Ñ|Đạt)\b/i', '', $name);
    $name = trim(preg_replace('/\s+/', ' ', $name));
    
    echo "Line: $line\n";
    echo "Code: $code\n";
    echo "Name: $name\n";
    echo "Unit: $unit\n";
    echo "Qty: $quantity\n";
    echo "-------------------\n";
}
