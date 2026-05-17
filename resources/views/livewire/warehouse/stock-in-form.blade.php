<div class="h-full flex flex-col space-y-4" style="font-family: 'Times New Roman', Times, serif;" x-data="{
    activeImportTab: 'excel',
    ocrProgress: 0,
    ocrStatus: '',
    ocrRunning: false,
    ocrImageSrc: '',
    ocrParsedRows: [],
    ocrMaximized: false,
    
    // Bản đồ sản phẩm phục vụ so khớp trực tiếp trên Trình duyệt
    productsMap: {
        @foreach($products as $p)
        '{{ strtolower($p->code) }}': { id: {{ $p->id }}, name: '{{ $p->name }}', unit: '{{ $p->unit ?: 'Cái' }}', price: {{ $p->price ?: 0 }}, location: '{{ $p->location ?: '' }}' },
        @endforeach
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
    async handlePdfUpload(event) {
        const file = event.target.files ? event.target.files[0] : (event.dataTransfer ? event.dataTransfer.files[0] : null);
        if (!file) return;

        this.ocrRunning = true;
        this.ocrProgress = 20;
        this.ocrStatus = 'Đang đọc cấu trúc tệp PDF...';

        try {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            
            let fullText = '';
            this.ocrStatus = `Đang phân tích dữ liệu văn bản từ ${pdf.numPages} trang PDF...`;
            
            for (let i = 1; i <= pdf.numPages; i++) {
                const page = await pdf.getPage(i);
                const textContent = await page.getTextContent();
                const pageText = textContent.items.map(item => item.str).join(' ');
                fullText += pageText + '\n';
                this.ocrProgress = 20 + Math.round((i / pdf.numPages) * 60);
            }

            this.ocrStatus = 'Đang bóc tách danh sách vật tư nhập kho...';
            this.ocrParsedRows = this.parseStockInText(fullText);
            
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

            // 5. Tìm số lượng và đơn giá từ các số trong dòng
            const numberMatches = rest.match(/(\b\d+([.,]\d+)?\b)/g) || [];
            let quantity = '';
            let unitPrice = '';

            const numericValues = numberMatches.map(n => {
                let cleanNum = n.replace(/[,.]/g, '');
                return parseFloat(cleanNum);
            }).filter(v => !isNaN(v) && v > 0);

            if (numericValues.length > 0) {
                const priceCandidate = numericValues.find(v => v >= 5000);
                if (priceCandidate) {
                    unitPrice = priceCandidate;
                    const idx = numericValues.indexOf(priceCandidate);
                    numericValues.splice(idx, 1);
                }
                
                if (numericValues.length > 0) {
                    quantity = numericValues[0];
                }
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
                unit_price: unitPrice || (matchedProduct ? matchedProduct.price : 0)
            });
        });

        return parsed;
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

    <!-- Tab Navigation -->
    <div class="bg-white p-2 rounded-2xl shadow-md border border-slate-200 flex items-center gap-3 w-fit no-print">
        <button wire:click="$set('activeTab', 'form')" class="px-8 py-3 rounded-xl text-[13px] font-black transition-all flex items-center gap-2 {{ $activeTab === 'form' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50' }}">
            <span>📥</span> LẬP PHIẾU NHẬP
        </button>
        <button wire:click="$set('activeTab', 'list')" class="px-8 py-3 rounded-xl text-[13px] font-black transition-all flex items-center gap-2 {{ $activeTab === 'list' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50' }}">
            <span>📋</span> DANH SÁCH PHIẾU
        </button>
    </div>

    <div class="flex-1 main-content">
        @if($activeTab === 'form')
            @if(session('success'))
                <div class="mb-4 p-4 bg-emerald-100 text-emerald-800 rounded-2xl font-bold flex items-center gap-2 border border-emerald-200 animate-in fade-in slide-in-from-top-2">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-rose-50 text-rose-700 rounded-2xl font-bold border border-rose-200 animate-in fade-in slide-in-from-top-2">
                    <div class="flex items-center gap-2 mb-2 text-rose-800">
                        <span>❌</span> <span>Có lỗi xảy ra:</span>
                    </div>
                    <ul class="list-disc list-inside text-[13px] ml-6">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-[15px] font-black text-slate-900 flex items-center gap-2 uppercase tracking-tight">
                        <span class="p-2 bg-indigo-600 text-white rounded-xl shadow-lg">📥</span>
                        PHIẾU NHẬP KHO MỚI
                    </h2>

                    <!-- Nút Nhập Tự Động cực kỳ sang trọng -->
                    <button type="button" wire:click="$set('showImportModal', true)" 
                            class="px-4 py-2.5 text-xs font-black text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl shadow-sm transition-all duration-150 flex items-center gap-1.5 active:scale-95 no-print">
                        ⚡ Nhập từ Excel / PDF / Ảnh AI
                    </button>
                </div>
                
                <div class="p-6">

        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="space-y-1.5">
                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest px-1">Nhà cung cấp</label>
                <input type="text" wire:model="supplier_name" list="suppliers_list" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2.5 px-4 text-[13px] font-bold text-slate-800" placeholder="Chọn hoặc nhập tên...">
                <datalist id="suppliers_list">
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->name }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="space-y-1.5">
                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest px-1">Hãng sản xuất</label>
                <input type="text" wire:model="manufacturer" list="brands_list" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2.5 px-4 text-[13px] font-bold text-slate-800" placeholder="Nhập hãng SX...">
                <datalist id="brands_list">
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="space-y-1.5">
                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest px-1">Loại nhập</label>
                <select wire:model.live="type" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2.5 px-4 text-[13px] font-black text-slate-800 appearance-none">
                    <option value="purchase_produced">🛒 NHẬP MUA HÀNG TP</option>
                    <option value="return_produced">↩️ NHẬP TRẢ HÀNG TP</option>
                    <option value="production">🏭 NHẬP TỪ SẢN XUẤT</option>
                    <option value="import_material">📦 NHẬP NGUYÊN VẬT LIỆU</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden border border-slate-200 rounded-2xl shadow-sm mb-6 bg-slate-50/30">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-slate-800">
                                    <th class="px-4 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 min-w-[200px]">Vật tư</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Mã Code NCC</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-32">Hạn dùng</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Vị trí</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-20">SL</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">ĐVT</th>
                                    <th class="px-2 py-3 border-b border-slate-700 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                    <!-- Cột Vật tư -->
                                    <td class="px-4 py-3">
                                        <input type="text" wire:model.live.debounce.250ms="items.{{ $index }}.product_search" list="product_list_{{ $index }}"
                                               class="w-full rounded-lg text-[13px] font-bold focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-3 {{ empty($item['product_id']) ? 'border-orange-400 bg-orange-50/40 focus:ring-orange-100 text-orange-900 placeholder:text-orange-300' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-800' }}"
                                               placeholder="Mã hoặc tên vật tư...">
                                        <datalist id="product_list_{{ $index }}">
                                            @foreach($products as $product)
                                                <option value="{{ $product->code }} - {{ $product->name }}"></option>
                                            @endforeach
                                        </datalist>
                                        @error("items.{$index}.product_id") <p class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                                    </td>
                                    
                                    <!-- Cột Mã Code NCC -->
                                    <td class="px-2 py-3">
                                        <input type="text" wire:model.live="items.{{ $index }}.batch_number"
                                               class="w-full rounded-lg text-[12px] font-black focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-2 {{ empty($item['batch_number']) ? 'border-orange-400 bg-orange-50/40 focus:ring-orange-100 text-orange-900 placeholder:text-orange-300' : 'border-slate-200 bg-slate-50 focus:bg-white text-indigo-700' }}" 
                                               placeholder="Mã Code NCC...">
                                    </td>
                                    
                                    <!-- Cột Hạn dùng -->
                                    <td class="px-2 py-3">
                                        <input type="date" wire:model="items.{{ $index }}.expiry_date"
                                               class="w-full rounded-lg text-[12px] focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-2 {{ empty($item['expiry_date']) ? 'border-orange-400 bg-orange-50/40 focus:ring-orange-100 text-orange-900' : 'border-slate-200 bg-slate-50 focus:bg-white font-bold text-slate-700' }}">
                                    </td>
                                    
                                    <!-- Cột Vị trí -->
                                    <td class="px-2 py-3">
                                        <input type="text" wire:model="items.{{ $index }}.warehouse_location"
                                               class="w-full text-[12px] font-bold rounded-lg focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-2 {{ empty($item['warehouse_location']) ? 'border-orange-400 bg-orange-50/40 focus:ring-orange-100 text-orange-900 placeholder:text-orange-300' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-700' }}" 
                                               placeholder="Vị trí...">
                                    </td>
                                    
                                    <!-- Cột Số lượng -->
                                    <td class="px-2 py-3">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.{{ $index }}.quantity"
                                               class="w-full text-center text-[13px] font-black rounded-lg focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-1 {{ (empty($item['quantity']) || $item['quantity'] <= 0) ? 'border-orange-400 bg-orange-50/40 focus:ring-orange-100 text-orange-900' : 'border-slate-200 bg-slate-50 focus:bg-white text-slate-900' }}"
                                               placeholder="0">
                                    </td>
                                    
                                    <td class="px-2 py-3 text-center">
                                        <span class="text-[11px] font-black text-slate-500 bg-slate-100 px-2 py-1 rounded-md border border-slate-200 uppercase">{{ $items[$index]['unit'] ?? '-' }}</span>
                                    </td>
                                    <td class="px-2 py-3 text-center">
                                        @if(count($items) > 1)
                                            <button wire:click="removeItem({{ $index }})" class="text-slate-300 hover:text-rose-600 transition-all p-1.5 rounded-full hover:bg-rose-50">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if(count($items) > 0)
                            <tfoot class="border-t-2 border-slate-100">
                                <tr class="bg-indigo-50/50">
                                    <td colspan="6" class="px-6 py-4 text-right font-black text-slate-500 uppercase tracking-widest text-[11px]">Tổng số lượng:</td>
                                    <td class="px-4 py-4 text-right font-black text-indigo-900 text-[16px] underline decoration-double">
                                        {{ number_format(collect($items)->sum('quantity')) }}
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

        <div class="flex items-center gap-4 mb-8">
            @if($this->canAddItem())
                <button wire:click="addItem" class="bg-slate-800 text-white px-6 py-2.5 rounded-xl text-[12px] font-black flex items-center gap-2 hover:bg-indigo-600 transition-all shadow-md active:scale-95">
                    <span>➕</span> THÊM DÒNG MỚI
                </button>
            @endif

            <button wire:click="openProductModal" class="bg-white border-2 border-emerald-600 text-emerald-700 px-6 py-2.5 rounded-xl text-[12px] font-black flex items-center gap-2 hover:bg-emerald-50 transition-all shadow-sm active:scale-95">
                <span>📦</span> TẠO NHANH VẬT TƯ
            </button>
        </div>

        <div class="border-t border-slate-150 pt-6 flex items-center justify-between">
            <div class="w-2/3">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest px-1 mb-1">Ghi chú phiếu nhập</label>
                <textarea wire:model="note" rows="2" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[13px] font-bold text-slate-800 placeholder:font-normal" placeholder="Lý do nhập kho, số chứng từ kèm theo..."></textarea>
            </div>
            <button wire:click="save" class="px-12 py-4 rounded-xl text-[14px] font-black text-white bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 shadow-xl shadow-indigo-100 hover:shadow-indigo-200 transition-all flex items-center gap-2 transform hover:-translate-y-0.5 active:translate-y-0">
                <span>💾</span> LƯU PHIẾU NHẬP
            </button>
        </div>

                </div>
            </div>
        @endif

        <!-- TAB DANH SÁCH PHIẾU -->
        @if($activeTab === 'list')
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 no-print">
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
                                @if(empty($selectedIds)) disabled class="px-4 py-2 text-xs font-black text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed"
                                @else class="px-4 py-2 text-xs font-black text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl flex items-center gap-1.5"
                                @endif>
                            🖨️ In tích chọn ({{ count($selectedIds) }})
                        </button>

                        <button wire:click="deleteSelected" 
                                onclick="confirm('Lưu ý: Số lượng tồn kho tương ứng sẽ bị giảm trừ khi xóa phiếu nhập. Tiếp tục?') || event.stopImmediatePropagation()"
                                @if(empty($selectedIds)) disabled class="px-4 py-2 text-xs font-black text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed"
                                @else class="px-4 py-2 text-xs font-black text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl flex items-center gap-1.5"
                                @endif>
                            🗑️ Xóa đã chọn ({{ count($selectedIds) }})
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[11px] font-black text-white uppercase tracking-widest bg-slate-800 border-b border-slate-700">
                            @php
                                $allOnPage = \App\Models\StockIn::whereBetween('created_at', [$listDateFrom . ' 00:00:00', $listDateTo . ' 23:59:59'])
                                    ->where(function($q) {
                                        $q->where('code', 'like', '%' . $this->listSearch . '%')
                                          ->orWhere('supplier_name', 'like', '%' . $this->listSearch . '%');
                                    })
                                    ->latest()
                                    ->paginate(15);
                                $idsOnPage = $allOnPage->pluck('id')->toArray();
                            @endphp
                            <tr>
                                <th class="px-6 py-4 w-10 no-print text-center">
                                    <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $idsOnPage) }}])" {{ count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500">
                                </th>
                                <th class="px-2 py-4">MÃ PHIẾU</th>
                                <th class="px-6 py-4">NGÀY TẠO</th>
                                <th class="px-6 py-4">NHÀ CUNG CẤP / ĐỐI TÁC</th>
                                <th class="px-6 py-4">LOẠI NHẬP</th>
                                <th class="px-6 py-4 text-right">TỔNG TIỀN</th>
                                <th class="px-6 py-4">GHI CHÚ</th>
                                <th class="px-6 py-4 text-center no-print">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($allOnPage as $si)
                                <tr class="hover:bg-indigo-50/30 transition-all group {{ in_array($si->id, $selectedIds) ? 'bg-indigo-50' : '' }}">
                                    <td class="px-6 py-4 no-print text-center">
                                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $si->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-2 py-4 font-black text-indigo-700 tracking-tight">{{ $si->code }}</td>
                                    <td class="px-6 py-4 text-slate-500 text-[12px] font-bold">{{ $si->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 font-black text-slate-800 text-[13px] uppercase tracking-tighter">{{ $si->supplier_name ?: ($si->manufacturer ?: '-') }}</td>
                                    <td class="px-6 py-4">
                                        @switch($si->type)
                                            @case('purchase_produced') <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-black uppercase border border-emerald-100">🛒 MUA HÀNG</span> @break
                                            @case('production') <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-black uppercase border border-indigo-100">🏭 SẢN XUẤT</span> @break
                                            @case('import_material') <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-[10px] font-black uppercase border border-amber-100">📦 NGUYÊN LIỆU</span> @break
                                            @default <span class="px-2.5 py-1 bg-slate-50 text-slate-600 rounded-lg text-[10px] font-black uppercase border border-slate-100">{{ $si->type }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-slate-900 text-[14px]">
                                        {{ number_format($si->items->sum('total_amount')) }} đ
                                    </td>
                                    <td class="px-6 py-4 text-slate-400 text-[11px] font-bold italic truncate max-w-[150px]">{{ $si->note ?: '-' }}</td>
                                    <td class="px-6 py-4 text-center no-print">
                                        <div class="flex items-center justify-center gap-1">
                                            <button wire:click="printSingle({{ $si->id }})" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="In phiếu này">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            </button>
                                            <button wire:click="delete({{ $si->id }})" class="p-2 text-rose-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Xóa phiếu">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">Không tìm thấy phiếu nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-50 no-print">
                    {{ $allOnPage->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- MODAL NHẬP ĐA PHƯƠNG THỨC TỰ ĐỘNG (EXCEL / PDF / ẢNH AI OCR) -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 no-print" :class="ocrMaximized ? 'overflow-hidden h-screen w-screen' : 'overflow-y-auto'">
            <div class="flex items-center justify-center min-h-screen text-center" :class="ocrMaximized ? 'h-screen w-screen p-0 items-stretch' : 'pt-4 px-4 pb-20 align-middle'">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" wire:click="$set('showImportModal', false); ocrMaximized = false"></div>
                
                <div :class="ocrMaximized ? 'w-screen h-screen max-w-full my-0 rounded-none flex flex-col' : 'inline-block align-middle sm:max-w-4xl sm:w-full rounded-2xl sm:my-8 border border-slate-150'"
                     class="bg-white text-left overflow-hidden shadow-2xl transform transition-all">
                    
                    <!-- Tab Header -->
                    <div class="bg-slate-55 border-b border-slate-150 px-6 py-4 flex items-center justify-between shrink-0 bg-slate-50">
                        <div class="flex gap-6">
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
                        <div x-show="activeImportTab === 'excel'" class="p-6 space-y-4">
                        <div class="p-3.5 bg-emerald-50 text-emerald-850 rounded-lg text-xs font-semibold leading-relaxed border border-emerald-100">
                            ✨ <span class="font-extrabold text-emerald-950">Giải pháp đồng bộ cột linh hoạt:</span> Anh/chị có thể sắp xếp thứ tự các cột Excel tùy ý! Hệ thống sẽ quét dòng tiêu đề để bóc tách thông tin tự động. 
                            Những cột nào không tìm thấy hoặc trống thông tin sẽ được <span class="font-black text-orange-700 underline">báo màu cam</span> trên bảng nhập để anh/chị bổ sung nhanh chóng.
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Chọn tệp tin Excel/CSV từ máy tính</label>
                            <input type="file" wire:model="excelFile" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border file:border-slate-200 file:text-xs file:font-bold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" />
                            @error('excelFile') <span class="text-red-500 text-xs font-bold block mt-1">{{ $message }}</span> @enderror
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
                    <div x-show="activeImportTab === 'pdf'" class="p-6 space-y-4">
                        <div class="p-3.5 bg-red-50 text-red-850 rounded-lg text-xs font-semibold leading-relaxed border border-red-100">
                            📋 <span class="font-extrabold text-red-950">Giải pháp xử lý PDF thông minh:</span> Hệ thống sẽ đọc dữ liệu text trực tiếp từ tệp tin PDF hóa đơn/phiếu giao hàng của nhà cung cấp và tự động bóc tách các trường: <i>Mã vật tư, Số lượng, Hạn dùng, Số lô, Vị trí, Đơn giá</i> để điền nhanh vào phiếu nhập!
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- PDF Drag and Drop Zone -->
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 flex flex-col items-center justify-center min-h-[200px] bg-slate-50 relative hover:border-red-400 hover:bg-slate-100/50 transition-all cursor-pointer"
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

                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-150 flex flex-col justify-between">
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
                                                <th class="p-3 min-w-[280px]">Vật tư quét được / Khớp danh mục</th>
                                                <th class="p-3 text-center w-20">Số lượng</th>
                                                <th class="p-3 text-center w-16">ĐVT</th>
                                                <th class="p-3 w-24">Số lô</th>
                                                <th class="p-3 w-28">Hạn dùng</th>
                                                <th class="p-3 w-20">Vị trí</th>
                                                <th class="p-3 text-right w-28">Đơn giá (đ)</th>
                                                <th class="p-3 text-center w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, idx) in ocrParsedRows" :key="idx">
                                                <tr class="border-b border-slate-150 hover:bg-indigo-50/20 transition-colors">
                                                    <!-- Cột Vật tư -->
                                                    <td class="p-2 space-y-1">
                                                        <template x-if="row.code && row.name">
                                                            <div class="flex items-center gap-1.5 mb-1">
                                                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-1.5 py-0.5 rounded border border-emerald-200 shrink-0">
                                                                    ✅ Khớp: <span x-text="row.code"></span>
                                                                </span>
                                                                <span class="text-[11px] font-bold text-slate-850 truncate max-w-[200px]" x-text="row.name"></span>
                                                            </div>
                                                        </template>

                                                        <div class="space-y-1">
                                                            <template x-if="!row.code || !row.name">
                                                                <div class="text-[10px] font-semibold text-orange-700 bg-orange-50 px-2 py-1 rounded border border-orange-200 leading-tight mb-1 flex items-center gap-1 w-fit">
                                                                    <span>⚠️</span> Quét được: <span class="font-bold italic" x-text="row.scanned_name || 'Không rõ tên'"></span>
                                                                </div>
                                                            </template>
                                                            
                                                            <select x-model="row.code" @change="let p = productsMap[row.code.toLowerCase()]; if(p) { row.name = p.name; row.unit = p.unit; row.unit_price = p.price; row.warehouse_location = p.location; } else { row.name = ''; }" 
                                                                    :class="(!row.code || !row.name) ? 'border-orange-400 bg-orange-50/40 text-orange-950 focus:ring-orange-100' : 'border-slate-200 bg-slate-50 text-slate-800'"
                                                                    class="w-full text-[11px] p-1.5 rounded-lg font-bold focus:ring-4 focus:ring-indigo-100 transition-all">
                                                                <option value="">-- Chọn vật tư khớp danh mục... --</option>
                                                                @foreach($products as $p)
                                                                    <option value="{{ $p->code }}">{{ $p->code }} - {{ $p->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>

                                                    <!-- Cột Số lượng -->
                                                    <td class="p-2">
                                                        <input type="text" x-model="row.quantity" 
                                                               :class="!row.quantity ? 'border-orange-400 bg-orange-50/40 focus:ring-orange-100' : 'border-slate-250 bg-slate-50 focus:bg-white'"
                                                               class="w-full p-1.5 text-[11px] text-center font-black rounded-lg focus:ring-4 focus:ring-indigo-100 transition-all text-slate-850" placeholder="0" />
                                                    </td>

                                                    <!-- Cột ĐVT -->
                                                    <td class="p-2 text-center">
                                                        <span class="text-[10px] font-black text-slate-500 bg-slate-100 px-1.5 py-1 rounded border border-slate-250 uppercase" x-text="row.unit || 'Cái'"></span>
                                                    </td>

                                                    <!-- Cột Số lô -->
                                                    <td class="p-2">
                                                        <input type="text" x-model="row.batch_number" 
                                                               :class="!row.batch_number ? 'border-orange-300 bg-orange-50/20' : 'border-slate-250 bg-slate-50 focus:bg-white'"
                                                               class="w-full p-1.5 text-[11px] font-bold rounded-lg focus:ring-4 focus:ring-indigo-100 transition-all text-indigo-755" placeholder="Lô..." />
                                                    </td>

                                                    <!-- Cột Hạn dùng -->
                                                    <td class="p-2">
                                                        <input type="date" x-model="row.expiry_date" 
                                                               :class="!row.expiry_date ? 'border-orange-300 bg-orange-50/20' : 'border-slate-250 bg-slate-50 focus:bg-white'"
                                                               class="w-full p-1.5 text-[11px] font-semibold rounded-lg focus:ring-4 focus:ring-indigo-100 transition-all text-slate-700" />
                                                    </td>

                                                    <!-- Cột Vị trí -->
                                                    <td class="p-2">
                                                        <input type="text" x-model="row.warehouse_location" 
                                                               :class="!row.warehouse_location ? 'border-orange-300 bg-orange-50/20' : 'border-slate-250 bg-slate-50 focus:bg-white'"
                                                               class="w-full p-1.5 text-[11px] font-bold rounded-lg focus:ring-4 focus:ring-indigo-100 transition-all text-slate-700" placeholder="Vị trí..." />
                                                    </td>

                                                    <!-- Cột Đơn giá -->
                                                    <td class="p-2">
                                                        <input type="text" x-model="row.unit_price" class="w-full p-1.5 text-[11px] text-right font-black rounded-lg border-slate-250 bg-slate-50 focus:bg-white text-emerald-700" placeholder="0" />
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
                    <div x-show="activeImportTab === 'ocr'" class="p-6 space-y-4" @paste="handleImagePaste($event)">
                        <div class="p-3 bg-indigo-50 text-indigo-850 rounded-lg text-xs font-semibold leading-relaxed border border-indigo-100">
                            📷 <span class="font-extrabold text-indigo-950">Quét ảnh chụp thông minh:</span> Anh/chị chỉ cần chụp ảnh màn hình bảng Excel hoặc chụp phiếu xuất kho của nhà cung cấp, nhấn **Ctrl + V** để dán trực tiếp ảnh vào đây hoặc chọn ảnh chụp để AI bóc tách nhanh chóng!
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Image Drag and Drop Zone -->
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 flex flex-col items-center justify-center min-h-[200px] bg-slate-50 relative hover:border-indigo-400 hover:bg-slate-100/50 transition-all cursor-pointer"
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
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-150 flex flex-col justify-between">
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
                                                            @foreach($products as $p)
                                                                <option value="{{ $p->code }}">{{ $p->code }} - {{ $p->name }}</option>
                                                            @endforeach
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
    @endif

    <!-- Quick Product Modal -->
    @if($showProductModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-bold mb-4">Tạo nhanh vật tư mới</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mã vật tư</label>
                        <input type="text" wire:model="newPCode" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('newPCode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tên vật tư</label>
                        <input type="text" wire:model="newPName" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('newPName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Đơn vị tính</label>
                        <input type="text" wire:model="newPUnit" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('newPUnit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hãng sản xuất (Kế thừa từ header)</label>
                        <input type="text" disabled value="{{ $manufacturer }}" class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showProductModal', false)" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button wire:click="createProduct" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Lưu & Thêm dòng</button>
                </div>
            </div>
        </div>
    @endif

    <!-- PHẦN IN CHI TIẾT HÀNG LOẠT (Nhập kho) -->
    @if(count($printItems) > 0)
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        @foreach($printItems as $pItem)
        <div class="print-page p-8 bg-white" style="font-family: 'Times New Roman', serif; min-height: 297mm; page-break-after: always;">
            {{-- Header Công ty --}}
            <div style="text-align: center; margin-bottom: 16px;">
                <h1 style="font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 0;">CÔNG TY CPĐT VÀ THI CÔNG HẠ TẦNG VINALPHA</h1>
            </div>
            <div class="flex justify-between items-start mb-6 border-b-2 border-slate-900 pb-4">
                <div>
                    <h1 class="text-xl font-black uppercase">CÔNG TY TNHH SANE</h1>
                    <p class="text-[11px] font-bold">Khu công nghiệp Đức Hòa 1, Long An</p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-black text-slate-900 uppercase">PHIẾU NHẬP KHO</h2>
                    <p class="text-sm font-bold mt-1">Số: <span class="text-indigo-700">{{ $pItem->code }}</span></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 mb-8">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Đơn vị giao hàng</p>
                    <p class="font-black text-slate-800 text-lg uppercase">{{ $pItem->supplier_name ?: ($pItem->manufacturer ?: 'N/A') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ngày nhập kho</p>
                    <p class="font-black text-slate-800">{{ $pItem->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <table class="w-full border-collapse border-2 border-slate-900 mb-6">
                <thead>
                    <tr class="bg-slate-100 uppercase text-[10px] font-black">
                        <th class="border border-slate-900 px-2 py-2 text-center w-10">STT</th>
                        <th class="border border-slate-900 px-2 py-2 text-left">Tên vật tư / Quy cách</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-16">Mã Code NCC</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-16">ĐVT</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-20">Số lượng</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @foreach($pItem->items as $idx => $ii)
                    <tr>
                        <td class="border border-slate-900 px-2 py-2 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-slate-900 px-2 py-2 font-bold">{{ $ii->product->name }} ({{ $ii->product->code }})</td>
                        <td class="border border-slate-900 px-2 py-2 text-center">{{ $ii->batch_number }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-center">{{ $ii->product->unit }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-right font-bold">{{ number_format($ii->quantity) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="grid grid-cols-3 gap-4 text-center mt-12">
                <div>
                    <p class="font-bold text-xs uppercase">Người giao hàng</p>
                    <p class="text-[9px] italic">(Ký, ghi rõ họ tên)</p>
                </div>
                <div>
                    <p class="font-bold text-xs uppercase">Người nhận</p>
                    <p class="text-[9px] italic">(Ký, ghi rõ họ tên)</p>
                </div>
                <div>
                    <p class="font-bold text-xs uppercase">Thủ kho</p>
                    <p class="text-[9px] italic">(Ký, ghi rõ họ tên)</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @script
    <script>
        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 500);
        });

        $wire.on('show-success-effect', () => {
            const effect = document.createElement('div');
            effect.innerHTML = `
                <div class="fixed inset-0 flex items-center justify-center z-[9999] pointer-events-none transition-all duration-500 opacity-0" id="success-effect-container">
                    <div class="bg-white/90 backdrop-blur-md border-4 border-emerald-500 text-emerald-600 rounded-[3rem] p-12 flex flex-col items-center justify-center shadow-[0_0_100px_rgba(16,185,129,0.4)] transform scale-50 transition-transform duration-500" id="success-effect-box">
                        <div class="bg-emerald-500 text-white p-4 rounded-full mb-6 shadow-xl animate-[bounce_1s_ease-in-out]">
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
            }, 2500);
        });
    </script>
    @endscript
</div>
