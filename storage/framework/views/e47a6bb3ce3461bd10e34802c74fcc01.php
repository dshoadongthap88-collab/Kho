<div class="h-full flex flex-col space-y-4" x-data="{
    activeImportTab: 'excel',
    ocrProgress: 0,
    ocrStatus: '',
    ocrRunning: false,
    ocrImageSrc: '',
    ocrParsedRows: [],
    ocrMaximized: false,
    
    // Bản đồ sản phẩm phục vụ so khớp trực tiếp trên Trình duyệt
    productsMap: {
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
'<?php echo e(strtolower($p->code)); ?>': { id: <?php echo e($p->id); ?>, name: '<?php echo e(addslashes($p->name)); ?>', unit: '<?php echo e(addslashes($p->unit ?: 'Cái')); ?>', price: <?php echo e($p->price ?: 0); ?>, location: '<?php echo e(addslashes($p->location ?: '')); ?>', box_spec: '<?php echo e(addslashes($p->box_spec ?: '')); ?>', carton_spec: '<?php echo e(addslashes($p->carton_spec ?: '')); ?>' },
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    },

    // Helper: Parse số lượng thông minh - xử lý cả dấu phẩy và chấm làm decimal separator
    parseQuantity(text) {
        if (!text) return '';
        const numStr = text.replace(/[^\d.,]/g, '').trim();
        if (!numStr) return '';

        const hasComma = numStr.includes(',');
        const hasDot = numStr.includes('.');

        if (hasComma && hasDot) {
            const lastComma = numStr.lastIndexOf(',');
            const lastDot = numStr.lastIndexOf('.');
            if (lastComma > lastDot) {
                // Format VN: 1.234,56 → 1234.56
                return parseFloat(numStr.replace(/\./g, '').replace(',', '.'));
            } else {
                // Format US: 1,234.56 → 1234.56
                return parseFloat(numStr.replace(/,/g, ''));
            }
        } else if (hasComma) {
            // Kiểm tra xem phẩy là decimal hay thousands separator
            const parts = numStr.split(',');
            if (parts.length === 2 && (parts[1].length === 1 || parts[1].length === 2)) {
                return parseFloat(numStr.replace(',', '.'));
            }
            return parseFloat(numStr.replace(/,/g, ''));
        } else if (hasDot) {
            // Kiểm tra chấm có phải decimal không
            const parts = numStr.split('.');
            if (parts.length === 2 && (parts[1].length === 1 || parts[1].length === 2)) {
                return parseFloat(numStr);
            }
            // Nếu có đúng 3 chữ số sau dấu chấm, ví dụ 2.704 hoặc 3.000, rất có thể là phân cách hàng nghìn (VN)
            return parseFloat(numStr.replace(/\./g, ''));
        }

        return parseFloat(numStr);
    },

    // Xử lý khi tải lên / dán ảnh chụp hoặc chọn tệp PDF
    handleImageUpload(event) {
        const file = event.target.files ? event.target.files[0] : (event.dataTransfer ? event.dataTransfer.files[0] : null);
        if (file) {
            this.readImage(file);
        }
    },
    handleImagePaste(event) {
        const items = (event.clipboardData || event.originalEvent.clipboardData).items;
        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') !== -1) {
                const file = items[i].getAsFile();
                this.readImage(file);
                break;
            }
        }
    },
    readImage(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            this.ocrImageSrc = e.target.result;
            this.ocrStatus = 'Ảnh chụp đã sẵn sàng phân tích!';
            this.ocrParsedRows = [];
        };
        reader.readAsDataURL(file);
    },

    // Đọc và phân tích tệp tin PDF ngay tại Client
    // Vì PDF VAP lưu bảng dạng ảnh (không có text layer), ta render từng trang thành canvas
    // rồi dùng Tesseract OCR đọc nội dung bảng
    async handlePdfUpload(event) {
        const file = event.target.files ? event.target.files[0] : (event.dataTransfer ? event.dataTransfer.files[0] : null);
        if (!file) return;

        this.ocrRunning = true;
        this.ocrProgress = 5;
        this.ocrStatus = 'Đang khởi động công cụ xử lý PDF...';

        try {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;

            // Bước 1: Thử đọc text layer trước
            let allTextItems = [];
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const textContent = await page.getTextContent();
                textContent.items.forEach(item => allTextItems.push(item));
            }

            // Lọc các item có nội dung thực sự (không phải metadata như tên người duyệt)
            const meaningfulItems = allTextItems.filter(it => it.str && it.str.trim().length > 0);

            // Bước 2: Thử parse bảng từ text layer
            let parsedTableRows = this.parsePdfTableData(meaningfulItems);

            if (parsedTableRows && parsedTableRows.length > 0) {
                // PDF có text layer và parse được bảng
                this.ocrParsedRows = parsedTableRows;
                this.ocrProgress = 100;
                this.ocrRunning = false;
                this.ocrStatus = `Nhận diện PDF xong! Tìm thấy ${this.ocrParsedRows.length} dòng vật tư nhập kho.`;
                return;
            }

            // Bước 3: PDF không có text layer (như VAP) → render từng trang thành ảnh rồi OCR
            this.ocrStatus = `PDF dạng ảnh - Đang khởi động OCR để quét ${pdf.numPages} trang...`;
            this.ocrProgress = 10;

            // Dùng ref để cập nhật progress từ trong callback của Tesseract
            const self = this;
            const totalPages = pdf.numPages;
            let currentPageNum = 1;

            const worker = await Tesseract.createWorker('vie+eng', 1, {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        const baseProgress = 10 + Math.round(((currentPageNum - 1) / totalPages) * 75);
                        const pageProgress = Math.round((m.progress * 75) / totalPages);
                        self.ocrProgress = Math.min(85, baseProgress + pageProgress);
                    }
                }
            });

            let combinedOcrText = '';

            for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                currentPageNum = pageNum;
                this.ocrStatus = `Đang quét trang ${pageNum}/${totalPages} bằng AI OCR...`;
                this.ocrProgress = 10 + Math.round(((pageNum - 1) / totalPages) * 75);

                const page = await pdf.getPage(pageNum);
                // Scale 2.5x để tăng độ chính xác OCR với PDF nhỏ
                const viewport = page.getViewport({ scale: 2.5 });

                const canvas = document.createElement('canvas');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                const ctx = canvas.getContext('2d');

                await page.render({ canvasContext: ctx, viewport }).promise;

                const imageDataUrl = canvas.toDataURL('image/png');
                const { data: { text } } = await worker.recognize(imageDataUrl);
                combinedOcrText += text + '\n';
            }

            await worker.terminate();

            this.ocrProgress = 90;
            this.ocrStatus = 'Đang bóc tách dữ liệu bảng từ kết quả OCR...';

            // Parse kết quả OCR: thử nhận diện cấu trúc bảng trước, sau đó fallback
            const ocrTableRows = this.parseOcrTableText(combinedOcrText);
            if (ocrTableRows && ocrTableRows.length > 0) {
                this.ocrParsedRows = ocrTableRows;
            } else {
                this.ocrParsedRows = this.parseStockInText(combinedOcrText);
            }

            this.ocrProgress = 100;
            this.ocrRunning = false;
            this.ocrStatus = `Nhận diện PDF xong! Tìm thấy ${this.ocrParsedRows.length} dòng vật tư nhập kho.`;
        } catch (error) {
            console.error(error);
            this.ocrRunning = false;
            this.ocrStatus = 'Lỗi đọc tệp PDF: ' + error.message;
            alert('Đọc tệp PDF thất bại! Vui lòng kiểm tra định dạng.');
        }
    },

    // Chạy OCR cho ảnh chụp
    async runOCR() {
        if (!this.ocrImageSrc) {
            alert('Vui lòng chọn hoặc dán ảnh chụp phiếu trước!');
            return;
        }

        this.ocrRunning = true;
        this.ocrProgress = 10;
        this.ocrStatus = 'Đang khởi động công cụ AI OCR...';

        try {
            const worker = await Tesseract.createWorker('vie+eng', 1, {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        this.ocrProgress = Math.round(15 + m.progress * 80);
                        this.ocrStatus = `Đang quét văn bản trên ảnh chụp: ${Math.round(m.progress * 100)}%`;
                    }
                }
            });

            this.ocrStatus = 'Đang bóc tách chữ tiếng Việt trên phiếu...';
            const { data: { text } } = await worker.recognize(this.ocrImageSrc);
            await worker.terminate();

            this.ocrProgress = 95;
            this.ocrStatus = 'Đang lập bản đồ vật tư khớp danh mục...';
            
            this.ocrParsedRows = this.parseStockInText(text);

            this.ocrProgress = 100;
            this.ocrRunning = false;
            this.ocrStatus = `Nhận diện ảnh xong! Phân tích được ${this.ocrParsedRows.length} dòng vật tư.`;
        } catch (error) {
            console.error(error);
            this.ocrRunning = false;
            this.ocrStatus = 'Lỗi OCR: ' + error.message;
            alert('Nhận diện ảnh thất bại!');
        }
    },

    // Giải thuật bóc tách và phân tách thông tin phiếu nhập kho thông minh tối tân
    parseStockInText(text) {
        const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 2);
        const parsed = [];

        lines.forEach(line => {
            let rest = line;

            // 0. Chuan hoa sua loi OCR va boc tach cac dac thu cua bang in
            // - Sua chu O thanh so 0 trong ma hang: VAPO7001 -> VAP07001
            rest = rest.replace(/\b([A-Z]+)O(\d+)\b/gi, '$10$2');
            // - Sua chu I hoac l thanh so 1 o cuoi ma so san pham: VAP0700I/VAP0700l -> VAP07001
            rest = rest.replace(/\b([A-Z]+\d+)I\b/gi, '$11');
            rest = rest.replace(/\b([A-Z]+\d+)l\b/g, '$11');
            
            // - Loai bo so STT o dau dong
            const sttMatch = rest.match(/^\s*(\d+)\s+([A-Z]{2,}\d+|\w+-\d+)/i);
            if (sttMatch) {
                rest = rest.replace(/^\s*\d+\s+/, '');
            }

            // - Loai bo cac thong so kich thuoc, phan so de tranh nhan nham so luong
            rest = rest.replace(/\b\d+\/\d+\b/g, ''); 
            rest = rest.replace(/\b\d+-\d+\/\d+\b/g, '');
            rest = rest.replace(/\b\d+AT\b/gi, '');
            rest = rest.replace(/\b\d+Bar\b/gi, '');

            // 1. Tìm và trích xuất ngày hạn dùng (Expiry Date) - Dạng DD/MM/YYYY hoặc YYYY-MM-DD
            const dateMatch = rest.match(/(\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4}|\d{4}[\/-]\d{1,2}[\/-]\d{1,2})/);
            let expiryDate = '';
            if (dateMatch) {
                expiryDate = dateMatch[0];
                rest = rest.replace(dateMatch[0], '');
                try {
                    const parts = expiryDate.split(/[\/-]/);
                    if (parts[0].length === 4) {
                        expiryDate = `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
                    } else if (parts[2].length === 4) {
                        expiryDate = `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                    }
                } catch(e) {}
            }

            // 2. Tìm số lô
            const batchMatch = rest.match(/(lô\s*[\w\d-]+|lo\s*[\w\d-]+|batch\s*[\w\d-]+)/i);
            let batchNumber = '';
            if (batchMatch) {
                batchNumber = batchMatch[0].replace(/lô|lo|batch/i, '').trim();
                rest = rest.replace(batchMatch[0], '');
            }

            // 3. Tìm vị trí
            const locationMatch = rest.match(/([A-Z]\d+|ROW-\d+|KHO-\w+)/i);
            let location = '';
            if (locationMatch) {
                location = locationMatch[0].toUpperCase();
                rest = rest.replace(locationMatch[0], '');
            }

            // 4. Tìm đơn vị tính (ĐVT) tương ứng trong dòng văn bản ảnh chụp
            const unitMatch = rest.match(/\b(cái|cai|lít|lit|l|kg|kilogam|hộp|hop|chai|lon|vỉ|vi|cuộn|cuon|mét|met|m|bộ|bo|chiếc|chiec|bao|túi|tui|thùng|thung|hũ|hu|can|cặp|cap|tấn|tan|tạ|ta|yến|yen|g|gam|ml)\b/i);
            let unitVal = '';
            if (unitMatch) {
                unitVal = unitMatch[0];
                rest = rest.replace(unitMatch[0], '');
            }

            // 5. Tìm số lượng từ các số trong dòng (đơn giá để trống, người dùng nhập sau)
            const numberMatches = rest.match(/(\b\d+([.,]\d+)?\b)/g) || [];
            let quantity = '';

            // Parse tất cả numbers dùng parseQuantity để xử lý đúng format VN/US
            const numericValues = numberMatches.map(n => this.parseQuantity(n)).filter(v => !isNaN(v) && v > 0);

            if (numericValues.length > 0) {
                // Lấy số đầu tiên làm số lượng (phổ biến trong OCR dòng đơn)
                quantity = numericValues[0];
            }

            // Xóa các số khỏi chuỗi còn lại để lấy tên
            numberMatches.forEach(numStr => {
                rest = rest.replace(numStr, '');
            });

            // 6. Làm sạch phần văn bản còn lại để lấy tên vật tư quét được (Scanned Name)
            let scannedName = rest.replace(/[\/\|\\\[\]\(\)\-\+\*:=\.\?,;]/g, ' ')
                                  .replace(/\s+/g, ' ').trim();

            if (scannedName.length < 2 && !quantity) {
                return; // Dòng rác không có thông tin hữu ích
            }

            // 7. So khớp thông minh scannedName với productsMap
            let foundCode = '';
            let foundName = '';
            let matchedProduct = null;

            // Quét tìm xem có từ nào trông giống mã vật tư hay không (Ví dụ: VAP07001, ST-123)
            // Nhận diện mã kể cả khi chưa có trong CSDL
            const codeCandidateMatch = scannedName.match(/\b([A-Z]{2,}\d+|\w+-\d+|\d+[A-Z]{2,})\b/i);
            if (codeCandidateMatch) {
                const candidate = codeCandidateMatch[0];
                foundCode = candidate.toUpperCase();
                // Loại bỏ mã khỏi scannedName để trả lại tên vật tư sạch
                scannedName = scannedName.replace(candidate, '').replace(/\s+/g, ' ').trim();
            }

            // Tìm khớp mã trực tiếp sau khi lọc sạch ký tự đặc biệt khỏi từ
            const codeWords = scannedName.replace(/[,;.:!\?\(\)\[\]\{\}]/g, ' ').split(/\s+/);
            for (const word of codeWords) {
                const wordLower = word.trim().toLowerCase();
                if (this.productsMap[wordLower]) {
                    foundCode = word.toUpperCase();
                    matchedProduct = this.productsMap[wordLower];
                    break;
                }
            }

            // Nếu đã tìm thấy foundCode từ trước, thử tra cứu xem nó có trong productsMap không
            if (foundCode && !matchedProduct) {
                const codeLower = foundCode.toLowerCase();
                if (this.productsMap[codeLower]) {
                    matchedProduct = this.productsMap[codeLower];
                }
            }

            // Tìm khớp tên gián tiếp
            if (!matchedProduct) {
                const scannedNameLower = scannedName.toLowerCase();
                
                for (const code in this.productsMap) {
                    const p = this.productsMap[code];
                    const pNameLower = p.name.toLowerCase();
                    const pCodeLower = code.toLowerCase();
                    
                    if (scannedNameLower.includes(pCodeLower) || scannedNameLower.includes(pNameLower)) {
                        foundCode = code.toUpperCase();
                        matchedProduct = p;
                        break;
                    }
                }

                if (!matchedProduct) {
                    for (const code in this.productsMap) {
                        const p = this.productsMap[code];
                        const nameWords = p.name.toLowerCase().split(/\s+/).filter(w => w.length > 2);
                        let matchCount = 0;
                        nameWords.forEach(w => {
                            if (scannedNameLower.includes(w)) matchCount++;
                        });
                        
                        if (nameWords.length > 0 && matchCount >= Math.ceil(nameWords.length * 0.7)) {
                            foundCode = code.toUpperCase();
                            matchedProduct = p;
                            break;
                        }
                    }
                }
            }

            if (matchedProduct) {
                foundCode = matchedProduct.code || foundCode;
                foundName = matchedProduct.name;
            } else {
                foundName = scannedName; // Nếu chưa có mã đó trong CSDL, vẫn lấy tên vật tư bóc tách được từ hình ảnh!
            }

            parsed.push({
                code: foundCode || '',
                name: foundName || '',
                scanned_name: scannedName,
                quantity: quantity || '',
                unit: unitVal ? (unitVal.charAt(0).toUpperCase() + unitVal.slice(1).toLowerCase()) : (matchedProduct ? matchedProduct.unit : 'Cái'),
                batch_number: batchNumber || '',
                expiry_date: expiryDate || '',
                warehouse_location: location || '',
                unit_price: matchedProduct ? matchedProduct.price : 0
            });
        });

        return parsed;
    },

    // Parse văn bản OCR từ PDF dạng ảnh (như VAP) - nhận diện cấu trúc bảng từ text
    parseOcrTableText(ocrText) {
        if (!ocrText) return null;

        const normalizeStr = (s) => s.toLowerCase()
            .replace(/[áàảãạăắằẳẵặâấầẩẫậ]/g, 'a')
            .replace(/[éèẻẽẹêếềểễệ]/g, 'e')
            .replace(/[íìỉĩị]/g, 'i')
            .replace(/[óòỏõọôốồổỗộơớờởỡợ]/g, 'o')
            .replace(/[úùủũụưứừửữự]/g, 'u')
            .replace(/[ýỳỷỹỵ]/g, 'y')
            .replace(/[đ]/g, 'd')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ').trim();

        const lines = ocrText.split('\n').map(l => l.trim()).filter(l => l.length > 0);

        // Tìm dòng tiêu đề bảng (có chứa 'Mã hàng' / 'Tên hàng' / 'Số lượng')
        let headerLineIdx = -1;
        let colPositions = { code: -1, name: -1, unit: -1, qty: -1 };

        for (let i = 0; i < lines.length; i++) {
            const norm = normalizeStr(lines[i]);
            const hasCode = norm.includes('ma hang') || norm.includes('ma vat tu') || norm.includes('ma sp') || norm.includes('ma hh') || norm.includes('ma vt');
            const hasName = norm.includes('ten hang') || norm.includes('ten vat tu') || norm.includes('ten sp') || norm.includes('ten hh') || norm.includes('ten vt');
            const hasQty  = norm.includes('so luong') || norm.includes('sl nhan') || norm.includes('sl nhap') || norm.includes('s luong');

            if (hasCode && hasName) {
                headerLineIdx = i;

                // Xác định vị trí ký tự của từng cột trong dòng tiêu đề
                const raw = lines[i];
                const rawNorm = normalizeStr(raw);

                const codeIdx = rawNorm.search(/ma\s*(hang|vat\s*tu|sp|hh|vt|code)/);
                const nameIdx = rawNorm.search(/ten\s*(hang|vat\s*tu|sp|hh|vt|name)|mota/);
                const unitIdx = rawNorm.search(/don\s*vi|d\.?v\.?t/);
                
                // Thử tìm Số lượng nhận hoặc nhập trước
                let qtyIdx = rawNorm.search(/so\s*luong\s*(nhan|nhap)|sl\s*(nhan|nhap)|thuc\s*(nhan|nhap)/);
                if (qtyIdx === -1) {
                    // Tiếp theo thử tìm Số lượng chung (không chứa chữ 'giao' phía sau để tránh nhầm)
                    qtyIdx = rawNorm.search(/so\s*luong(?!.*giao)|sl(?!.*giao)/);
                }
                if (qtyIdx === -1) {
                    // Cuối cùng lấy bất kỳ Số lượng nào
                    qtyIdx = rawNorm.search(/so\s*luong|sl/);
                }

                // Kiểm tra bắt buộc: phải tìm thấy cột Mã và Số lượng
                if (codeIdx === -1 || qtyIdx === -1) {
                    console.warn('Không tìm thấy cột bắt buộc (Mã hoặc Số lượng) trong header PDF, bỏ qua dòng này');
                    continue; // Không break, tiếp tục tìm dòng header khác
                }

                // Tính tỉ lệ vị trí để ánh xạ lên độ dài chuỗi gốc
                const ratio = raw.length / rawNorm.length;
                colPositions.code = Math.round(codeIdx * ratio);
                colPositions.name = nameIdx >= 0 ? Math.round(nameIdx * ratio) : -1; // Optional
                colPositions.unit = unitIdx >= 0 ? Math.round(unitIdx * ratio) : -1; // Optional
                colPositions.qty  = Math.round(qtyIdx * ratio);
                break;
            }
        }

        if (headerLineIdx === -1) {
            console.warn('Không tìm thấy dòng header phù hợp trong OCR text');
            return null;
        }

        // Tính ranh giới giữa các cột (midpoint) - cải thiện với fallback an toàn
        const sortedCols = [
            { key: 'code', pos: colPositions.code },
            { key: 'name', pos: colPositions.name },
            { key: 'unit', pos: colPositions.unit },
            { key: 'qty',  pos: colPositions.qty  }
        ].filter(c => c.pos >= 0).sort((a, b) => a.pos - b.pos);

        if (sortedCols.length < 2) {
            console.warn('Không đủ cột được xác định để parse bảng');
            return null;
        }

        const colBounds = sortedCols.map((col, idx) => {
            const nextPos = idx < sortedCols.length - 1 ? sortedCols[idx + 1].pos : 9999;
            const prevPos = idx > 0 ? sortedCols[idx - 1].pos : 0;
            return {
                key: col.key,
                start: idx === 0 ? 0 : Math.round((col.pos + prevPos) / 2),
                end:   idx === sortedCols.length - 1 ? 9999 : Math.round((col.pos + nextPos) / 2)
            };
        });

        const getColText = (line, colKey) => {
            const bound = colBounds.find(b => b.key === colKey);
            if (!bound) return '';
            const start = Math.min(bound.start, line.length);
            const end   = Math.min(bound.end, line.length);
            return line.slice(start, end).trim();
        };

        // Hàm parse số lượng thông minh - phát hiện decimal separator từ context
        const parseQuantity = (text) => {
            if (!text) return '';
            // Lấy phần số, giữ dấu thập phân
            const numStr = text.replace(/[^\d.,]/g, '').trim();
            if (!numStr) return '';

            // Nếu có cả dấu phẩy và dấu chấm → giả định dấu chấm là decimal, phẩy là nghìn separator
            // VD: '1.234,56' → 1234.56 (VN) hoặc '1,234.56' → 1234.56 (US)
            let hasComma = numStr.includes(',');
            let hasDot = numStr.includes('.');

            if (hasComma && hasDot) {
                // Xác định separator cuối cùng
                const lastComma = numStr.lastIndexOf(',');
                const lastDot = numStr.lastIndexOf('.');
                if (lastComma > lastDot) {
                    // Format: 1.234,56 (VN) → loại bỏ dấu chấm, thay phẩy bằng chấm
                    return parseFloat(numStr.replace(/\./g, '').replace(',', '.'));
                } else {
                    // Format: 1,234.56 (US) → loại bỏ dấu phẩy
                    return parseFloat(numStr.replace(/,/g, ''));
                }
            } else if (hasComma) {
                // Chỉ có phẩy: kiểm tra xem là decimal separator hay thousands separator
                // Nếu sau phẩy có đúng 1-2 số → có thể là decimal (VN)
                const parts = numStr.split(',');
                if (parts.length === 2 && (parts[1].length === 1 || parts[1].length === 2)) {
                    return parseFloat(numStr.replace(',', '.'));
                }
                return parseFloat(numStr.replace(/,/g, ''));
            } else if (hasDot) {
                // Chỉ có chấm: kiểm tra xem có phải decimal không
                const parts = numStr.split('.');
                if (parts.length === 2 && (parts[1].length === 1 || parts[1].length === 2)) {
                    return parseFloat(numStr);
                }
                // Nếu có đúng 3 chữ số sau dấu chấm (như 2.704) → coi là hàng nghìn
                return parseFloat(numStr.replace(/\./g, ''));
            }

            return parseFloat(numStr);
        };

        // Đọc từng dòng dữ liệu bên dưới tiêu đề
        const parsed = [];
        let currentRow = null;

        for (let i = headerLineIdx + 1; i < lines.length; i++) {
            const line = lines[i];
            if (line.length < 3) continue;

            const norm = normalizeStr(line);

            // Dừng nếu gặp dòng tổng kết
            if (norm.includes('tong cong') || norm.includes('tong so') || norm.includes('ky ten') || norm.includes('ghi chu')) break;

            const codeVal = getColText(line, 'code').replace(/\s+/g, ' ').trim();
            const nameVal = getColText(line, 'name').replace(/\s+/g, ' ').trim();
            const unitVal = getColText(line, 'unit').replace(/\s+/g, ' ').trim();
            const qtyRaw  = getColText(line, 'qty').trim();
            const qtyVal  = this.parseQuantity(qtyRaw);

            // Dòng có STT (số thứ tự đầu dòng) → bắt đầu record mới
            const sttMatch = codeVal.match(/^(\d{1,3})\s+(.*)/);
            let finalCode = codeVal;
            if (sttMatch) finalCode = sttMatch[2].trim();

            // Nếu có ít nhất tên hoặc mã hoặc số lượng → tạo record mới
            if (finalCode || nameVal || qtyVal) {
                if (currentRow) parsed.push(currentRow);

                // Tra cứu trong productsMap
                let matchedProduct = null;
                const codeLower = finalCode.toLowerCase();
                if (finalCode && this.productsMap[codeLower]) {
                    matchedProduct = this.productsMap[codeLower];
                } else if (nameVal) {
                    const nameLower = nameVal.toLowerCase();
                    for (const k in this.productsMap) {
                        if (this.productsMap[k].name.toLowerCase() === nameLower) {
                            matchedProduct = this.productsMap[k];
                            break;
                        }
                    }
                }

                currentRow = {
                    code: matchedProduct ? matchedProduct.code : finalCode,
                    name: matchedProduct ? matchedProduct.name : nameVal,
                    scanned_name: nameVal,
                    quantity: qtyVal || '',
                    unit: unitVal || (matchedProduct ? matchedProduct.unit : 'Cái'),
                    batch_number: '',
                    expiry_date: '',
                    warehouse_location: matchedProduct ? matchedProduct.location : '',
                    unit_price: matchedProduct ? matchedProduct.price : 0
                };
            } else if (currentRow && nameVal) {
                // Dòng phụ (multi-line cell): nối thêm tên
                currentRow.name += ' ' + nameVal;
                currentRow.scanned_name += ' ' + nameVal;
            }
        }

        if (currentRow) parsed.push(currentRow);

        // Lọc bỏ dòng rác (không có tên và không có số lượng)
        return parsed.filter(r => (r.name && r.name.length > 1) || r.quantity);
    },

    // Quét và nhận diện cấu trúc dạng BẢNG cho hóa đơn PDF (Hỗ trợ nhiều dòng trong 1 ô)
    parsePdfTableData(items) {
        if (!items || items.length === 0) return null;

        let yDirection = 1;
        if (items.length > 5) {
            let firstY = items[0].transform[5];
            let lastY = items[items.length - 1].transform[5];
            if (firstY < lastY) yDirection = -1; 
        }

        let linesObj = {};
        items.forEach(item => {
            let str = item.str.trim();
            if (!str) return;
            let y = item.transform[5];
            let x = item.transform[4];
            
            let foundY = null;
            for (let existingY in linesObj) {
                if (Math.abs(Number(existingY) - y) <= 8) {
                    foundY = existingY;
                    break;
                }
            }
            if (foundY === null) {
                foundY = y;
                linesObj[foundY] = [];
            }
            linesObj[foundY].push({ text: str, x: x, y: y, size: Math.abs(item.transform[0]) });
        });

        let sortedY = Object.keys(linesObj).map(Number).sort((a, b) => {
            return yDirection === 1 ? b - a : a - b;
        });

        let lines = [];
        
        let normalizeString = (s) => {
            return s.toLowerCase()
                .replace(/[áàảãạăắằẳẵặâấầẩẫậ]/g, 'a')
                .replace(/[éèẻẽẹêếềểễệ]/g, 'e')
                .replace(/[íìỉĩị]/g, 'i')
                .replace(/[óòỏõọôốồổỗộơớờởỡợ]/g, 'o')
                .replace(/[úùủũụưứừửữự]/g, 'u')
                .replace(/[ýỳỷỹỵ]/g, 'y')
                .replace(/[đ]/g, 'd')
                .replace(/[^a-z0-9]/g, '');
        };

        sortedY.forEach(y => {
            let lineItems = linesObj[y].sort((a, b) => a.x - b.x);
            let fullStr = lineItems.map(i => i.text).join(' ');
            let normStr = normalizeString(fullStr);
            lines.push({ y: Number(y), items: lineItems, normStr, rawStr: fullStr });
        });

        // Tìm cao độ (Y) của dòng tiêu đề
        let headerY = null;
        for (let i = 0; i < lines.length; i++) {
            let s = lines[i].normStr;
            if (s.includes('mahang') || s.includes('mavattu') || s.includes('masp') || s.includes('mavt') || s.includes('mahh') ||
                s.includes('tenhang') || s.includes('tenvattu') || s.includes('tensp') || s.includes('tenvt') || s.includes('tenhh')) {
                headerY = lines[i].y;
                break;
            }
        }

        if (headerY === null) return null; 

        // Thu thập toàn bộ các từ nằm trong dải băng ngang của tiêu đề (+/- 18px)
        let headerItems = items.filter(it => Math.abs(it.transform[5] - headerY) < 18);
        headerItems.sort((a, b) => a.transform[4] - b.transform[4]);

        // Gộp các chữ liền kề trong dòng tiêu đề với ngưỡng scale-invariant
        let mergedHeaders = [];
        let curHeader = null;
        
        let avgWidth = headerItems.length > 0 ? headerItems.reduce((acc, it) => acc + (it.size || 6), 0) / headerItems.length : 6;
        if (avgWidth < 1) avgWidth = 6;
        let mergeThreshold = Math.max(avgWidth * 3.5, 25); 
        
        for (let it of headerItems) {
            let itX = it.transform[4];
            let itStr = it.str.trim();
            if (!itStr) continue;

            if (!curHeader) {
                curHeader = { text: itStr, x: itX, endX: itX + (itStr.length * avgWidth * 0.7) };
            } else {
                if (itX - curHeader.endX < mergeThreshold) { 
                    curHeader.text += ' ' + itStr;
                    curHeader.endX = itX + (itStr.length * avgWidth * 0.7);
                } else {
                    mergedHeaders.push(curHeader);
                    curHeader = { text: itStr, x: itX, endX: itX + (itStr.length * avgWidth * 0.7) };
                }
            }
        }
        if (curHeader) mergedHeaders.push(curHeader);

        for (let i = 0; i < mergedHeaders.length; i++) {
            let startX = (i === 0) ? -9999 : (mergedHeaders[i-1].x + mergedHeaders[i].x) / 2;
            let endX = (i === mergedHeaders.length - 1) ? 9999 : (mergedHeaders[i].x + mergedHeaders[i+1].x) / 2;
            mergedHeaders[i].start = startX;
            mergedHeaders[i].end = endX;
            mergedHeaders[i].normText = normalizeString(mergedHeaders[i].text);
        }

        let colBounds = { code: null, name: null, unit: null, quantity: null };
        let scores = {
            code: { header: null, score: 0 },
            name: { header: null, score: 0 },
            unit: { header: null, score: 0 },
            quantity: { header: null, score: 0 }
        };

        mergedHeaders.forEach(h => {
            let nText = h.normText;

            // Score for Code
            let codeScore = 0;
            if (nText === 'ma' || nText === 'mavt' || nText === 'mahh' || nText === 'masp') codeScore = 100;
            else if (nText.includes('mavattu') || nText.includes('mahanghoa') || nText.includes('masanpham')) codeScore = 90;
            else if (nText.includes('mahang') || nText.includes('mavt') || nText.includes('code') || nText.includes('macode')) codeScore = 80;
            else if (nText.includes('ma')) codeScore = 40;
            if (codeScore > scores.code.score) {
                scores.code.score = codeScore;
                scores.code.header = h;
            }

            // Score for Name
            let nameScore = 0;
            if (nText === 'ten' || nText === 'tenvt' || nText === 'tenhh' || nText === 'tensp') nameScore = 100;
            else if (nText.includes('tenvattu') || nText.includes('tenhanghoa') || nText.includes('tensanpham')) nameScore = 90;
            else if (nText.includes('tenhang') || nText.includes('tenvt') || nText.includes('name') || nText.includes('mota')) nameScore = 80;
            else if (nText.includes('ten')) nameScore = 40;
            if (nameScore > scores.name.score) {
                scores.name.score = nameScore;
                scores.name.header = h;
            }

            // Score for Unit
            let unitScore = 0;
            if (nText === 'dvt' || nText === 'donvitinh' || nText === 'donvi') unitScore = 100;
            else if (nText.includes('dvt') || nText.includes('donvi')) unitScore = 80;
            else if (nText.includes('tinh')) unitScore = 30;
            if (unitScore > scores.unit.score) {
                scores.unit.score = unitScore;
                scores.unit.header = h;
            }

            // Score for Quantity (nhận/nhập/thực tế)
            let qtyScore = 0;
            if (nText.includes('soluongnhan') || nText.includes('slnhan') || nText.includes('soluongnhap') || nText.includes('slnhap') || nText.includes('thucnhan') || nText.includes('thucnhap')) {
                qtyScore = 100; // Cực kỳ ưu tiên cột nhận/nhập
            } else if ((nText.includes('soluong') || nText.includes('sl')) && !nText.includes('giao')) {
                qtyScore = 80; // Số lượng chung
            } else if (nText.includes('soluong') || nText.includes('sl')) {
                qtyScore = 50; // Số lượng bất kỳ (kể cả giao)
            } else if (nText.includes('giao') || nText.includes('nhap')) {
                qtyScore = 30;
            }
            if (qtyScore > scores.quantity.score) {
                scores.quantity.score = qtyScore;
                scores.quantity.header = h;
            }
        });

        if (scores.code.score > 0) colBounds.code = scores.code.header;
        if (scores.name.score > 0) colBounds.name = scores.name.header;
        if (scores.unit.score > 0) colBounds.unit = scores.unit.header;
        if (scores.quantity.score > 0) colBounds.quantity = scores.quantity.header;

        if (!colBounds.code && !colBounds.name) return null;

        let colsData = { code: [], name: [], unit: [], quantity: [] };
        
        items.forEach(it => {
            let x = it.transform[4];
            let y = it.transform[5];
            let text = it.str.trim();
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
            if (finalAnchors.length === 0) {
                finalAnchors.push(a);
            } else {
                let last = finalAnchors[finalAnchors.length - 1];
                if (Math.abs(last.y - a.y) > 20) { 
                    finalAnchors.push(a);
                }
            }
        });

        let rows = finalAnchors.map(a => ({
            anchorY: a.y,
            codeItems: [],
            nameItems: [],
            unitItems: [],
            quantityItems: []
        }));

        if (rows.length === 0) return null;

        let assignToRow = (itemsArray, targetCol) => {
            itemsArray.forEach(it => {
                let closestRow = null;
                let minDiff = 9999;
                rows.forEach(r => {
                    let diff = Math.abs(r.anchorY - it.y);
                    if (diff < minDiff) {
                        minDiff = diff;
                        closestRow = r;
                    }
                });
                if (closestRow && minDiff < 50) { 
                    closestRow[targetCol].push(it);
                }
            });
        };

        assignToRow(colsData.code, 'codeItems');
        assignToRow(colsData.name, 'nameItems');
        assignToRow(colsData.unit, 'unitItems');
        assignToRow(colsData.quantity, 'quantityItems');

        let parsed = [];

        rows.forEach(r => {
            let sortAndJoin = (arr) => arr.sort((a, b) => {
                if (Math.abs(a.y - b.y) > 5) {
                    return yDirection === 1 ? b.y - a.y : a.y - b.y;
                }
                return a.x - b.x;
            }).map(i => i.text).join(' ').trim();

            let codeVal = sortAndJoin(r.codeItems);
            // Loại bỏ số thứ tự (STT) nếu bị dính vào mã hàng hoá
            codeVal = codeVal.replace(/^\d+\s+/, '').trim();
            
            let nameVal = sortAndJoin(r.nameItems);
            let unitVal = sortAndJoin(r.unitItems);
            let qtyRaw = sortAndJoin(r.quantityItems);
            let qtyVal = this.parseQuantity(qtyRaw);

            if (!codeVal && !nameVal && !qtyVal) return; 
            if (nameVal.toLowerCase().includes('tổng') || nameVal.toLowerCase().includes('cộng') || codeVal.toLowerCase().includes('tổng')) return;

            let finalQty = qtyVal;
            if (isNaN(finalQty)) finalQty = '';

            let foundCode = codeVal;
            let matchedProduct = null;
            let codeLower = foundCode.toLowerCase();

            if (foundCode && this.productsMap[codeLower]) {
                matchedProduct = this.productsMap[codeLower];
            } else if (nameVal) {
                let searchName = nameVal.toLowerCase();
                for (const k in this.productsMap) {
                    if (this.productsMap[k].name.toLowerCase() === searchName) {
                        matchedProduct = this.productsMap[k];
                        foundCode = matchedProduct.code;
                        break;
                    }
                }
            }

            let finalName = matchedProduct ? matchedProduct.name : nameVal;
            if (!foundCode && matchedProduct) foundCode = matchedProduct.code;

            parsed.push({
                code: foundCode || '',
                name: finalName || '',
                scanned_name: nameVal,
                quantity: finalQty || '',
                unit: unitVal ? (unitVal.charAt(0).toUpperCase() + unitVal.slice(1).toLowerCase()) : (matchedProduct ? matchedProduct.unit : 'Cái'),
                batch_number: '',
                expiry_date: '',
                warehouse_location: matchedProduct ? matchedProduct.location : '',
                unit_price: matchedProduct ? matchedProduct.price : 0
            });
        });

        return parsed.length > 0 ? parsed : null;
    },

    // Gửi dữ liệu đồng bộ về Livewire
    submitParsedData() {
        if (this.ocrParsedRows.length === 0) {
            alert('Không có dữ liệu hợp lệ để đồng bộ!');
            return;
        }
        $wire.importParsedData(this.ocrParsedRows);
        this.ocrParsedRows = [];
        this.ocrImageSrc = '';
        this.ocrStatus = '';
        this.ocrProgress = 0;
        this.ocrMaximized = false; // Tự động đóng/thu nhỏ cửa sổ khi đã xác nhận đưa dữ liệu vào tồn kho
    }
}">
    <!-- Thư viện PDF.js và Tesseract.js phục vụ đọc PDF và quét OCR trực tiếp ở Browser -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script>
        // Chỉ định đường dẫn worker cho PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js';
    </script>

    <!-- Top Bar: Tabs & Header Info -->
    <div class="flex items-center justify-between gap-2 mb-4">
        <!-- Tab Navigation -->
        <div class="bg-white p-2 rounded-2xl shadow-md border border-slate-200 flex items-center gap-3 w-fit no-print shrink-0">
            <button wire:click="$set('activeTab', 'form')" class="px-8 py-3 rounded-xl text-[13px] font-black transition-all flex items-center gap-2 <?php echo e($activeTab === 'form' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'); ?>">
                <span>📥</span> LẬP PHIẾU NHẬP
            </button>
            <button wire:click="$set('activeTab', 'list')" class="px-8 py-3 rounded-xl text-[13px] font-black transition-all flex items-center gap-2 <?php echo e($activeTab === 'list' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50'); ?>">
                <span>📋</span> DANH SÁCH PHIẾU
            </button>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'form'): ?>
        <!-- Header Info (moved up) -->
        <div class="flex-1 bg-white p-2 rounded-2xl shadow-md border border-slate-200 flex items-center gap-2">
            <div class="flex-1 space-y-1">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Nhà cung cấp</label>
                <input type="text" wire:model="supplier_name" list="suppliers_list" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Chọn hoặc nhập tên...">
                <datalist id="suppliers_list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($supplier->name); ?>"></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </datalist>
            </div>
            <div class="flex-1 space-y-1">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Ngày nhập kho</label>
                <input type="date" wire:model="stock_in_date" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800">
            </div>
            <div class="flex-1 space-y-1">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Hãng sản xuất</label>
                <input type="text" wire:model="manufacturer" list="brands_list" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Nhập hãng SX...">
                <datalist id="brands_list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($brand); ?>"></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </datalist>
            </div>
            <div class="flex-1 space-y-1">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Loại nhập</label>
                <select wire:model.live="type" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-black text-slate-800 appearance-none">
                    <option value="purchase_produced">🛒 MUA HÀNG TP</option>
                    <option value="return_produced">↩️ TRẢ HÀNG TP</option>
                    <option value="production">🏭 TỪ SẢN XUẤT</option>
                    <option value="import_material">📦 NGUYÊN VẬT LIỆU</option>
                </select>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="flex-1 main-content">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'form'): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="mb-4 p-2 bg-emerald-100 text-emerald-800 rounded-2xl font-bold flex items-center gap-2 border border-emerald-200 animate-in fade-in slide-in-from-top-2">
                    <span>✅</span> <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                <div class="mb-4 p-2 bg-red-100 text-red-800 rounded-2xl font-bold flex items-center gap-2 border border-red-200 animate-in fade-in slide-in-from-top-2">
                    <span>❌</span> <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="mb-4 p-2 bg-rose-50 text-rose-700 rounded-2xl font-bold border border-rose-200 animate-in fade-in slide-in-from-top-2">
                    <div class="flex items-center gap-2 mb-2 text-rose-800">
                        <span>❌</span> <span>Có lỗi xảy ra:</span>
                    </div>
                    <ul class="list-disc list-inside text-[13px] ml-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li><?php echo e($error); ?></li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden flex flex-col h-full">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 flex items-center justify-between shrink-0">
                    <h2 class="text-[14px] font-black text-slate-800 flex items-center gap-2 uppercase tracking-tight">
                        <span class="p-1.5 bg-indigo-600 text-white rounded-lg shadow-sm">📥</span>
                        PHIẾU NHẬP KHO MỚI
                    </h2>

                    <!-- Nút Nhập Tự Động cực kỳ sang trọng -->
                    <button type="button" wire:click="$set('showImportModal', true)" 
                            class="px-3 py-1.5 text-xs font-black text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg shadow-sm transition-all duration-150 flex items-center gap-1.5 active:scale-95 no-print">
                        ⚡ Nhập từ Excel / PDF / Ảnh AI
                    </button>
                </div>
                
                <div class="p-2 flex-1 flex flex-col min-h-0">

        <!-- Grid removed from here as it moved to top bar -->

        <div class="overflow-y-auto border border-slate-200 rounded-lg shadow-sm mb-3 bg-slate-50/30 flex-1">
                        <table class="w-full border-collapse relative">
                            <thead class="sticky top-0 z-10">
                                <tr class="bg-sky-100">
                                    <th class="px-2 py-1.5 text-left text-[11px] font-bold text-sky-800 uppercase tracking-widest border-b border-sky-200 min-w-[200px]">Vật tư</th>
                                    <th class="px-1 py-1.5 text-left text-[11px] font-bold text-sky-800 uppercase tracking-widest border-b border-sky-200 w-24">Mã Code NCC</th>
                                    <th class="px-1 py-1.5 text-left text-[11px] font-bold text-sky-800 uppercase tracking-widest border-b border-sky-200 w-28">Hạn dùng</th>
                                    <th class="px-1 py-1.5 text-left text-[11px] font-bold text-sky-800 uppercase tracking-widest border-b border-sky-200 w-20">Vị trí</th>
                                    <th class="px-1 py-1.5 text-center text-[11px] font-bold text-sky-800 uppercase tracking-widest border-b border-sky-200 w-16">SL</th>
                                    <th class="px-1 py-1.5 text-center text-[11px] font-bold text-sky-800 uppercase tracking-widest border-b border-sky-200 w-12">ĐVT</th>
                                    <th class="px-1 py-1.5 border-b border-sky-200 w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                    <!-- Cột Vật tư -->
                                    <td class="px-2 py-1">
                                        <input type="text" wire:model.live.debounce.250ms="items.<?php echo e($index); ?>.product_search" list="product_list_<?php echo e($index); ?>"
                                               class="w-full rounded-md text-[12px] font-bold focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1 px-2 <?php echo e(empty($item['product_id']) ? 'border-orange-300 bg-orange-50/40 focus:ring-orange-100 text-orange-900 placeholder:text-orange-300' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-800'); ?>"
                                               placeholder="Mã hoặc tên vật tư...">
                                        <datalist id="product_list_<?php echo e($index); ?>">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($product->code); ?> - <?php echo e($product->name); ?>"></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </datalist>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["items.{$index}.product_id"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-500 text-[10px] mt-0.5 font-bold"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    
                                    <!-- Cột Mã Code NCC -->
                                    <td class="px-1 py-1">
                                        <input type="text" wire:model.live="items.<?php echo e($index); ?>.batch_number"
                                               class="w-full rounded-md text-[11px] font-black focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1 px-1.5 <?php echo e(empty($item['batch_number']) ? 'border-orange-300 bg-orange-50/40 focus:ring-orange-100 text-orange-900 placeholder:text-orange-300' : 'border-slate-200 bg-slate-50 focus:bg-white text-indigo-700'); ?>" 
                                               placeholder="Mã NCC...">
                                    </td>
                                    
                                    <!-- Cột Hạn dùng -->
                                    <td class="px-1 py-1">
                                        <input type="date" wire:model="items.<?php echo e($index); ?>.expiry_date"
                                               class="w-full rounded-md text-[11px] focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1 px-1.5 <?php echo e(empty($item['expiry_date']) ? 'border-orange-300 bg-orange-50/40 focus:ring-orange-100 text-orange-900' : 'border-slate-200 bg-slate-50 focus:bg-white font-bold text-slate-700'); ?>">
                                    </td>
                                    
                                    <!-- Cột Vị trí -->
                                    <td class="px-1 py-1">
                                        <input type="text" wire:model="items.<?php echo e($index); ?>.warehouse_location"
                                               class="w-full text-[11px] font-bold rounded-md focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1 px-1.5 <?php echo e(empty($item['warehouse_location']) ? 'border-orange-300 bg-orange-50/40 focus:ring-orange-100 text-orange-900 placeholder:text-orange-300' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-700'); ?>" 
                                               placeholder="Vị trí...">
                                    </td>
                                    
                                    <!-- Cột Số lượng -->
                                    <td class="px-1 py-1">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.<?php echo e($index); ?>.quantity"
                                               class="w-full text-center text-[12px] font-black rounded-md focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1 px-1 <?php echo e((empty($item['quantity']) || $item['quantity'] <= 0) ? 'border-orange-300 bg-orange-50/40 focus:ring-orange-100 text-orange-900' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-900'); ?>"
                                               placeholder="0">
                                    </td>
                                    
                                    <td class="px-1 py-1 text-center">
                                        <span class="text-[10px] font-black text-slate-500 bg-slate-100 px-1 py-0.5 rounded border border-slate-200 uppercase"><?php echo e($items[$index]['unit'] ?? '-'); ?></span>
                                    </td>
                                    <td class="px-1 py-1 text-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($items) > 1): ?>
                                            <button wire:click="removeItem(<?php echo e($index); ?>)" class="text-slate-300 hover:text-rose-600 transition-all p-1 rounded-md hover:bg-rose-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($items) > 0): ?>
                            <tfoot class="border-t border-slate-200 sticky bottom-0 z-10">
                                <tr class="bg-indigo-50/90 backdrop-blur-sm">
                                    <td colspan="4" class="px-2 py-1.5 text-right font-black text-slate-500 uppercase tracking-widest text-[10px]">Tổng số lượng:</td>
                                    <td colspan="3" class="px-2 py-1.5 text-left font-black text-indigo-900 text-[14px] underline decoration-double">
                                        <?php echo e(number_format(collect($items)->sum(fn($item) => (float)($item['quantity'] ?? 0)))); ?>

                                    </td>
                                </tr>
                            </tfoot>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </table>
                    </div>

        <div class="flex items-center gap-2 mb-3 shrink-0">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canAddItem()): ?>
                <button wire:click="addItem" class="bg-slate-800 text-white px-4 py-2 rounded-lg text-[11px] font-black flex items-center gap-1.5 hover:bg-indigo-600 transition-all shadow-sm active:scale-95">
                    <span>➕</span> THÊM DÒNG MỚI
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button wire:click="openProductModal" class="bg-white border border-emerald-600 text-emerald-700 px-4 py-2 rounded-lg text-[11px] font-black flex items-center gap-1.5 hover:bg-emerald-50 transition-all shadow-sm active:scale-95">
                <span>📦</span> TẠO NHANH VẬT TƯ
            </button>
        </div>

        <div class="border-t border-slate-150 pt-3 flex items-center justify-between shrink-0">
            <div class="w-2/3">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest px-1 mb-0.5">Ghi chú phiếu nhập</label>
                <textarea wire:model="note" rows="1" class="w-full rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-1.5 px-3 text-[12px] font-bold text-slate-800 placeholder:font-normal" placeholder="Lý do nhập kho, số chứng từ kèm theo..."></textarea>
            </div>
            <button wire:click="save" class="px-8 py-3 rounded-xl text-[13px] font-black text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 shadow-md shadow-indigo-100 hover:shadow-indigo-200 transition-all flex items-center gap-2 transform hover:-translate-y-0.5 active:translate-y-0">
                <span>💾</span> LƯU PHIẾU NHẬP
            </button>
        </div>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- TAB DANH SÁCH PHIẾU -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'list'): ?>
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2 no-print">
                    <div class="flex items-center gap-3">
                        <input type="date" wire:model.live="listDateFrom" class="rounded-xl border-slate-200 text-[13px] font-bold focus:ring-4 focus:ring-indigo-100 py-2 px-3 bg-white">
                        <span class="text-slate-400 font-black">➔</span>
                        <input type="date" wire:model.live="listDateTo" class="rounded-xl border-slate-200 text-[13px] font-bold focus:ring-4 focus:ring-indigo-100 py-2 px-3 bg-white">
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">🔍</span>
                            <input type="text" wire:model.live.debounce.300ms="listSearch" class="pl-9 pr-4 py-2 w-64 text-[13px] font-bold rounded-xl border border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 bg-white" placeholder="Tìm số phiếu, supplier...">
                        </div>
                        
                        <button wire:click="exportExcel" class="px-4 py-2 text-xs font-black text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl flex items-center gap-1.5">
                            📤 Xuất Excel
                        </button>

                        <button wire:click="printSelected" 
                                <?php if(empty($selectedIds)): ?> disabled class="px-4 py-2 text-xs font-black text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed"
                                <?php else: ?> class="px-4 py-2 text-xs font-black text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl flex items-center gap-1.5"
                                <?php endif; ?>>
                            🖨️ In tích chọn (<?php echo e(count($selectedIds)); ?>)
                        </button>

                        <button wire:click="deleteSelected" 
                                onclick="confirm('Lưu ý: Số lượng tồn kho tương ứng sẽ bị giảm trừ khi xóa phiếu nhập. Tiếp tục?') || event.stopImmediatePropagation()"
                                <?php if(empty($selectedIds)): ?> disabled class="px-4 py-2 text-xs font-black text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed"
                                <?php else: ?> class="px-4 py-2 text-xs font-black text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl flex items-center gap-1.5"
                                <?php endif; ?>>
                            🗑️ Xóa đã chọn (<?php echo e(count($selectedIds)); ?>)
                        </button>

                        <button wire:click="openEditModal" 
                                <?php if(count($selectedIds) !== 1): ?> disabled class="px-4 py-2 text-xs font-black text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed"
                                <?php else: ?> class="px-4 py-2 text-xs font-black text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl flex items-center gap-1.5"
                                <?php endif; ?>>
                            ✏️ Sửa
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[11px] font-black text-white uppercase tracking-widest bg-slate-800 border-b border-slate-700">
                            <?php
                                $allOnPage = \App\Models\StockIn::whereBetween('created_at', [$listDateFrom . ' 00:00:00', $listDateTo . ' 23:59:59'])
                                    ->where(function($q) {
                                        $q->where('code', 'like', '%' . $this->listSearch . '%')
                                          ->orWhere('supplier_name', 'like', '%' . $this->listSearch . '%');
                                    })
                                    ->latest()
                                    ->paginate(15);
                                $idsOnPage = $allOnPage->pluck('id')->toArray();
                            ?>
                            <tr>
                                <th class="px-2 py-2 w-10 no-print text-center">
                                    <input type="checkbox" wire:click="toggleSelectAll([<?php echo e(implode(',', $idsOnPage)); ?>])" <?php echo e(count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : ''); ?> class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500">
                                </th>
                                <th class="px-2 py-4">MÃ PHIẾU</th>
                                <th class="px-2 py-2">NGÀY TẠO</th>
                                <th class="px-2 py-2">NGÀY NHẬP</th>
                                <th class="px-2 py-2">NHÀ CUNG CẤP / ĐỐI TÁC</th>
                                <th class="px-2 py-2">LOẠI NHẬP</th>
                                <th class="px-2 py-2 text-right">TỔNG TIỀN</th>
                                <th class="px-2 py-2">GHI CHÚ</th>
                                <th class="px-2 py-2 text-center no-print">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $allOnPage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $si): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="hover:bg-indigo-50/30 transition-all group <?php echo e(in_array($si->id, $selectedIds) ? 'bg-indigo-50' : ''); ?>">
                                    <td class="px-2 py-1.5 no-print text-center">
                                        <input type="checkbox" wire:model.live="selectedIds" value="<?php echo e($si->id); ?>" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-2 py-4 font-black text-indigo-700 tracking-tight"><?php echo e($si->code); ?></td>
                                    <td class="px-2 py-1.5 text-slate-500 text-[12px] font-bold"><?php echo e($si->created_at->format('d/m/Y H:i')); ?></td>
          <td class="px-2 py-1.5 text-slate-500 text-[12px] font-bold"><?php echo e($si->stock_in_date ? $si->stock_in_date->format('d/m/Y') : $si->created_at->format('d/m/Y')); ?></td>
                                    <td class="px-2 py-1.5 font-black text-slate-800 text-[13px] uppercase tracking-tighter"><?php echo e($si->supplier_name ?: ($si->manufacturer ?: '-')); ?></td>
                                    <td class="px-2 py-1.5">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($si->type):
                                            case ('purchase_produced'): ?> <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-black uppercase border border-emerald-100">🛒 MUA HÀNG</span> <?php break; ?>
                                            <?php case ('production'): ?> <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-black uppercase border border-indigo-100">🏭 SẢN XUẤT</span> <?php break; ?>
                                            <?php case ('import_material'): ?> <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-[10px] font-black uppercase border border-amber-100">📦 NGUYÊN LIỆU</span> <?php break; ?>
                                            <?php default: ?> <span class="px-2.5 py-1 bg-slate-50 text-slate-600 rounded-lg text-[10px] font-black uppercase border border-slate-100"><?php echo e($si->type); ?></span>
                                        <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-2 py-1.5 text-right font-black text-slate-900 text-[14px]">
                                        <?php echo e(number_format($si->items->sum('total_amount'))); ?> đ
                                    </td>
                                    <td class="px-2 py-1.5 text-slate-400 text-[11px] font-bold italic truncate max-w-[150px]"><?php echo e($si->note ?: '-'); ?></td>
                                    <td class="px-2 py-1.5 text-center no-print">
                                        <div class="flex items-center justify-center gap-1">
                                            <button wire:click="printSingle(<?php echo e($si->id); ?>)" class="p-2 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="In phiếu">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 0 0 002-2v-4a2 0 0 00-2-2H5a2 0 0 00-2 2v4a2 0 0 002 2h2m2 4h6a2 0 0 002-2v-4a2 0 0 00-2-2H9a2 0 0 00-2 2v4a2 0 0 002 2zm8-12V5a2 0 0 00-2-2H9a2 0 0 00-2 2v4h10z"></path></svg>
                                            </button>
                                            <button wire:click="delete(<?php echo e($si->id); ?>)" onclick="confirm('Bạn có chắc chắn muốn xóa phiếu nhập này? Tồn kho tương ứng sẽ bị giảm trừ.') || event.stopImmediatePropagation()" class="p-2 text-rose-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Xóa phiếu">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-slate-400">Không tìm thấy phiếu nào</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-50 no-print">
                    <?php echo e($allOnPage->links()); ?>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- MODAL NHẬP ĐA PHƯƠNG THỨC TỰ ĐỘNG (EXCEL / PDF / ẢNH AI OCR) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showImportModal): ?>
        <div class="fixed inset-0 z-50 no-print" :class="ocrMaximized ? 'overflow-hidden h-screen w-screen' : 'overflow-y-auto'">
            <div class="flex items-center justify-center min-h-screen text-center" :class="ocrMaximized ? 'h-screen w-screen p-0 items-stretch' : 'pt-4 px-4 pb-20 align-middle'">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" wire:click="$set('showImportModal', false); ocrMaximized = false"></div>
                
                <div :class="ocrMaximized ? 'w-screen h-screen max-w-full my-0 rounded-none flex flex-col' : 'inline-block align-middle sm:max-w-4xl sm:w-full rounded-2xl sm:my-8 border border-slate-150'"
                     class="bg-white text-left overflow-hidden shadow-2xl transform transition-all">
                    
                    <!-- Tab Header -->
                    <div class="bg-slate-55 border-b border-slate-150 px-6 py-4 flex items-center justify-between shrink-0 bg-slate-50">
                        <div class="flex gap-2">
                            <button @click="activeImportTab = 'excel'" 
                                    :class="activeImportTab === 'excel' ? 'border-indigo-650 text-indigo-650 font-black' : 'border-transparent text-slate-500 font-bold hover:text-slate-700'"
                                    class="py-2 px-1 text-[13px] border-b-2 transition duration-150">
                                📥 Nhập từ Excel/CSV linh hoạt
                            </button>
                            <button @click="activeImportTab = 'pdf'" 
                                    :class="activeImportTab === 'pdf' ? 'border-indigo-650 text-indigo-650 font-black' : 'border-transparent text-slate-500 font-bold hover:text-slate-700'"
                                    class="py-2 px-1 text-[13px] border-b-2 transition duration-150 flex items-center gap-1">
                                📋 Nhập từ tệp tin PDF
                                <span class="bg-red-100 text-red-700 text-[9px] px-1.5 py-0.5 rounded font-black uppercase">Mới</span>
                            </button>
                            <button @click="activeImportTab = 'ocr'" 
                                    :class="activeImportTab === 'ocr' ? 'border-indigo-650 text-indigo-650 font-black' : 'border-transparent text-slate-500 font-bold hover:text-slate-700'"
                                    class="py-2 px-1 text-[13px] border-b-2 transition duration-150 flex items-center gap-1.5">
                                📷 Nhận diện từ Ảnh chụp (AI OCR)
                                <span class="bg-indigo-100 text-indigo-800 text-[9px] px-1.5 py-0.5 rounded font-black uppercase">Thông minh</span>
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="ocrMaximized = !ocrMaximized" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/50 transition duration-150 flex items-center justify-center" title="Phóng to / Thu nhỏ">
                                <template x-if="ocrMaximized">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 14h6v6m10-6h-6v6M4 10h6V4m10 6h-6V4"></path></svg>
                                </template>
                                <template x-if="!ocrMaximized">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4m12 4V4h-4M4 16v4h4m12-4v4h-4"></path></svg>
                                </template>
                            </button>
                            <button type="button" wire:click="$set('showImportModal', false)" @click="ocrMaximized = false" class="text-slate-450 hover:text-slate-650 text-lg p-1">✕</button>
                        </div>
                    </div>

                    <!-- Tab Content Wrapper -->
                    <div :class="ocrMaximized ? 'flex-1 overflow-y-auto' : ''">
                        <!-- Tab 1: Nhập từ Excel/CSV -->
                        <div x-show="activeImportTab === 'excel'" class="p-2 space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                                <div class="p-3 bg-red-100 text-red-800 rounded-lg text-xs font-bold border border-red-200">
                                    ❌ <?php echo e(session('error')); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="p-3.5 bg-emerald-50 text-emerald-850 rounded-lg text-xs font-semibold leading-relaxed border border-emerald-100">
                            ✨ <span class="font-extrabold text-emerald-950">Giải pháp đồng bộ cột linh hoạt:</span> Anh/chị có thể sắp xếp thứ tự các cột Excel tùy ý! Hệ thống sẽ quét dòng tiêu đề để bóc tách thông tin tự động. 
                            Những cột nào không tìm thấy hoặc trống thông tin sẽ được <span class="font-black text-orange-700 underline">báo màu cam</span> trên bảng nhập để anh/chị bổ sung nhanh chóng.
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Chọn tệp tin Excel/CSV từ máy tính</label>
                            <input type="file" wire:model="excelFile" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border file:border-slate-200 file:text-xs file:font-bold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['excelFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs font-bold block mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div wire:loading wire:target="excelFile" class="text-xs text-indigo-600 font-bold flex items-center gap-1.5">
                            ⏳ Đang tải tệp tin lên hệ thống...
                        </div>

                        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                            <button type="button" wire:click="$set('showImportModal', false)" class="rounded-lg border border-slate-200 px-4 py-2 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Hủy bỏ
                            </button>
                            <button type="button" wire:click="importExcelData" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-xs font-black text-white transition">
                                Xác nhận nhập Excel
                            </button>
                        </div>
                    </div>

                    <!-- Tab 2: Nhập từ tệp tin PDF -->
                    <div x-show="activeImportTab === 'pdf'" class="p-2 space-y-4">
                        <div class="p-3.5 bg-red-50 text-red-850 rounded-lg text-xs font-semibold leading-relaxed border border-red-100">
                            📋 <span class="font-extrabold text-red-950">Giải pháp xử lý PDF thông minh:</span> Hệ thống sẽ đọc dữ liệu text trực tiếp từ tệp tin PDF hóa đơn/phiếu giao hàng của nhà cung cấp và tự động bóc tách các trường: <i>Mã vật tư, Số lượng, Hạn dùng, Số lô, Vị trí, Đơn giá</i> để điền nhanh vào phiếu nhập!
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- PDF Drag and Drop Zone -->
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-2 flex flex-col items-center justify-center min-h-[200px] bg-slate-50 relative hover:border-red-400 hover:bg-slate-100/50 transition-all cursor-pointer"
                                 @click="$refs.pdfInput.click()"
                                 @dragover.prevent="$el.classList.add('border-red-400', 'bg-slate-100')"
                                 @dragleave.prevent="$el.classList.remove('border-red-400', 'bg-slate-100')"
                                 @drop.prevent="$el.classList.remove('border-red-400', 'bg-slate-100'); handlePdfUpload($event)">
                                
                                <input type="file" x-ref="pdfInput" @change="handlePdfUpload($event)" accept="application/pdf" class="hidden" />

                                <div class="text-center space-y-3 select-none">
                                    <div class="p-3 bg-red-50 text-red-650 rounded-full inline-block">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[13px] font-black text-slate-800">Kéo & Thả tệp PDF hóa đơn vào đây</p>
                                        <p class="text-xs text-slate-500 font-bold">Hoặc nhấp để chọn tệp từ thiết bị</p>
                                    </div>
                                    <button type="button" class="mt-2 px-4 py-2 bg-red-650 text-white font-black text-xs rounded-xl shadow-md shadow-red-100 hover:bg-red-700 transition">
                                        📁 Chọn tệp PDF hóa đơn
                                    </button>
                                </div>
                            </div>

                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-150 flex flex-col justify-between">
                                <div class="space-y-3">
                                    <h4 class="text-xs font-bold text-slate-700 uppercase">Trạng thái phân tích PDF</h4>
                                    <p class="text-xs font-semibold text-slate-650" x-text="ocrStatus || 'Đang đợi tải tệp PDF...'"></p>
                                    
                                    <template x-if="ocrRunning">
                                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-red-600 h-2 rounded-full transition-all duration-300" :style="`width: ${ocrProgress}%`"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Lưới xem trước PDF -->
                        <template x-if="ocrParsedRows.length > 0">
                            <div class="border border-slate-200 rounded-lg overflow-hidden bg-white mt-4 shadow-sm">
                                <div class="bg-slate-55 px-4 py-3.5 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                        <span>📋</span> Lưới xem trước bóc tách từ tệp PDF
                                    </h4>
                                    <span class="text-[10px] text-orange-700 font-black bg-orange-50 px-2 py-1 rounded-lg border border-orange-200">Các cột thiếu thông tin sẽ báo màu cam trên phiếu để anh/chị điền nhanh</span>
                                </div>
                                <div class="max-h-[260px] overflow-y-auto">
                                    <table class="w-full text-left text-xs border-collapse">
                                        <thead>
                                            <tr class="bg-slate-800 font-black border-b border-slate-700 text-white uppercase tracking-widest text-[10px]">
                                                <th class="p-3 w-40 text-center">Mã vật tư (Mã HH)</th>
                                                <th class="p-3 text-left">Tên vật tư (Tên HH)</th>
                                                <th class="p-3 w-28 text-center">ĐVT</th>
                                                <th class="p-3 w-28 text-center">Số lượng (Nhận)</th>
                                                <th class="p-3 text-center w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, idx) in ocrParsedRows" :key="idx">
                                                <tr class="border-b border-slate-150 hover:bg-indigo-50/20 transition-colors">
                                                    <!-- Cột Mã vật tư -->
                                                    <td class="p-2 text-center">
                                                        <input type="text" x-model="row.code" 
                                                               @input="let p = productsMap[row.code.toLowerCase()]; if(p) { row.name = p.name; row.unit = p.unit; row.unit_price = p.price; row.warehouse_location = p.location; }"
                                                               :class="!row.code ? 'border-orange-400 bg-orange-50/40 text-orange-950 focus:ring-orange-100' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-800 font-extrabold'"
                                                               class="w-full p-2 text-xs text-center rounded-lg transition-all focus:ring-4 focus:ring-indigo-100" placeholder="Mã vật tư..." />
                                                    </td>

                                                    <!-- Cột Tên vật tư -->
                                                    <td class="p-2 space-y-1">
                                                        <template x-if="row.scanned_name && !row.code">
                                                            <div class="text-[10px] font-semibold text-orange-700 bg-orange-50 px-2 py-0.5 rounded border border-orange-200 leading-tight mb-1 flex items-center gap-1 w-fit">
                                                                <span>⚠️ Quét được:</span> <span class="font-bold italic" x-text="row.scanned_name"></span>
                                                            </div>
                                                        </template>
                                                        
                                                        <input type="text" x-model="row.name" 
                                                               :class="!row.name ? 'border-orange-400 bg-orange-50/40 text-orange-950 focus:ring-orange-100' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-800'"
                                                               class="w-full p-2 text-xs font-semibold rounded-lg transition-all focus:ring-4 focus:ring-indigo-100" placeholder="Tên vật tư..." />

                                                        <select x-model="row.code" @change="let p = productsMap[row.code.toLowerCase()]; if(p) { row.code = p.code || row.code.toUpperCase(); row.name = p.name; row.unit = p.unit; row.unit_price = p.price; row.warehouse_location = p.location; }" 
                                                                class="w-full text-[10px] p-1 rounded bg-slate-50 text-slate-500 border border-slate-150 focus:ring-2 focus:ring-indigo-100 mt-1 font-bold">
                                                            <option value="">-- Hoặc chọn nhanh từ danh mục chuẩn... --</option>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                                <option value="<?php echo e($p->code); ?>"><?php echo e($p->code); ?> - <?php echo e($p->name); ?></option>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                        </select>
                                                    </td>

                                                    <!-- Cột ĐVT -->
                                                    <td class="p-2 text-center">
                                                        <input type="text" x-model="row.unit" 
                                                               :class="!row.unit ? 'border-orange-400 bg-orange-50/40 text-orange-950 focus:ring-orange-100' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-800 font-bold'"
                                                               class="w-full p-2 text-xs text-center rounded-lg transition-all focus:ring-4 focus:ring-indigo-100" placeholder="ĐVT" />
                                                    </td>

                                                    <!-- Cột Số lượng -->
                                                    <td class="p-2 text-center">
                                                        <input type="text" x-model="row.quantity" 
                                                               :class="!row.quantity ? 'border-orange-400 bg-orange-50/40 text-orange-950 focus:ring-orange-100' : 'border-slate-250 bg-slate-50 focus:bg-white font-black text-slate-850'"
                                                               class="w-full p-2 text-xs text-center rounded-lg focus:ring-4 focus:ring-indigo-100 transition-all text-slate-850" placeholder="0" />
                                                    </td>

                                                    <!-- Nút xóa dòng preview -->
                                                    <td class="p-2 text-center">
                                                        <button type="button" @click="ocrParsedRows.splice(idx, 1)" class="text-slate-350 hover:text-rose-600 transition-all p-1 rounded-full hover:bg-rose-50">
                                                            ✕
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>

                        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                            <button type="button" wire:click="$set('showImportModal', false)" class="rounded-lg border border-slate-200 px-4 py-2 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Hủy bỏ
                            </button>
                            <button type="button" @click="submitParsedData()" :disabled="ocrParsedRows.length === 0"
                                    class="rounded-lg bg-indigo-650 hover:bg-indigo-750 disabled:bg-slate-350 disabled:cursor-not-allowed px-5 py-2 text-xs font-black text-white shadow-sm transition">
                                💾 Đồng bộ vào phiếu nhập kho
                            </button>
                        </div>
                    </div>

                    <!-- Tab 3: Nhận diện từ Ảnh chụp AI OCR -->
                    <div x-show="activeImportTab === 'ocr'" class="p-2 space-y-4" @paste="handleImagePaste($event)">
                        <div class="p-3 bg-indigo-50 text-indigo-850 rounded-lg text-xs font-semibold leading-relaxed border border-indigo-100">
                            📷 <span class="font-extrabold text-indigo-950">Quét ảnh chụp thông minh:</span> Anh/chị chỉ cần chụp ảnh màn hình bảng Excel hoặc chụp phiếu xuất kho của nhà cung cấp, nhấn **Ctrl + V** để dán trực tiếp ảnh vào đây hoặc chọn ảnh chụp để AI bóc tách nhanh chóng!
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Image Drag and Drop Zone -->
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-2 flex flex-col items-center justify-center min-h-[200px] bg-slate-50 relative hover:border-indigo-400 hover:bg-slate-100/50 transition-all cursor-pointer"
                                 @click="$refs.imageInput.click()"
                                 @dragover.prevent="$el.classList.add('border-indigo-400', 'bg-slate-100')"
                                 @dragleave.prevent="$el.classList.remove('border-indigo-400', 'bg-slate-100')"
                                 @drop.prevent="$el.classList.remove('border-indigo-400', 'bg-slate-100'); handleImageUpload($event)">
                                
                                <input type="file" x-ref="imageInput" @change="handleImageUpload($event)" accept="image/*" class="hidden" />

                                <template x-if="!ocrImageSrc">
                                    <div class="text-center space-y-3 select-none">
                                        <div class="p-3 bg-indigo-50 text-indigo-650 rounded-full inline-block">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-[13px] font-black text-slate-800">Kéo & Thả ảnh chụp vào đây</p>
                                            <p class="text-xs text-slate-500 font-bold">Hoặc dán ảnh <kbd class="px-1.5 py-0.5 bg-slate-200 text-[10px] rounded font-black text-slate-700">Ctrl + V</kbd> hay nhấp để chọn tệp</p>
                                        </div>
                                        <button type="button" class="mt-2 px-4 py-2 bg-indigo-600 text-white font-black text-xs rounded-xl shadow-md shadow-indigo-100 hover:bg-indigo-700 transition">
                                            📁 Chọn ảnh từ máy tính
                                        </button>
                                    </div>
                                </template>
                                
                                <template x-if="ocrImageSrc">
                                    <div class="w-full flex flex-col items-center relative p-2" @click.stop>
                                        <img :src="ocrImageSrc" class="max-h-[160px] rounded-lg shadow-lg border border-slate-200 object-contain" />
                                        <button @click="ocrImageSrc = ''; ocrParsedRows = []" class="absolute -top-2 -right-2 bg-rose-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-black shadow-lg hover:bg-rose-700 transition-transform hover:scale-110">✕</button>
                                    </div>
                                </template>
                            </div>

                            <!-- Trạng thái OCR -->
                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-150 flex flex-col justify-between">
                                <div class="space-y-3">
                                    <h4 class="text-xs font-bold text-slate-700 uppercase">Trạng thái nhận diện AI OCR</h4>
                                    <p class="text-xs font-semibold text-slate-650" x-text="ocrStatus || 'Đang đợi ảnh chụp...'"></p>
                                    
                                    <template x-if="ocrRunning">
                                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="bg-indigo-650 h-2 rounded-full transition-all duration-300" :style="`width: ${ocrProgress}%`"></div>
                                        </div>
                                    </template>
                                </div>

                                <div class="pt-4">
                                    <button type="button" @click="runOCR(); ocrMaximized = true;" :disabled="ocrRunning || !ocrImageSrc" 
                                            class="w-full py-3.5 text-xs font-black text-slate-900 bg-amber-400 hover:bg-amber-500 disabled:bg-slate-200 disabled:text-slate-450 disabled:cursor-not-allowed rounded-xl transition shadow-lg flex items-center justify-center gap-1.5 active:scale-98">
                                        <span>🔍</span> Bắt đầu nhận diện AI OCR
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Lưới xem trước OCR -->
                        <template x-if="ocrParsedRows.length > 0">
                            <div class="border border-slate-200 rounded-lg overflow-hidden bg-white mt-4 shadow-sm">
                                <div class="bg-slate-55 px-4 py-3.5 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                        <span>📋</span> Lưới xem trước bóc tách từ Ảnh chụp AI
                                    </h4>
                                    <span class="text-[10px] text-orange-700 font-black bg-orange-50 px-2 py-1 rounded-lg border border-orange-200">Các cột thiếu thông tin sẽ báo màu cam trên phiếu để anh/chị điền nhanh</span>
                                </div>
                                <div class="max-h-[260px] overflow-y-auto">
                                    <table class="w-full text-left text-xs border-collapse">
                                        <thead>
                                            <tr class="bg-slate-800 font-black border-b border-slate-700 text-white uppercase tracking-widest text-[10px]">
                                                <th class="p-3 w-40 text-center">Mã vật tư</th>
                                                <th class="p-3 text-left">Tên vật tư</th>
                                                <th class="p-3 w-28 text-center">ĐVT</th>
                                                <th class="p-3 w-28 text-center">Số lượng</th>
                                                <th class="p-3 text-center w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, idx) in ocrParsedRows" :key="idx">
                                                <tr class="border-b border-slate-150 hover:bg-indigo-50/20 transition-colors">
                                                    <!-- Cột Mã vật tư -->
                                                    <td class="p-2 text-center">
                                                        <input type="text" x-model="row.code" 
                                                               @input="let p = productsMap[row.code.toLowerCase()]; if(p) { row.name = p.name; row.unit = p.unit; row.unit_price = p.price; row.warehouse_location = p.location; }"
                                                               :class="!row.code ? 'border-orange-400 bg-orange-50/40 text-orange-950 focus:ring-orange-100' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-800 font-extrabold'"
                                                               class="w-full p-2 text-xs text-center rounded-lg transition-all focus:ring-4 focus:ring-indigo-100" placeholder="Mã vật tư..." />
                                                    </td>

                                                    <!-- Cột Tên vật tư -->
                                                    <td class="p-2 space-y-1">
                                                        <template x-if="row.scanned_name && !row.code">
                                                            <div class="text-[10px] font-semibold text-orange-700 bg-orange-50 px-2 py-0.5 rounded border border-orange-200 leading-tight mb-1 flex items-center gap-1 w-fit">
                                                                <span>⚠️ Quét được:</span> <span class="font-bold italic" x-text="row.scanned_name"></span>
                                                            </div>
                                                        </template>
                                                        
                                                        <input type="text" x-model="row.name" 
                                                               :class="!row.name ? 'border-orange-400 bg-orange-50/40 text-orange-950 focus:ring-orange-100' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-800'"
                                                               class="w-full p-2 text-xs font-semibold rounded-lg transition-all focus:ring-4 focus:ring-indigo-100" placeholder="Tên vật tư..." />

                                                        <select x-model="row.code" @change="let p = productsMap[row.code.toLowerCase()]; if(p) { row.code = p.code || row.code.toUpperCase(); row.name = p.name; row.unit = p.unit; row.unit_price = p.price; row.warehouse_location = p.location; }" 
                                                                class="w-full text-[10px] p-1 rounded bg-slate-50 text-slate-500 border border-slate-150 focus:ring-2 focus:ring-indigo-100 mt-1 font-bold">
                                                            <option value="">-- Hoặc chọn nhanh từ danh mục chuẩn... --</option>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                                <option value="<?php echo e($p->code); ?>"><?php echo e($p->code); ?> - <?php echo e($p->name); ?></option>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                        </select>
                                                    </td>

                                                    <!-- Cột ĐVT -->
                                                    <td class="p-2 text-center">
                                                        <input type="text" x-model="row.unit" 
                                                               :class="!row.unit ? 'border-orange-400 bg-orange-50/40 text-orange-950 focus:ring-orange-100' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-800 font-bold'"
                                                               class="w-full p-2 text-xs text-center rounded-lg transition-all focus:ring-4 focus:ring-indigo-100" placeholder="ĐVT" />
                                                    </td>

                                                    <!-- Cột Số lượng -->
                                                    <td class="p-2 text-center">
                                                        <input type="text" x-model="row.quantity" 
                                                               :class="!row.quantity ? 'border-orange-400 bg-orange-50/40 focus:ring-orange-100' : 'border-slate-250 bg-slate-50 focus:bg-white font-black text-slate-850'"
                                                               class="w-full p-2 text-xs text-center rounded-lg focus:ring-4 focus:ring-indigo-100 transition-all text-slate-850" placeholder="0" />
                                                    </td>

                                                    <!-- Nút xóa dòng preview -->
                                                    <td class="p-2 text-center">
                                                        <button type="button" @click="ocrParsedRows.splice(idx, 1)" class="text-slate-350 hover:text-rose-600 transition-all p-1 rounded-full hover:bg-rose-50">
                                                            ✕
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>

                        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                            <button type="button" wire:click="$set('showImportModal', false)" class="rounded-lg border border-slate-200 px-4 py-2 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Hủy bỏ
                            </button>
                            <button type="button" @click="submitParsedData()" :disabled="ocrParsedRows.length === 0"
                                    class="rounded-lg bg-indigo-650 hover:bg-indigo-750 disabled:bg-slate-350 disabled:cursor-not-allowed px-5 py-2 text-xs font-black text-white shadow-sm transition">
                                💾 Đồng bộ vào phiếu nhập kho
                            </button>
                        </div>
                    </div> <!-- Close Tab 3 -->
                </div> <!-- Close Tab Content Wrapper -->
            </div> <!-- Close Modal box -->
        </div> <!-- Close Inner Flex overlay -->
    </div> <!-- Close Fixed Inset outer wrapper -->
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Quick Product Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showProductModal): ?>
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-2">
                <h3 class="text-lg font-bold mb-4">Tạo nhanh vật tư mới</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mã vật tư</label>
                        <input type="text" wire:model="newPCode" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newPCode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tên vật tư</label>
                        <input type="text" wire:model="newPName" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newPName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Đơn vị tính</label>
                        <input type="text" wire:model="newPUnit" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newPUnit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hãng sản xuất (Kế thừa từ header)</label>
                        <input type="text" disabled value="<?php echo e($manufacturer); ?>" class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showProductModal', false)" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button wire:click="createProduct" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Lưu & Thêm dòng</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- PHẦN IN CHI TIẾT HÀNG LOẠT (Nhập kho) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($printItems) > 0): ?>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0; /* Hides browser default header/footer */
            }
            body, html {
                margin: 0;
                padding: 0;
                background-color: white;
            }
            
            /* Ẩn toàn bộ UI của web app */
            body * {
                visibility: hidden;
            }
            
            /* Chỉ hiện thị phần in ấn */
            .print-wrapper, .print-wrapper * {
                visibility: visible;
            }
            
            .print-wrapper {
                position: absolute !important; 
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .print-page {
                width: 100%;
                min-height: 297mm;
                padding: 15mm !important; /* Lề 15mm linh hoạt đều 4 góc */
                box-sizing: border-box;
                margin: 0 auto;
                background: white;
                page-break-after: always;
            }
        }
    </style>
    <div class="hidden print:block print-wrapper z-[9999]">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $printItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="print-page" style="font-family: 'Times New Roman', serif;">
            
            <div class="flex justify-between items-start mb-6 pt-4">
                <div class="text-left leading-relaxed">
                    <h3 class="text-sm font-black uppercase text-slate-900">PHÒNG KỸ THUẬT SỬA CHỮA VINALPHA.</h3>
                    <h4 class="text-sm font-bold uppercase text-slate-900">DỰ ÁN : KHO HÓC MÔN</h4>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-600 font-bold italic"><?php echo e(now()->format('H:i d/m/Y')); ?></p>
                </div>
            </div>

            <div style="text-align: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #0f172a;">
                <h2 class="text-3xl font-black text-slate-900 uppercase tracking-widest">PHIẾU NHẬP KHO</h2>
                <p class="text-sm font-bold mt-2">Số: <span class="text-indigo-700"><?php echo e($pItem->code); ?></span></p>
                <p class="text-sm font-bold mt-1">Ngày nhập kho: <?php echo e($pItem->stock_in_date ? $pItem->stock_in_date->format('d/m/Y') : $pItem->created_at->format('d/m/Y')); ?></p>
            </div>

            <div class="mb-6 flex flex-col gap-2">
                <div class="flex items-baseline gap-2">
                    <span class="text-[12px] font-black text-slate-500 uppercase tracking-widest">Đơn vị giao hàng / Đối tác:</span>
                    <span class="font-black text-slate-800 text-lg uppercase"><?php echo e($pItem->supplier_name ?: ($pItem->manufacturer ?: 'N/A')); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pItem->note): ?>
                <div class="flex items-baseline gap-2">
                    <span class="text-[12px] font-black text-slate-500 uppercase tracking-widest">Ghi chú:</span>
                    <span class="font-bold text-slate-800 text-sm"><?php echo e($pItem->note); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <table class="w-full border-collapse border-2 border-slate-900 mb-8">
                <thead>
                    <tr class="bg-slate-100 uppercase text-[10px] font-black">
                        <th class="border border-slate-900 px-2 py-2 text-center w-10">STT</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-24">Mã vật tư</th>
                        <th class="border border-slate-900 px-2 py-2 text-left">Tên vật tư</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-16">ĐVT</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-20">Số lượng</th>
                        <th class="border border-slate-900 px-2 py-2 text-left w-32">Ghi chú</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pItem->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $ii): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="border border-slate-900 px-2 py-2 text-center"><?php echo e($idx + 1); ?></td>
                        <td class="border border-slate-900 px-2 py-2 text-center font-bold"><?php echo e($ii->product->code); ?></td>
                        <td class="border border-slate-900 px-2 py-2 font-bold"><?php echo e($ii->product->name); ?></td>
                        <td class="border border-slate-900 px-2 py-2 text-center"><?php echo e($ii->product->unit); ?></td>
                        <td class="border border-slate-900 px-2 py-2 text-right font-black"><?php echo e(is_numeric($ii->quantity) && floor($ii->quantity) == $ii->quantity ? number_format($ii->quantity, 0, ',', '.') : number_format($ii->quantity, 2, ',', '.')); ?></td>
                        <td class="border border-slate-900 px-2 py-2"></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; margin-top: 40px; text-align: center; font-size: 14px; font-weight: bold;">
                <div style="width: 25%;">
                    <p>THỦ KHO</p>
                    <p style="font-style: italic; font-weight: normal; font-size: 12px; margin-top: 4px;">(Ký, ghi rõ họ tên)</p>
                </div>
                <div style="width: 25%;">
                    <p>QUẢN LÝ KHO</p>
                    <p style="font-style: italic; font-weight: normal; font-size: 12px; margin-top: 4px;">(Ký, ghi rõ họ tên)</p>
                </div>
                <div style="width: 25%;">
                    <p>KTSC</p>
                    <p style="font-style: italic; font-weight: normal; font-size: 12px; margin-top: 4px;">(Ký, ghi rõ họ tên)</p>
                </div>
                <div style="width: 25%;">
                    <p>BP. AN NINH</p>
                    <p style="font-style: italic; font-weight: normal; font-size: 12px; margin-top: 4px;">(Ký, ghi rõ họ tên)</p>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- MODAL SỬA PHIẾU -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditModal): ?>
        <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" wire:click="$set('showEditModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10">
                                <span class="text-xl">✏️</span>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-black text-slate-900 uppercase tracking-widest" id="modal-title">
                                    Sửa Thông Tin Phiếu Nhập
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Ngày nhập <span class="text-rose-500">*</span></label>
                                        <input type="date" wire:model="editDate" class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-4 focus:ring-amber-100 py-2.5 bg-slate-50">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Loại nhập <span class="text-rose-500">*</span></label>
                                        <select wire:model="editType" class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-4 focus:ring-amber-100 py-2.5 bg-slate-50">
                                            <option value="purchase_produced">🛒 Mua Hàng</option>
                                            <option value="production">🏭 Sản Xuất</option>
                                            <option value="import_material">📦 Nguyên Liệu</option>
                                        </select>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['editType'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-xs font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Nhà cung cấp / Đối tác</label>
                                        <input type="text" wire:model="editSupplier" list="edit_suppliers_list" class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-4 focus:ring-amber-100 py-2.5 bg-slate-50" placeholder="Nhập tên nhà cung cấp...">
                                        <datalist id="edit_suppliers_list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($supplier->name); ?>"></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </datalist>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1">Ghi chú</label>
                                        <textarea wire:model="editNote" rows="2" class="w-full rounded-xl border-slate-200 text-sm font-bold focus:ring-4 focus:ring-amber-100 py-2.5 bg-slate-50" placeholder="Thông tin bổ sung..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-200">
                        <button type="button" wire:click="saveEdit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-amber-500 text-base font-black text-white hover:bg-amber-600 sm:ml-3 sm:w-auto sm:text-sm">
                            💾 Lưu thay đổi
                        </button>
                        <button type="button" wire:click="$set('showEditModal', false)" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                            Hủy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
        $__scriptKey = '598066137-0';
        ob_start();
    ?>
    <script>
        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 500);
        });

        $wire.on('show-success-effect', () => {
            const effect = document.createElement('div');
            effect.innerHTML = `
                <div class="fixed inset-0 flex items-center justify-center z-[9999] pointer-events-none transition-all duration-500 opacity-0" id="success-effect-container">
                    <div class="bg-white/90 backdrop-blur-md border-4 border-emerald-500 text-emerald-600 rounded-[3rem] p-12 flex flex-col items-center justify-center shadow-[0_0_100px_rgba(16,185,129,0.4)] transform scale-50 transition-transform duration-500" id="success-effect-box">
                        <div class="bg-emerald-500 text-white p-2 rounded-full mb-6 shadow-xl animate-[bounce_1s_ease-in-out]">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h2 class="text-4xl font-black uppercase tracking-widest text-slate-800">Đã Nhập Kho</h2>
                        <p class="text-slate-500 font-bold mt-2 text-lg">Hệ thống đã lưu phiếu thành công!</p>
                    </div>
                </div>
            `;
            document.body.appendChild(effect);
            
            setTimeout(() => {
                const container = document.getElementById('success-effect-container');
                const box = document.getElementById('success-effect-box');
                if (container && box) {
                    container.classList.remove('opacity-0');
                    container.classList.add('opacity-100');
                    box.classList.remove('scale-50');
                    box.classList.add('scale-100');
                }
            }, 50);

            setTimeout(() => {
                const container = document.getElementById('success-effect-container');
                const box = document.getElementById('success-effect-box');
                if (container && box) {
                    container.classList.remove('opacity-100');
                    container.classList.add('opacity-0');
                    box.classList.remove('scale-100');
                    box.classList.add('scale-50');
                    
                    setTimeout(() => {
                        if (effect.parentNode) effect.parentNode.removeChild(effect);
                    }, 500);
                }
            }, 1000);
        });

        $wire.on('show-error-effect', (data) => {
            let msg = Array.isArray(data) ? data[0].message : (data.message || data);
            alert('LỖI LƯU PHIẾU: ' + msg + '\n\nVui lòng kiểm tra lại dữ liệu (có thể có dòng bị trống số lượng). Trình duyệt sẽ tự động cuộn lên để bạn xem lỗi!');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        $wire.on('show-edit-success-effect', () => {
            const effect = document.createElement('div');
            effect.innerHTML = `
                <div class="fixed inset-0 flex items-center justify-center z-[9999] pointer-events-none transition-all duration-500 opacity-0" id="edit-success-effect-container">
                    <div class="bg-white/90 backdrop-blur-md border-4 border-amber-500 text-amber-600 rounded-[3rem] p-12 flex flex-col items-center justify-center shadow-[0_0_100px_rgba(245,158,11,0.4)] transform scale-50 transition-transform duration-500" id="edit-success-effect-box">
                        <div class="bg-amber-500 text-white p-2 rounded-full mb-6 shadow-xl animate-[bounce_1s_ease-in-out]">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h2 class="text-4xl font-black uppercase tracking-widest text-slate-800">Đã Lưu</h2>
                        <p class="text-slate-500 font-bold mt-2 text-lg">Thông tin phiếu đã được cập nhật!</p>
                    </div>
                </div>
            `;
            document.body.appendChild(effect);
            
            setTimeout(() => {
                const container = document.getElementById('edit-success-effect-container');
                const box = document.getElementById('edit-success-effect-box');
                if (container && box) {
                    container.classList.remove('opacity-0');
                    container.classList.add('opacity-100');
                    box.classList.remove('scale-50');
                    box.classList.add('scale-100');
                }
            }, 50);

            // Icon 3s đã lưu -> delay 3000ms
            setTimeout(() => {
                const container = document.getElementById('edit-success-effect-container');
                const box = document.getElementById('edit-success-effect-box');
                if (container && box) {
                    container.classList.remove('opacity-100');
                    container.classList.add('opacity-0');
                    box.classList.remove('scale-100');
                    box.classList.add('scale-50');
                    
                    setTimeout(() => {
                        if (effect.parentNode) effect.parentNode.removeChild(effect);
                    }, 500);
                }
            }, 3000);
        });
    </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/stock-in-form.blade.php ENDPATH**/ ?>