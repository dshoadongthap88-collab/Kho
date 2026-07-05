<div class="space-y-4" x-data="{ 
    activeTab: 'excel',
    ocrProgress: 0,
    ocrStatus: '',
    ocrRunning: false,
    ocrImageSrc: '',
    ocrParsedRows: [],
    
    // Xử lý kéo thả và dán ảnh chụp
    handleImageUpload(event) {
        const file = event.target.files[0];
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
            this.ocrStatus = 'Hình ảnh đã sẵn sàng để nhận diện!';
            this.ocrParsedRows = [];
        };
        reader.readAsDataURL(file);
    },
    
    // Chạy OCR sử dụng Tesseract.js
    async runOCR() {
        if (!this.ocrImageSrc) {
            alert('Vui lòng chọn hoặc dán ảnh chụp thiết bị/bảng định mức trước!');
            return;
        }
        
        this.ocrRunning = true;
        this.ocrProgress = 10;
        this.ocrStatus = 'Đang khởi tạo công cụ nhận diện AI...';
        
        try {
            const worker = await Tesseract.createWorker('vie+eng', 1, {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        this.ocrProgress = Math.round(15 + m.progress * 80);
                        this.ocrStatus = `Đang phân tích cấu trúc bảng định mức: ${Math.round(m.progress * 100)}%`;
                    }
                }
            });
            
            this.ocrStatus = 'Đang quét và nhận diện chữ tiếng Việt...';
            const { data: { text } } = await worker.recognize(this.ocrImageSrc);
            await worker.terminate();
            
            this.ocrProgress = 95;
            this.ocrStatus = 'Đang bóc tách cột và chuẩn hóa định mức bảo dưỡng...';
            
            // Phân tích văn bản nhận diện được
            this.ocrParsedRows = this.parseTextToBoms(text);
            
            this.ocrProgress = 100;
            this.ocrRunning = false;
            this.ocrStatus = `Nhận diện xong! Tìm thấy ${this.ocrParsedRows.length} dòng thiết bị. Vui lòng xem bảng xem trước phía dưới.`;
        } catch (error) {
            console.error(error);
            this.ocrRunning = false;
            this.ocrStatus = 'Lỗi nhận diện: ' + error.message;
            alert('Nhận diện thất bại! Hãy thử ảnh có độ sắc nét tốt hơn.');
        }
    },
    
    // Giải thuật bóc tách và phân tích văn bản OCR thông minh
    parseTextToBoms(text) {
        const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 5);
        const parsed = [];
        
        lines.forEach(line => {
            // Định dạng Mã tài sản: TS-XXX hoặc tương tự
            const assetCodeMatch = line.match(/(TS-\d+|TS\d+|[A-Z0-9]{3,8}-\d+)/i);
            if (!assetCodeMatch) return;
            const assetCode = assetCodeMatch[0].toUpperCase();
            
            let rest = line.replace(assetCodeMatch[0], '').trim();
            
            // Định dạng mã các lọc nhớt, thủy lực, gió (dạng LF-XXX, HF-XXX, AF-XXX)
            const filterMatches = rest.match(/([A-Z]{2,3}-\d+|[A-Z]{2}\d{3,5})/g) || [];
            let engineFilter = '';
            let hydraulicFilter = '';
            let airFilter = '';
            
            filterMatches.forEach(filter => {
                rest = rest.replace(filter, '').trim();
                const fUpper = filter.toUpperCase();
                if (fUpper.startsWith('LF') || fUpper.includes('LF') || fUpper.includes('ENG')) {
                    engineFilter = fUpper;
                } else if (fUpper.startsWith('HF') || fUpper.includes('HF') || fUpper.includes('HYD')) {
                    hydraulicFilter = fUpper;
                } else if (fUpper.startsWith('AF') || fUpper.includes('AF') || fUpper.includes('AIR')) {
                    airFilter = fUpper;
                } else {
                    if (!engineFilter) engineFilter = fUpper;
                    else if (!hydraulicFilter) hydraulicFilter = fUpper;
                    else if (!airFilter) airFilter = fUpper;
                }
            });
            
            // Định dạng Chu kỳ (ví dụ: 250 giờ, 500 gio, 250h, 5000km)
            const cycleMatch = rest.match(/(\d+\s*(giờ|gio|h|km|hours))/i);
            let cycle = '';
            if (cycleMatch) {
                cycle = cycleMatch[0];
                rest = rest.replace(cycleMatch[0], '').trim();
            }
            
            // Bóc tách dung tích dầu động cơ và dầu thủy lực (các số lẻ còn lại)
            const numberMatches = rest.match(/(\b\d+\b)/g) || [];
            let engineOil = '';
            let hydraulicOil = '';
            
            numberMatches.forEach(numStr => {
                rest = rest.replace(numStr, '').trim();
                const val = parseInt(numStr);
                if (val > 0) {
                    if (val >= 30 && !hydraulicOil) {
                        hydraulicOil = val;
                    } else if (!engineOil) {
                        engineOil = val;
                    } else if (!hydraulicOil) {
                        hydraulicOil = val;
                    }
                }
            });
            
            // Bóc tách Tên thiết bị và Bộ phận từ phần chữ còn lại
            let name = '';
            let department = '';
            const words = rest.split(/\s+/).filter(w => w.length > 0);
            
            if (words.length > 0) {
                if (words.length <= 3) {
                    name = words.join(' ');
                } else {
                    const lastTwo = words.slice(-2).join(' ');
                    if (/kho|van|co|gioi|bao|tri|truong|xi|nghiep/i.test(lastTwo)) {
                        department = lastTwo;
                        name = words.slice(0, -2).join(' ');
                    } else {
                        name = words.slice(0, -1).join(' ');
                        department = words.slice(-1).join(' ');
                    }
                }
            }
            
            parsed.push({
                asset_code: assetCode,
                name: name || 'Thiết bị mới',
                department: department || 'Cơ giới',
                engine_oil_cap: engineOil || '',
                hydraulic_oil_cap: hydraulicOil || '',
                engine_oil_filter: engineFilter || '',
                hydraulic_filter: hydraulicFilter || '',
                air_filter: airFilter || '',
                maintenance_cycle: cycle || '250 giờ'
            });
        });
        
        return parsed;
    },
    
    // Lưu kết quả nhận diện xuống DB qua Livewire
    submitOcrData() {
        if (this.ocrParsedRows.length === 0) {
            alert('Không có dữ liệu thiết bị nào để đồng bộ!');
            return;
        }
        
        // Gọi Livewire action
        $wire.saveOcrData(this.ocrParsedRows);
        
        // Reset state
        this.ocrParsedRows = [];
        this.ocrImageSrc = '';
        this.ocrStatus = '';
        this.ocrProgress = 0;
    }
}">
    <!-- Thư viện Tesseract.js phục vụ AI OCR trực tiếp ở trình duyệt -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

    <!-- CSS để in ấn -->
    <style>
        @media print {
            .no-print { display: none !important; }
            body {
                background: white !important;
                font-family: 'Times New Roman', Times, serif !important;
                padding: 0 !important;
                margin: 0 !important;
                color: black !important;
            }
            main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .print-only { display: block !important; }
            .print-container { width: 100%; padding: 10mm; }
            table.print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px;
            }
            table.print-table th, table.print-table td {
                border: 1.5px solid black !important;
                padding: 6px 8px !important;
                font-size: 13px !important;
                color: black !important;
            }
            table.print-table th {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 12px !important;
            }
            @page { size: A4 landscape; margin: 10mm; }
        }
        .print-only { display: none; }
    </style>

    <!-- Thống báo trạng thái -->
    @if (session()->has('message'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center gap-2 shadow-sm transition-all duration-300 no-print">
            <span class="text-base">✨</span>
            <div class="font-semibold">{{ session('message') }}</div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 mb-4 text-sm text-rose-800 rounded-lg bg-rose-50 border border-rose-100 flex items-center gap-2 shadow-sm transition-all duration-300 no-print">
            <span class="text-base">⚠️</span>
            <div class="font-semibold">{{ session('error') }}</div>
        </div>
    @endif

    <!-- Thanh công cụ tìm kiếm & nút thao tác -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4 no-print">
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <div class="relative w-full lg:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    🔍
                </span>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Tìm kiếm mã tài sản, tên, bộ phận..." 
                       class="pl-9 pr-4 py-2 w-full text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-slate-50 hover:bg-slate-100/50 transition-colors"
                />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-end">
            <!-- Nút Thêm thiết bị nhanh -->
            <button wire:click="toggleAddAsset" 
                    class="px-3 py-2 text-sm font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg border border-sky-200 transition duration-150 flex items-center gap-1.5 shadow-sm">
                ➕ Thêm thiết bị
            </button>

            <!-- Nút NHẬP EXCEL / OCR -->
            <button wire:click="$set('showImportModal', true)" 
                    class="px-3 py-2 text-sm font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 shadow-sm transition duration-150 flex items-center gap-1">
                📥 Nhập Excel & Ảnh
            </button>

            <!-- Nút XUẤT EXCEL -->
            <button wire:click="exportExcel" 
                    class="px-3 py-2 text-sm font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg border border-teal-200 shadow-sm transition duration-150 flex items-center gap-1">
                📤 Xuất Excel
            </button>

            <!-- Nút SỬA -->
            <button wire:click="openEditModal" 
                    @if(count($selectedIds) !== 1) disabled class="px-3 py-2 text-sm font-bold text-slate-400 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200"
                    @else class="px-3 py-2 text-sm font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 shadow-sm transition duration-150 flex items-center gap-1"
                    @endif>
                ✏️ Sửa
            </button>

            <!-- Nút XÓA -->
            <button wire:click="deleteSelected" 
                    onclick="confirm('Anh/chị có chắc chắn muốn xóa các thiết bị đang chọn?') || event.stopImmediatePropagation()"
                    @if(empty($selectedIds)) disabled class="px-3 py-2 text-sm font-bold text-slate-400 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200"
                    @else class="px-3 py-2 text-sm font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 shadow-sm transition duration-150 flex items-center gap-1"
                    @endif>
                🗑️ Xóa
            </button>

            <!-- Nút IN TICK CHỌN -->
            <button wire:click="printSelected" 
                    @if(empty($selectedIds)) disabled class="px-3 py-2 text-sm font-bold text-slate-400 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200"
                    @else class="px-3 py-2 text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 shadow-sm transition duration-150 flex items-center gap-1"
                    @endif>
                🖨️ In tích chọn
            </button>

            <div class="h-6 w-[1px] bg-slate-200 mx-1"></div>

            <!-- Nút Lưu định mức hàng loạt -->
            <button wire:click="saveBoms" 
                    class="px-4 py-2 text-sm font-extrabold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-lg shadow-sm hover:shadow transition duration-150 flex items-center gap-1">
                💾 LƯU ĐỊNH MỨC
            </button>
        </div>
    </div>

    <!-- Form thêm thiết bị nhanh -->
    @if($isAddingAsset)
        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 shadow-inner max-w-2xl transition-all duration-300 no-print">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight mb-4 flex items-center gap-1.5">
                <span class="bg-sky-600 text-white p-1 rounded-md text-xs">📝</span>
                Thêm thiết bị mới vào hệ thống
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Mã tài sản <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="new_asset_code" placeholder="Ví dụ: TS-003" 
                           class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                    @error('new_asset_code') <span class="text-xs text-red-600 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tên thiết bị <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="new_name" placeholder="Ví dụ: Xe ben Howo" 
                           class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                    @error('new_name') <span class="text-xs text-red-600 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bộ phận sử dụng</label>
                    <input type="text" wire:model.defer="new_department" placeholder="Ví dụ: Cơ giới, Vận chuyển" 
                           class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                </div>
            </div>

            <div class="flex items-center gap-2 mt-4 justify-end">
                <button wire:click="toggleAddAsset" type="button" class="px-4 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-700 bg-white border border-slate-200 rounded-lg">
                    Hủy bỏ
                </button>
                <button wire:click="addAsset" type="button" class="px-4 py-1.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow-sm">
                    Xác nhận thêm
                </button>
            </div>
        </div>
    @endif

    <!-- Bảng danh sách định mức tài sản (BOM) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden no-print">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 uppercase text-[11px] font-black tracking-tight border-b border-slate-200 select-none">
                        <th class="py-3.5 px-3 text-center w-10">
                            <input type="checkbox" 
                                   wire:model.live="selectAll" 
                                   class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" 
                            />
                        </th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[90px]">Mã tài sản</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[150px]">Tên thiết bị</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[110px]">Bộ phận</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[120px] text-center">Dầu động cơ 15W40 (Lít)</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[120px] text-center">Nhớt thủy lực AW68 (Lít)</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[130px]">Lọc nhớt động cơ</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[130px]">Lọc thủy lực</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[130px]">Lọc gió</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[100px] text-center">Chu kỳ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/50 transition-colors {{ in_array((string)$asset->id, $selectedIds) ? 'bg-sky-50/40' : '' }}" wire:key="row-{{ $asset->id }}">
                            <!-- Checkbox -->
                            <td class="py-3 px-3 text-center">
                                <input type="checkbox" 
                                       wire:model.live="selectedIds" 
                                       value="{{ $asset->id }}" 
                                       class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" 
                                />
                            </td>
                            
                            <!-- Mã tài sản -->
                            <td class="py-3 px-4 font-bold text-sky-800 select-all uppercase">
                                {{ $asset->asset_code }}
                            </td>
                            
                            <!-- Tên thiết bị -->
                            <td class="py-3 px-4 text-slate-900 font-extrabold uppercase text-[11px] tracking-tight">
                                {{ $asset->name }}
                            </td>
                            
                            <!-- Bộ phận -->
                            <td class="py-3 px-4 text-slate-600 font-semibold">
                                {{ $asset->department ?: '---' }}
                            </td>
                            
                            <!-- Dầu động cơ 15W40 (Lít) -->
                            <td class="py-3 px-4 text-center">
                                <input type="text" 
                                       wire:model.defer="engine_oil_caps.{{ $asset->id }}" 
                                       placeholder="8" 
                                       class="w-16 py-1 px-1.5 text-center text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300"
                                />
                            </td>
                            
                            <!-- Nhớt thủy lực AW68 (Lít) -->
                            <td class="py-3 px-4 text-center">
                                <input type="text" 
                                       wire:model.defer="hydraulic_oil_caps.{{ $asset->id }}" 
                                       placeholder="35" 
                                       class="w-16 py-1 px-1.5 text-center text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300"
                                />
                            </td>
                            
                            <!-- Lọc nhớt động cơ -->
                            <td class="py-3 px-4">
                                <input type="text" 
                                       wire:model.defer="engine_oil_filters.{{ $asset->id }}" 
                                       placeholder="LF-3349" 
                                       class="w-full py-1 px-2.5 text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300 uppercase"
                                />
                            </td>
                            
                            <!-- Lọc thủy lực -->
                            <td class="py-3 px-4">
                                <input type="text" 
                                       wire:model.defer="hydraulic_filters.{{ $asset->id }}" 
                                       placeholder="HF-6710" 
                                       class="w-full py-1 px-2.5 text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300 uppercase"
                                />
                            </td>
                            
                            <!-- Lọc gió -->
                            <td class="py-3 px-4">
                                <input type="text" 
                                       wire:model.defer="air_filters.{{ $asset->id }}" 
                                       placeholder="AF-2555" 
                                       class="w-full py-1 px-2.5 text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300 uppercase"
                                />
                            </td>
                            
                            <!-- Chu kỳ -->
                            <td class="py-3 px-4 text-center">
                                <input type="text" 
                                       wire:model.defer="maintenance_cycles.{{ $asset->id }}" 
                                       placeholder="250 giờ" 
                                       class="w-24 py-1 px-2 text-center text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 px-4 text-center text-slate-400 font-bold text-sm bg-slate-25/50">
                                📭 Không tìm thấy mã tài sản hay thiết bị nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($assets->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 no-print bg-slate-50/50">
                {{ $assets->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL NHẬP ĐA PHƯƠNG THỨC (EXCEL & OCR ẢNH CHỤP) -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto no-print">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" wire:click="$set('showImportModal', false)"></div>
                
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-100">
                    
                    <!-- Tab Header -->
                    <div class="bg-slate-50 border-b border-slate-150 px-6 py-3 flex items-center justify-between">
                        <div class="flex gap-4">
                            <button @click="activeTab = 'excel'" 
                                    :class="activeTab === 'excel' ? 'border-sky-600 text-sky-600 font-black' : 'border-transparent text-slate-500 font-bold hover:text-slate-700'"
                                    class="py-2 px-1 text-sm border-b-2 transition duration-150">
                                📥 Nhập từ Excel/CSV linh hoạt
                            </button>
                            <button @click="activeTab = 'ocr'" 
                                    :class="activeTab === 'ocr' ? 'border-sky-600 text-sky-600 font-black' : 'border-transparent text-slate-500 font-bold hover:text-slate-700'"
                                    class="py-2 px-1 text-sm border-b-2 transition duration-150 flex items-center gap-1.5">
                                📷 Nhận diện từ Ảnh chụp (AI OCR)
                                <span class="bg-indigo-100 text-indigo-800 text-[10px] px-1.5 py-0.5 rounded font-black uppercase">Mới</span>
                            </button>
                        </div>
                        <button wire:click="$set('showImportModal', false)" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <!-- Tab 1: Nhập từ Excel/CSV -->
                    <div x-show="activeTab === 'excel'" class="p-6 space-y-4">
                        <div class="p-3.5 bg-emerald-50 text-emerald-850 rounded-lg text-xs font-semibold leading-relaxed border border-emerald-100">
                            ✨ <span class="font-extrabold text-emerald-950">Hệ thống đồng bộ cột thông minh:</span> Anh/chị có thể sắp xếp các cột Excel/CSV theo thứ tự tùy ý! Hệ thống sẽ quét các dòng tiêu đề như 
                            <i>"Mã tài sản", "Tên thiết bị", "Bộ phận", "Dầu động cơ", "Lọc nhớt"...</i> để tự động phân tích và trích xuất thông tin một cách chuẩn xác nhất.
                        </div>

                        <div>
                            <p class="text-xs text-slate-500 mb-2">Tải tệp mẫu để điền thông tin nhanh chóng và đúng định dạng:</p>
                            <button wire:click="downloadTemplate" class="text-sky-650 hover:text-sky-850 text-xs font-black underline flex items-center gap-1">
                                📥 Tải tệp tin Excel/CSV mẫu tiêu chuẩn tại đây
                            </button>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Chọn tệp Excel/CSV từ máy tính</label>
                            <input type="file" wire:model="excelFile" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border file:border-slate-200 file:text-xs file:font-bold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" />
                            @error('excelFile') <span class="text-red-500 text-xs font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div wire:loading wire:target="excelFile" class="text-xs text-sky-600 font-bold flex items-center gap-1.5">
                            ⏳ Đang tải tệp tin lên hệ thống...
                        </div>

                        <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
                            <button type="button" wire:click="$set('showImportModal', false)" class="rounded-lg border border-slate-200 px-4 py-2 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                Hủy bỏ
                            </button>
                            <button type="button" wire:click="importExcel" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-xs font-black text-white transition">
                                Xác nhận nhập tệp
                            </button>
                        </div>
                    </div>

                    <!-- Tab 2: Nhận diện từ Ảnh chụp AI OCR -->
                    <div x-show="activeTab === 'ocr'" class="p-6 space-y-4" @paste="handleImagePaste($event)">
                        <div class="p-3 bg-indigo-50 text-indigo-850 rounded-lg text-xs font-semibold leading-relaxed border border-indigo-100">
                            📷 <span class="font-extrabold text-indigo-950">Giải pháp nhận diện ảnh thông minh:</span> Anh/chị chỉ cần **chụp ảnh màn hình bảng Excel hoặc chụp ảnh thiết bị**, rồi nhấn **Ctrl + V** để dán ảnh trực tiếp vào đây hoặc chọn ảnh chụp để AI bóc tách định mức nhanh chóng!
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Khu vực tải ảnh & Preview -->
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center min-h-[220px] bg-slate-50 relative hover:border-sky-350 transition-colors">
                                <template x-if="!ocrImageSrc">
                                    <div class="text-center space-y-2 pointer-events-none select-none">
                                        <span class="text-3xl block">📋</span>
                                        <p class="text-xs font-bold text-slate-600">Nhấp Ctrl + V để dán ảnh chụp</p>
                                        <p class="text-[10px] text-slate-400">Hoặc click nút chọn tệp bên dưới</p>
                                        <input type="file" @change="handleImageUpload($event)" accept="image/*" class="mt-2 text-xs text-slate-500 w-44" />
                                    </div>
                                </template>
                                
                                <template x-if="ocrImageSrc">
                                    <div class="w-full flex flex-col items-center relative">
                                        <img :src="ocrImageSrc" class="max-h-[160px] rounded-lg shadow-sm border border-slate-200 object-contain" />
                                        <button @click="ocrImageSrc = ''; ocrParsedRows = []" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold shadow hover:bg-red-700">✕</button>
                                        <span class="text-[10px] text-slate-500 font-bold block mt-2">Ảnh đã sẵn sàng. Bấm bắt đầu bên phải ➡️</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Trạng thái quét AI -->
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-150 flex flex-col justify-between">
                                <div class="space-y-3">
                                    <h4 class="text-xs font-bold text-slate-700 uppercase">Trạng thái nhận diện AI</h4>
                                    <p class="text-xs font-semibold text-slate-600" x-text="ocrStatus || 'Chưa tải ảnh chụp lên...'"></p>
                                    
                                    <!-- Thanh tiến trình quét -->
                                    <template x-if="ocrRunning">
                                        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-indigo-650 h-2.5 rounded-full transition-all duration-300" :style="`width: ${ocrProgress}%`"></div>
                                        </div>
                                    </template>
                                </div>

                                <div class="pt-4">
                                    <button type="button" @click="runOCR()" :disabled="ocrRunning || !ocrImageSrc" 
                                            class="w-full py-2.5 rounded-lg text-xs font-black text-white shadow bg-indigo-600 hover:bg-indigo-750 disabled:bg-slate-350 disabled:cursor-not-allowed transition flex items-center justify-center gap-1">
                                        🔍 Bắt đầu phân tích AI OCR
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Kết quả phân tích dạng lưới xem trước -->
                        <template x-if="ocrParsedRows.length > 0">
                            <div class="border border-slate-200 rounded-lg overflow-hidden bg-white mt-4">
                                <div class="bg-slate-50 px-4 py-2 border-b border-slate-150">
                                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-tight">📋 Kết quả xem trước (Có thể sửa lại nếu sai sót)</h4>
                                </div>
                                <div class="max-h-[220px] overflow-y-auto">
                                    <table class="w-full text-left text-xs border-collapse">
                                        <thead>
                                            <tr class="bg-slate-100 font-bold border-b border-slate-200 text-slate-800">
                                                <th class="p-2 w-20">MÃ TÀI SẢN</th>
                                                <th class="p-2">Tên thiết bị</th>
                                                <th class="p-2">Bộ phận</th>
                                                <th class="p-2 text-center w-12">Dầu máy</th>
                                                <th class="p-2 text-center w-12">Thủy lực</th>
                                                <th class="p-2 w-16">Lọc nhớt</th>
                                                <th class="p-2 w-16">Lọc TL</th>
                                                <th class="p-2 w-16">Lọc gió</th>
                                                <th class="p-2 w-16 text-center">Chu kỳ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, idx) in ocrParsedRows" :key="idx">
                                                <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                                                    <td class="p-1.5"><input type="text" x-model="row.asset_code" class="w-full p-1 text-[11px] font-bold text-sky-850 uppercase border rounded bg-slate-50 focus:bg-white" /></td>
                                                    <td class="p-1.5"><input type="text" x-model="row.name" class="w-full p-1 text-[11px] font-bold uppercase border rounded bg-slate-50 focus:bg-white" /></td>
                                                    <td class="p-1.5"><input type="text" x-model="row.department" class="w-full p-1 text-[11px] border rounded bg-slate-50 focus:bg-white" /></td>
                                                    <td class="p-1.5"><input type="text" x-model="row.engine_oil_cap" class="w-full p-1 text-[11px] text-center font-bold border rounded bg-slate-50 focus:bg-white" /></td>
                                                    <td class="p-1.5"><input type="text" x-model="row.hydraulic_oil_cap" class="w-full p-1 text-[11px] text-center font-bold border rounded bg-slate-50 focus:bg-white" /></td>
                                                    <td class="p-1.5"><input type="text" x-model="row.engine_oil_filter" class="w-full p-1 text-[11px] uppercase border rounded bg-slate-50 focus:bg-white" /></td>
                                                    <td class="p-1.5"><input type="text" x-model="row.hydraulic_filter" class="w-full p-1 text-[11px] uppercase border rounded bg-slate-50 focus:bg-white" /></td>
                                                    <td class="p-1.5"><input type="text" x-model="row.air_filter" class="w-full p-1 text-[11px] uppercase border rounded bg-slate-50 focus:bg-white" /></td>
                                                    <td class="p-1.5"><input type="text" x-model="row.maintenance_cycle" class="w-full p-1 text-[11px] text-center border rounded bg-slate-50 focus:bg-white" /></td>
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
                            <button type="button" @click="submitOcrData()" :disabled="ocrParsedRows.length === 0"
                                    class="rounded-lg bg-indigo-650 hover:bg-indigo-750 disabled:bg-slate-300 disabled:cursor-not-allowed px-5 py-2 text-xs font-black text-white shadow-sm transition">
                                💾 Đồng bộ dữ liệu nhận diện vào hệ thống
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL SỬA THÔNG TIN THIẾT BỊ -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto no-print">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" wire:click="$set('showEditModal', false)"></div>
                
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-black text-slate-800 uppercase tracking-tight">✏️ Sửa thông tin thiết bị</h3>
                            <button wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Mã tài sản <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="edit_asset_code" class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                                @error('edit_asset_code') <span class="text-xs text-red-600 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tên thiết bị <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="edit_name" class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                                @error('edit_name') <span class="text-xs text-red-600 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bộ phận sử dụng</label>
                                <input type="text" wire:model.defer="edit_department" class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                            </div>
                            
                            <!-- Định mức vật tư (BOM) -->
                            <div class="mt-4 pt-4 border-t border-slate-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-tight">⚙️ Định mức vật tư (BOM)</h4>
                                    <button type="button" wire:click="addBomItem" class="text-[11px] font-bold text-sky-700 bg-sky-100 px-2 py-1 rounded hover:bg-sky-200 transition shadow-sm">
                                        + Thêm vật tư
                                    </button>
                                </div>
                                
                                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                    @if(count($bomItems) == 0)
                                        <div class="text-xs text-slate-400 italic text-center py-3 bg-slate-50 rounded-lg border border-dashed border-slate-300">
                                            Chưa có vật tư bảo dưỡng nào được thiết lập.
                                        </div>
                                    @else
                                        @foreach($bomItems as $index => $item)
                                        <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-lg border border-slate-200" wire:key="bom-item-{{ $index }}">
                                            <div class="flex-1">
                                                <input type="text" wire:model.defer="bomItems.{{ $index }}.name" placeholder="Tên vật tư (VD: Dầu 15W40...)" class="w-full text-xs px-2 py-1.5 rounded border border-slate-200 focus:ring-1 focus:ring-sky-500 font-medium" list="common-materials">
                                            </div>
                                            <div class="w-28">
                                                <input type="text" wire:model.defer="bomItems.{{ $index }}.quantity" placeholder="SL/Đơn vị (VD: 15 Lít)" class="w-full text-xs px-2 py-1.5 rounded border border-slate-200 focus:ring-1 focus:ring-sky-500 text-center font-bold text-sky-800">
                                            </div>
                                            <button type="button" wire:click="removeBomItem({{ $index }})" class="text-rose-500 hover:text-rose-700 p-1 bg-white rounded shadow-sm border border-slate-100 hover:bg-rose-50 transition" title="Xóa">
                                                ✕
                                            </button>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                                <datalist id="common-materials">
                                    <option value="Dầu động cơ 15W-40">
                                    <option value="Dầu động cơ 20W-50">
                                    <option value="Dầu cầu hộp số 80W-90">
                                    <option value="Dầu cầu hộp số 85W-140">
                                    <option value="Dầu thủy lực AW 68">
                                    <option value="Dầu thủy lực AW 46">
                                    <option value="Mỡ bôi trơn EP2">
                                    <option value="Lọc nhớt động cơ">
                                    <option value="Lọc nhiên liệu (thô)">
                                    <option value="Lọc nhiên liệu (tinh)">
                                    <option value="Lọc gió (trong)">
                                    <option value="Lọc gió (ngoài)">
                                    <option value="Lọc thủy lực">
                                    <option value="Lọc nước làm mát">
                                </datalist>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" wire:click="updateAsset" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-sky-600 hover:bg-sky-700 text-xs font-black text-white sm:w-auto transition">
                            Lưu thay đổi
                        </button>
                        <button type="button" wire:click="$set('showEditModal', false)" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-200 shadow-sm px-4 py-2 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto transition">
                            Hủy bỏ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- BẢNG IN ĐỊNH MỨC MÃ TÀI SẢN -->
    <div class="print-only print-container" style="font-family: 'Times New Roman', Times, serif;">
        <!-- Header công ty -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 16px; font-weight: bold; text-transform: uppercase; margin: 0;">CÔNG TY CỔ PHẦN ĐẦU TƯ XÂY DỰNG</h1>
                <p style="font-size: 11px; margin: 4px 0;">BỘ PHẬN QUẢN LÝ THIẾT BỊ & CƠ GIỚI</p>
                <p style="font-size: 11px; margin: 4px 0;">Điện thoại hỗ trợ: 0708091050</p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 11px; margin: 0;">Mẫu số: 03-BOM/TS</p>
                <p style="font-size: 11px; margin: 4px 0; font-style: italic;">Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div style="border-bottom: 1.5px solid black; margin-bottom: 15px;"></div>

        <!-- Tiêu đề phiếu -->
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin: 0;">BẢNG ĐỊNH MỨC VẬT TƯ & BẢO DƯỠNG MÃ TÀI SẢN</h2>
            <p style="font-size: 11px; font-style: italic; margin-top: 4px;">
                (Danh sách thiết bị cơ giới chọn lọc phục vụ công tác bảo dưỡng định kỳ)
            </p>
        </div>

        <!-- Bảng in -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Mã tài sản</th>
                    <th style="width: 22%;">Tên thiết bị</th>
                    <th style="width: 13%;">Bộ phận</th>
                    <th style="width: 11%; text-align: center;">Dầu động cơ (Lít)</th>
                    <th style="width: 11%; text-align: center;">Dầu thủy lực (Lít)</th>
                    <th style="width: 11%;">Lọc nhớt</th>
                    <th style="width: 11%;">Lọc thủy lực</th>
                    <th style="width: 11%;">Lọc gió</th>
                    <th style="width: 10%; text-align: center;">Chu kỳ</th>
                </tr>
            </thead>
            <tbody>
                @foreach(App\Models\Asset::whereIn('id', $selectedIds)->get() as $item)
                    <tr>
                        <td style="font-weight: bold; text-align: center; text-transform: uppercase;">{{ $item->asset_code }}</td>
                        <td style="font-weight: bold; text-transform: uppercase; font-size: 12px !important;">{{ $item->name }}</td>
                        <td>{{ $item->department ?: '---' }}</td>
                        <td style="text-align: center;">{{ $item->engine_oil_cap ?: '---' }}</td>
                        <td style="text-align: center;">{{ $item->hydraulic_oil_cap ?: '---' }}</td>
                        <td>{{ $item->engine_oil_filter ?: '---' }}</td>
                        <td>{{ $item->hydraulic_filter ?: '---' }}</td>
                        <td>{{ $item->air_filter ?: '---' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $item->maintenance_cycle ?: '---' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Chữ ký -->
        <div style="margin-top: 40px; display: flex; justify-content: space-between; text-align: center;">
            <div style="width: 40%;">
                <p style="font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">Người lập bảng</p>
                <p style="font-size: 10px; font-style: italic; margin: 0;">(Ký, ghi rõ họ tên)</p>
                <div style="height: 50px;"></div>
                <p style="font-weight: bold; text-transform: uppercase;">{{ auth()->user()->name ?? '........................' }}</p>
            </div>
            <div style="width: 40%;">
                <p style="font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">Trưởng bộ phận cơ giới</p>
                <p style="font-size: 10px; font-style: italic; margin: 0;">(Ký, ghi rõ họ tên)</p>
                <div style="height: 50px;"></div>
                <p style="font-weight: bold;">.................................</p>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 400);
        });
    </script>
    @endscript
</div>
