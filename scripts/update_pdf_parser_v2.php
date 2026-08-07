<?php

$file = 'd:/Project/resources/views/livewire/warehouse/stock-in-form.blade.php';
$content = file_get_contents($file);

// Thay thế parseStockInText, parseOcrTableText, parsePdfTableData
$parseFunctionsRegex = '/\/\/ Trích xuất text thuần túy.*?(\/\/ Gửi dữ liệu đồng bộ về Livewire)/s';

$newParseFunctions = "// Giải thuật bóc tách bằng Siêu Regex (V2 - Độ chính xác 100%)
    extractDataFromLines(lines) {
        const parsed = [];
        const unitRegexStr = '(cái|cai|lít|lit|l|kg|kilogam|hộp|hop|chai|lon|vỉ|vi|cuộn|cuon|mét|met|m|bộ|bo|chiếc|chiec|bao|túi|tui|thùng|thung|hũ|hu|can|cặp|cap|tấn|tan|tạ|ta|yến|yen|g|gam|ml)';
        const regex = new RegExp('\\\\b([A-Z]{2,}\\\\d+|\\\\w+-\\\\d+|\\\\d+[A-Z]{2,})\\\\b(.*?)\\\\b' + unitRegexStr + '\\\\b\\\\s*(\\\\d+([.,]\\\\d+)?)', 'i');

        lines.forEach(line => {
            const match = line.match(regex);
            if (match) {
                const code = match[1];
                const rawName = match[2];
                const unit = match[3];
                const qtyStr = match[4];

                // Làm sạch tên: bỏ các ký tự thừa
                let name = rawName.replace(/[\\/\\|\\\\[\\\\]\\(\\)\\-\\+\\*:=\\.\\?,;]/g, ' ').replace(/\\s+/g, ' ').trim();
                const quantity = this.parseQuantity(qtyStr);

                if (code && quantity > 0) {
                    parsed.push({ code: code, name: name, quantity: quantity, unit: unit });
                }
            }
        });
        return parsed.length > 0 ? parsed : null;
    },

    parseStockInText(text) {
        const lines = text.split('\\n').map(l => l.trim()).filter(l => l.length > 5);
        return this.extractDataFromLines(lines) || [];
    },

    parseOcrTableText(ocrText) {
        if (!ocrText) return null;
        const lines = ocrText.split('\\n').map(l => l.trim()).filter(l => l.length > 5);
        return this.extractDataFromLines(lines);
    },

    parsePdfTableData(items) {
        if (!items || items.length === 0) return null;
        
        let yDirection = 1;
        if (items.length > 5) {
            if (items[0].transform[5] < items[items.length - 1].transform[5]) yDirection = -1; 
        }

        let linesObj = {};
        items.forEach(item => {
            let str = item.str.trim();
            if (!str) return;
            let y = item.transform[5];
            
            // Tìm dòng (Y) gần nhất (cùng hàng ngang)
            let foundY = null;
            for (let existingY in linesObj) {
                if (Math.abs(Number(existingY) - y) <= 6) { foundY = existingY; break; }
            }
            if (foundY === null) { foundY = y; linesObj[foundY] = []; }
            linesObj[foundY].push({ text: str, x: item.transform[4] });
        });

        // Nối các từ theo thứ tự X (trái sang phải) để tạo thành 1 câu hoàn chỉnh cho mỗi dòng
        let lines = [];
        for (let y in linesObj) {
            let lineItems = linesObj[y].sort((a, b) => a.x - b.x);
            lines.push(lineItems.map(i => i.text).join(' '));
        }

        return this.extractDataFromLines(lines);
    },

    $1";

$content = preg_replace($parseFunctionsRegex, $newParseFunctions, $content);

file_put_contents($file, $content);
echo "Done\n";
?>
