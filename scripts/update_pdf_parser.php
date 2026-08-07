<?php

$file = 'd:/Project/resources/views/livewire/warehouse/stock-in-form.blade.php';
$content = file_get_contents($file);

// 1. Thay thế parseQuantity
$parseQtyRegex = '/parseQuantity\(text\) \{.*?\},\s*\/\/ Xử lý khi tải lên/s';
$newParseQty = "parseQuantity(text) {
        if (!text) return '';
        const numStr = text.replace(/[^\d.,]/g, '').trim();
        if (!numStr) return '';
        const hasComma = numStr.includes(',');
        const hasDot = numStr.includes('.');
        if (hasComma && hasDot) {
            const lastComma = numStr.lastIndexOf(',');
            const lastDot = numStr.lastIndexOf('.');
            if (lastComma > lastDot) return parseFloat(numStr.replace(/\./g, '').replace(',', '.'));
            else return parseFloat(numStr.replace(/,/g, ''));
        } else if (hasComma) {
            const parts = numStr.split(',');
            if (parts.length === 2 && (parts[1].length === 1 || parts[1].length === 2)) return parseFloat(numStr.replace(',', '.'));
            return parseFloat(numStr.replace(/,/g, ''));
        } else if (hasDot) {
            return parseFloat(numStr);
        }
        return parseFloat(numStr);
    },

    // Xử lý khi tải lên";

$content = preg_replace($parseQtyRegex, $newParseQty, $content);


// 2. Thay thế parseStockInText, parseOcrTableText, parsePdfTableData
$parseFunctionsRegex = '/\/\/ Giải thuật bóc tách và phân tách thông tin.*?\/\/ Gửi dữ liệu đồng bộ về Livewire/s';

$newParseFunctions = "// Trích xuất text thuần túy
    parseStockInText(text) {
        const lines = text.split('\\n').map(l => l.trim()).filter(l => l.length > 2);
        const parsed = [];
        lines.forEach(line => {
            let rest = line;
            const unitMatch = rest.match(/\b(cái|cai|lít|lit|l|kg|kilogam|hộp|hop|chai|lon|vỉ|vi|cuộn|cuon|mét|met|m|bộ|bo|chiếc|chiec|bao|túi|tui|thùng|thung|hũ|hu|can|cặp|cap|tấn|tan|tạ|ta|yến|yen|g|gam|ml)\b/i);
            let unitVal = '';
            if (unitMatch) { unitVal = unitMatch[0]; rest = rest.replace(unitMatch[0], ''); }
            const numberMatches = rest.match(/(\b\d+([.,]\d+)?\b)/g) || [];
            let quantity = '';
            const numericValues = numberMatches.map(n => this.parseQuantity(n)).filter(v => !isNaN(v) && v > 0);
            if (numericValues.length > 0) quantity = numericValues[0];
            numberMatches.forEach(numStr => { rest = rest.replace(numStr, ''); });
            let scannedName = rest.replace(/[\/\|\\\[\]\(\)\-\+\*:=\.\?,;]/g, ' ').replace(/\s+/g, ' ').trim();
            if (scannedName.length < 2 && !quantity) return;
            let foundCode = '';
            const codeCandidateMatch = scannedName.match(/\b([A-Z]{2,}\d+|\w+-\d+|\d+[A-Z]{2,})\b/i);
            if (codeCandidateMatch) {
                foundCode = codeCandidateMatch[0];
                scannedName = scannedName.replace(foundCode, '').replace(/\s+/g, ' ').trim();
            }
            if (!foundCode || !quantity) return; // Yêu cầu bắt buộc
            parsed.push({ code: foundCode, name: scannedName, quantity: quantity, unit: unitVal || 'Cái' });
        });
        return parsed;
    },

    parseOcrTableText(ocrText) {
        if (!ocrText) return null;
        const normalizeStr = (s) => s.toLowerCase().replace(/[áàảãạăắằẳẵặâấầẩẫậ]/g, 'a').replace(/[éèẻẽẹêếềểễệ]/g, 'e').replace(/[íìỉĩị]/g, 'i').replace(/[óòỏõọôốồổỗộơớờởỡợ]/g, 'o').replace(/[úùủũụưứừửữự]/g, 'u').replace(/[ýỳỷỹỵ]/g, 'y').replace(/[đ]/g, 'd').replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
        const lines = ocrText.split('\\n').map(l => l.trim()).filter(l => l.length > 0);
        let headerLineIdx = -1;
        let colPositions = { code: -1, name: -1, unit: -1, qty: -1 };
        for (let i = 0; i < lines.length; i++) {
            const rawNorm = normalizeStr(lines[i]);
            const codeIdx = rawNorm.search(/ma\s*(hang|vat\s*tu|sp|hh|vt|code)/);
            const nameIdx = rawNorm.search(/ten\s*(hang|vat\s*tu|sp|hh|vt|name)|mota/);
            const unitIdx = rawNorm.search(/don\s*vi|d\.?v\.?t/);
            let qtyIdx = rawNorm.search(/so\s*luong\s*(nhan|nhap)|sl\s*(nhan|nhap)|thuc\s*(nhan|nhap)/);
            if (qtyIdx === -1) qtyIdx = rawNorm.search(/so\s*luong(?!.*giao)|sl(?!.*giao)/);
            if (qtyIdx === -1) qtyIdx = rawNorm.search(/so\s*luong|sl/);
            if (codeIdx !== -1 && qtyIdx !== -1) {
                headerLineIdx = i;
                const ratio = lines[i].length / rawNorm.length;
                colPositions.code = Math.round(codeIdx * ratio);
                colPositions.name = nameIdx >= 0 ? Math.round(nameIdx * ratio) : -1;
                colPositions.unit = unitIdx >= 0 ? Math.round(unitIdx * ratio) : -1;
                colPositions.qty  = Math.round(qtyIdx * ratio);
                break;
            }
        }
        if (headerLineIdx === -1) return null;
        const sortedCols = [ { key: 'code', pos: colPositions.code }, { key: 'name', pos: colPositions.name }, { key: 'unit', pos: colPositions.unit }, { key: 'qty',  pos: colPositions.qty  } ].filter(c => c.pos >= 0).sort((a, b) => a.pos - b.pos);
        const colBounds = sortedCols.map((col, idx) => {
            const nextPos = idx < sortedCols.length - 1 ? sortedCols[idx + 1].pos : 9999;
            const prevPos = idx > 0 ? sortedCols[idx - 1].pos : 0;
            return { key: col.key, start: idx === 0 ? 0 : Math.round((col.pos + prevPos) / 2), end: idx === sortedCols.length - 1 ? 9999 : Math.round((col.pos + nextPos) / 2) };
        });
        const getColText = (line, colKey) => {
            const bound = colBounds.find(b => b.key === colKey);
            if (!bound) return '';
            return line.slice(Math.min(bound.start, line.length), Math.min(bound.end, line.length)).trim();
        };
        const parsed = [];
        let currentRow = null;
        for (let i = headerLineIdx + 1; i < lines.length; i++) {
            const line = lines[i];
            if (line.length < 3) continue;
            const norm = normalizeStr(line);
            if (norm.includes('tong cong') || norm.includes('tong so') || norm.includes('ky ten') || norm.includes('ghi chu')) break;
            const codeVal = getColText(line, 'code').replace(/\s+/g, ' ').trim();
            const nameVal = getColText(line, 'name').replace(/\s+/g, ' ').trim();
            const unitVal = getColText(line, 'unit').replace(/\s+/g, ' ').trim();
            const qtyVal  = this.parseQuantity(getColText(line, 'qty').trim());
            let finalCode = codeVal;
            const sttMatch = codeVal.match(/^(\d{1,3})\s+(.*)/);
            if (sttMatch) finalCode = sttMatch[2].trim();
            
            if (finalCode || nameVal || qtyVal) {
                if (currentRow) {
                    if (currentRow.code && currentRow.quantity) parsed.push(currentRow);
                }
                currentRow = { code: finalCode, name: nameVal, quantity: qtyVal || '', unit: unitVal || 'Cái' };
            } else if (currentRow && nameVal) {
                currentRow.name += ' ' + nameVal;
            }
        }
        if (currentRow && currentRow.code && currentRow.quantity) parsed.push(currentRow);
        return parsed;
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
            let x = item.transform[4];
            let foundY = null;
            for (let existingY in linesObj) {
                if (Math.abs(Number(existingY) - y) <= 8) { foundY = existingY; break; }
            }
            if (foundY === null) { foundY = y; linesObj[foundY] = []; }
            linesObj[foundY].push({ text: str, x: x, y: y, size: Math.abs(item.transform[0]) });
        });
        let sortedY = Object.keys(linesObj).map(Number).sort((a, b) => yDirection === 1 ? b - a : a - b);
        let lines = [];
        let normalizeString = (s) => s.toLowerCase().replace(/[áàảãạăắằẳẵặâấầẩẫậ]/g, 'a').replace(/[éèẻẽẹêếềểễệ]/g, 'e').replace(/[íìỉĩị]/g, 'i').replace(/[óòỏõọôốồổỗộơớờởỡợ]/g, 'o').replace(/[úùủũụưứừửữự]/g, 'u').replace(/[ýỳỷỹỵ]/g, 'y').replace(/[đ]/g, 'd').replace(/[^a-z0-9]/g, '');
        sortedY.forEach(y => {
            let lineItems = linesObj[y].sort((a, b) => a.x - b.x);
            let fullStr = lineItems.map(i => i.text).join(' ');
            lines.push({ y: Number(y), items: lineItems, normStr: normalizeString(fullStr), rawStr: fullStr });
        });
        let headerY = null;
        for (let i = 0; i < lines.length; i++) {
            let s = lines[i].normStr;
            if (s.includes('mahang') || s.includes('mavattu') || s.includes('masp') || s.includes('mavt') || s.includes('mahh') || s.includes('tenhang') || s.includes('tenvattu')) {
                headerY = lines[i].y;
                break;
            }
        }
        if (headerY === null) return null; 
        let headerItems = items.filter(it => Math.abs(it.transform[5] - headerY) < 18).sort((a, b) => a.transform[4] - b.transform[4]);
        let mergedHeaders = [];
        let curHeader = null;
        let avgWidth = headerItems.length > 0 ? headerItems.reduce((acc, it) => acc + (it.size || 6), 0) / headerItems.length : 6;
        if (avgWidth < 1) avgWidth = 6;
        let mergeThreshold = Math.max(avgWidth * 3.5, 25); 
        for (let it of headerItems) {
            if (!curHeader) { curHeader = { text: it.text, x: it.x, endX: it.x + (it.text.length * avgWidth * 0.7) }; }
            else {
                if (it.x - curHeader.endX < mergeThreshold) { curHeader.text += ' ' + it.text; curHeader.endX = it.x + (it.text.length * avgWidth * 0.7); }
                else { mergedHeaders.push(curHeader); curHeader = { text: it.text, x: it.x, endX: it.x + (it.text.length * avgWidth * 0.7) }; }
            }
        }
        if (curHeader) mergedHeaders.push(curHeader);
        for (let i = 0; i < mergedHeaders.length; i++) {
            mergedHeaders[i].start = (i === 0) ? -9999 : (mergedHeaders[i-1].x + mergedHeaders[i].x) / 2;
            mergedHeaders[i].end = (i === mergedHeaders.length - 1) ? 9999 : (mergedHeaders[i].x + mergedHeaders[i+1].x) / 2;
            mergedHeaders[i].normText = normalizeString(mergedHeaders[i].text);
        }
        let colBounds = { code: null, name: null, unit: null, quantity: null };
        let scores = { code: { header: null, score: 0 }, name: { header: null, score: 0 }, unit: { header: null, score: 0 }, quantity: { header: null, score: 0 } };
        mergedHeaders.forEach(h => {
            let nText = h.normText;
            let codeScore = 0; if (nText === 'ma' || nText === 'mavt' || nText === 'mahh') codeScore = 100; else if (nText.includes('mavattu') || nText.includes('mahanghoa')) codeScore = 90; else if (nText.includes('mahang') || nText.includes('code')) codeScore = 80;
            if (codeScore > scores.code.score) { scores.code.score = codeScore; scores.code.header = h; }
            let nameScore = 0; if (nText === 'ten' || nText === 'tenvt' || nText === 'tenhh') nameScore = 100; else if (nText.includes('tenvattu') || nText.includes('tenhanghoa')) nameScore = 90; else if (nText.includes('tenhang') || nText.includes('name')) nameScore = 80;
            if (nameScore > scores.name.score) { scores.name.score = nameScore; scores.name.header = h; }
            let unitScore = 0; if (nText === 'dvt' || nText === 'donvitinh') unitScore = 100; else if (nText.includes('donvi')) unitScore = 80;
            if (unitScore > scores.unit.score) { scores.unit.score = unitScore; scores.unit.header = h; }
            let qtyScore = 0; if (nText.includes('soluongnhan') || nText.includes('slnhan') || nText.includes('thucnhan')) qtyScore = 100; else if ((nText.includes('soluong') || nText.includes('sl')) && !nText.includes('giao')) qtyScore = 80; else if (nText.includes('soluong') || nText.includes('sl')) qtyScore = 50;
            if (qtyScore > scores.quantity.score) { scores.quantity.score = qtyScore; scores.quantity.header = h; }
        });
        if (scores.code.score > 0) colBounds.code = scores.code.header;
        if (scores.name.score > 0) colBounds.name = scores.name.header;
        if (scores.unit.score > 0) colBounds.unit = scores.unit.header;
        if (scores.quantity.score > 0) colBounds.quantity = scores.quantity.header;
        if (!colBounds.code || !colBounds.quantity) return null;
        let colsData = { code: [], name: [], unit: [], quantity: [] };
        items.forEach(it => {
            let x = it.transform[4], y = it.transform[5], text = it.str.trim();
            if (!text) return;
            if (Math.abs(y - headerY) <= 8) return;
            if (yDirection === 1 && y > headerY) return; 
            if (yDirection === -1 && y < headerY) return; 
            if (colBounds.code && x >= colBounds.code.start && x <= colBounds.code.end) colsData.code.push({text, y, x});
            else if (colBounds.name && x >= colBounds.name.start && x <= colBounds.name.end) colsData.name.push({text, y, x});
            else if (colBounds.unit && x >= colBounds.unit.start && x <= colBounds.unit.end) colsData.unit.push({text, y, x});
            else if (colBounds.quantity && x >= colBounds.quantity.start && x <= colBounds.quantity.end) colsData.quantity.push({text, y, x});
        });
        let anchors = colsData.code.length > 0 ? colsData.code : (colsData.quantity.length > 0 ? colsData.quantity : colsData.name);
        anchors.sort((a, b) => yDirection === 1 ? b.y - a.y : a.y - b.y);
        let finalAnchors = [];
        anchors.forEach(a => {
            if (finalAnchors.length === 0) finalAnchors.push(a);
            else { let last = finalAnchors[finalAnchors.length - 1]; if (Math.abs(last.y - a.y) > 20) finalAnchors.push(a); }
        });
        let rows = finalAnchors.map(a => ({ anchorY: a.y, codeItems: [], nameItems: [], unitItems: [], quantityItems: [] }));
        if (rows.length === 0) return null;
        let assignToRow = (itemsArray, targetCol) => {
            itemsArray.forEach(it => {
                let closestRow = null, minDiff = 9999;
                rows.forEach(r => { let diff = Math.abs(r.anchorY - it.y); if (diff < minDiff) { minDiff = diff; closestRow = r; } });
                if (closestRow && minDiff < 50) closestRow[targetCol].push(it);
            });
        };
        assignToRow(colsData.code, 'codeItems');
        assignToRow(colsData.name, 'nameItems');
        assignToRow(colsData.unit, 'unitItems');
        assignToRow(colsData.quantity, 'quantityItems');
        let parsed = [];
        rows.forEach(r => {
            let sortAndJoin = (arr) => arr.sort((a, b) => {
                if (Math.abs(a.y - b.y) > 5) return yDirection === 1 ? b.y - a.y : a.y - b.y;
                return a.x - b.x;
            }).map(i => i.text).join(' ').trim();
            let codeVal = sortAndJoin(r.codeItems).replace(/^\d+\s+/, '').trim();
            let nameVal = sortAndJoin(r.nameItems);
            let unitVal = sortAndJoin(r.unitItems);
            let qtyVal = this.parseQuantity(sortAndJoin(r.quantityItems));
            if (!codeVal || !qtyVal) return; // Yêu cầu bắt buộc
            if (nameVal.toLowerCase().includes('tổng') || nameVal.toLowerCase().includes('cộng') || codeVal.toLowerCase().includes('tổng')) return;
            parsed.push({ code: codeVal, name: nameVal, quantity: qtyVal, unit: unitVal || 'Cái' });
        });
        return parsed.length > 0 ? parsed : null;
    },

    // Gửi dữ liệu đồng bộ về Livewire";

$content = preg_replace($parseFunctionsRegex, $newParseFunctions, $content);

// 3. Cập nhật bảng Preview Lưới xem trước PDF
$previewRegex = '/<table class="w-full text-left text-xs border-collapse">.*?<\/table>/s';

$newPreview = '<table class="w-full text-left text-xs border-collapse">
                                        <thead>
                                            <tr class="bg-slate-800 font-black border-b border-slate-700 text-white uppercase tracking-widest text-[10px]">
                                                <th class="p-3 w-40 text-left">Mã vật tư</th>
                                                <th class="p-3 text-left">Tên vật tư</th>
                                                <th class="p-3 w-28 text-center">ĐVT</th>
                                                <th class="p-3 w-32 text-center">Số lượng nhập</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, index) in ocrParsedRows" :key="index">
                                                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                                    <td class="p-3 text-slate-800 font-bold" x-text="row.code"></td>
                                                    <td class="p-3 text-slate-700" x-text="row.name || \'- chưa xác định -\'"></td>
                                                    <td class="p-3 text-center text-slate-500 font-semibold" x-text="row.unit || \'Cái\'"></td>
                                                    <td class="p-3 text-center text-indigo-700 font-black" x-text="row.quantity"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>';

$content = preg_replace($previewRegex, $newPreview, $content);

file_put_contents($file, $content);

echo "Done\n";
?>
