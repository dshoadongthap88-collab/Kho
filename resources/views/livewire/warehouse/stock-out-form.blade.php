<div style="font-family: 'Times New Roman', Times, serif;">
    <style>
        @media print {
            body { font-family: 'Times New Roman', Times, serif; }
            /* Căn giữa A5/A4, tối ưu lề */
            @page {
                size: A4 landscape;
                margin: 5mm; 
            }
            body, html {
                margin: 0;
                padding: 0;
                background-color: white !important;
            }
            .print-table { page-break-inside: auto; }
            .print-table tr { page-break-inside: avoid; page-break-after: auto; }
            .signatures-section { page-break-inside: avoid; }
            nav, .sidebar-toolbar, button, a, .no-print {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
            }
            body {
                background: white !important;
                font-size: 12pt;
                -webkit-print-color-adjust: exact;
            }
            .print-only {
                display: block !important;
            }
        }
        .custom-toast {
            animation: slideIn 0.3s ease-out forwards;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        @keyframes slideIn {
            0% { transform: translateY(1rem); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .custom-toast.hide {
            animation: fadeOut 0.3s ease-in forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>

    <div class="relative w-full">
        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed bottom-5 right-5 z-50 pointer-events-none flex flex-col gap-2 no-print"></div>
        <!-- Main Content -->
        <div class="w-full main-content">
            @if($activeTab === 'form')
            @if(session('success'))
                <div class="mb-4 p-2 bg-green-100 text-green-800 rounded-lg shadow-sm border border-green-200 no-print">
                    <span class="flex items-center gap-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ session('success') }}
                    </span>
                    @if(session('print_notice'))
                        <p class="mt-1 text-sm font-medium">{{ session('print_notice') }}</p>
                    @endif
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-2 bg-red-100 text-red-800 rounded-lg shadow-sm border border-red-200 no-print">
                    <span class="flex items-center gap-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ session('error') }}
                    </span>
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden printable-area no-print">
                <!-- Header visible only on screen -->
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-3 flex items-center justify-between no-print">
                    <h2 class="text-[16px] font-black text-slate-900 flex items-center gap-3 uppercase tracking-tight">
                        <span class="p-2.5 bg-indigo-600 text-white rounded-2xl shadow-lg shadow-indigo-100">📤</span>
                        {{ $editingStockOutId ? 'SỬA PHIẾU XUẤT KHO' : 'PHIẾU XUẤT KHO MỚI' }}
                    </h2>
                    <div class="flex items-center gap-2">
                        @if($editingStockOutId)
                            <button type="button" wire:click="cancelEdit" class="flex items-center gap-1.5 px-3.5 py-2 bg-slate-500 hover:bg-slate-600 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                                <span>❌</span> Hủy Sửa
                            </button>
                        @else
                            <!-- Thêm mới -->
                            <button type="button" onclick="handleResetForm('add')" class="flex items-center gap-1.5 px-3.5 py-2 bg-sky-500 hover:bg-sky-600 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                                <span>➕</span> Thêm Mới
                            </button>
                            <!-- Sửa phiếu (Thông báo) -->
                            <button type="button" onclick="showToast('Bạn đang soạn thảo trực tiếp. Chọn vật tư ở bảng dưới để sửa đổi.', '✏️')" class="flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                                <span>✏️</span> Sửa Phiếu
                            </button>
                            <!-- Xóa phiếu -->
                            <button type="button" onclick="handleResetForm('delete')" class="flex items-center gap-1.5 px-3.5 py-2 bg-rose-500 hover:bg-rose-600 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150" title="Xóa sạch dữ liệu đang điền">
                            <span>🗑️</span> Xóa Phiếu
                        </button>
                        @endif
                        <!-- In phiếu -->
                        <button type="button" onclick="handlePrint()" class="flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                            <span>🖨️</span> In Phiếu
                        </button>
                        <!-- Danh sách phiếu -->
                        <button type="button" onclick="handleSwitchTab('list')" class="flex items-center gap-1.5 px-3.5 py-2 bg-slate-600 hover:bg-slate-700 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                            <span>📋</span> Danh Sách Phiếu
                        </button>
                        <!-- Lưu phiếu -->
                        <button type="button" onclick="handleSave()" class="flex items-center gap-1.5 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                            <span>💾</span> Lưu Phiếu
                        </button>
                        <!-- Thoát -->
                        <a href="{{ route('warehouse.inventory') }}" onclick="return handleExit(event)" class="flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[12px] font-extrabold rounded-xl shadow border border-slate-200 transition duration-150">
                            <span>⬅️</span> Thoát
                        </a>
                    </div>
                </div>

                <!-- Header visible only when printing -->
                <div class="hidden print:flex items-center justify-between px-6 py-3 border-b border-black">
                    <h2 class="text-[16px] font-black text-black uppercase tracking-tight">
                        PHIẾU XUẤT KHO MỚI
                    </h2>
                    <div class="text-right">
                        <p class="text-xs font-bold text-black uppercase">Số phiếu: SO-{{ date('Ymd') }}-XXXX</p>
                        <p class="text-[10px] text-black font-bold">Ngày in: {{ date('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="p-2">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-2 mb-2">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Khách hàng / Bộ phận nhận</label>
                            <select wire:model.live="customer_name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-black text-slate-800 uppercase appearance-none">
                                <option value="">-- Chọn khách hàng / bộ phận --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->name }}">
                                        @if($customer->type === 'internal') [NỘI BỘ] 
                                        @elseif($customer->type === 'supplier') [NCC]
                                        @elseif($customer->type === 'customer') [KH]
                                        @else [ĐỐI TÁC]
                                        @endif 
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Người liên hệ</label>
                            <input type="text" wire:model="receiver_name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên người liên hệ...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Người nhận</label>
                            <input type="text" wire:model="receiver_contact" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên người nhận...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Mã tài sản</label>
                            <input type="text" wire:model="asset_code" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800 uppercase" placeholder="Nhập mã tài sản...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Loại hình xuất kho</label>
                            <select wire:model="type" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-black text-slate-800 appearance-none">
                                <option value="repair">🛠️ XUẤT CHO TỔ ĐỘI SỬA CHỮA</option>
                                <option value="delivery">🚚 XUẤT GIAO KHÁCH HÀNG</option>
                                <option value="disposal">🗑️ XUẤT HỦY</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Lý do xuất kho</label>
                            <div class="relative">
                                <select wire:model.live="note" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 pl-3 pr-8 text-[12px] font-black text-slate-800 appearance-none">
                                    <option value="">-- Chọn lý do --</option>
                                    <option value="BẢO DƯỠNG ĐỊNH KỲ">BẢO DƯỠNG ĐỊNH KỲ</option>
                                    <option value="SỬA CHỮA">SỬA CHỮA</option>
                                    <option value="CÔNG CỤ SỬA CHỮA">CÔNG CỤ SỬA CHỮA</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($type === 'repair')
                    <div class="grid grid-cols-1 md:grid-cols-7 gap-3 mb-4 p-2 bg-slate-50 rounded-2xl border border-slate-200 shadow-sm no-print">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Số Phiếu ĐNSC/BD</label>
                            <input type="text" wire:model="document_number" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Số phiếu...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Dự án (D.A)</label>
                            <input type="text" wire:model="project_name" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Mã dự án...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">BP sử dụng</label>
                            <div class="relative">
                                <select wire:model="department" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 pl-3 pr-8 text-[12px] font-bold text-slate-800 appearance-none">
                                    <option value="">-- Chọn BP --</option>
                                    <option value="BCH VINALPHA">BCH VINALPHA</option>
                                    <option value="TỔ ĐỘI KTSC VINALPHA">TỔ ĐỘI KTSC VINALPHA</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Biển số</label>
                            <input type="text" wire:model="license_plate" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800 uppercase" placeholder="Biển kiểm soát...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Mức bảo dưỡng</label>
                            @if(!empty($available_boms))
                                <select wire:model.live="selected_bom_id" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800">
                                    <option value="">-- Chọn mức bảo dưỡng --</option>
                                    @foreach($available_boms as $bom)
                                        <option value="{{ $bom['id'] }}">{{ $bom['maintenance_level'] }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" wire:model.lazy="km_number" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Gõ tự do...">
                            @endif
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Số giờ HĐ</label>
                            <input type="text" wire:model="operating_hours" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Số giờ hoạt động...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên thiết bị</label>
                            <input type="text" list="asset_list" wire:model.live="device_name" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Chọn hoặc gõ tên thiết bị...">
                            <datalist id="asset_list">
                                @foreach(\App\Models\Asset::orderBy('name')->get() as $asset)
                                    <option value="{{ $asset->name }}">{{ $asset->asset_code }}</option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                    @endif

                    @if($type === 'production')
                    <!-- Production BOM Selection Area -->
                    <div class="mb-4 p-2 bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl shadow-inner no-print">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="block text-sm font-black text-indigo-900 uppercase tracking-tight">Thành phẩm cần sản xuất</label>
                                <div class="relative">
                                    <select wire:model.live="production_product_id" class="w-full rounded-xl border-indigo-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition-all py-3 pl-4 pr-10 appearance-none bg-white font-bold text-slate-800">
                                        <option value="">-- Chọn thành phẩm từ định mức (BOM) --</option>
                                        @foreach($productionProducts as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->code }} - {{ $prod->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-indigo-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                <p class="text-[10px] text-indigo-400 font-bold px-1 italic">Hệ thống sẽ tự động điền danh sách nguyên vật liệu theo định mức đã cài đặt</p>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-black text-indigo-900 uppercase tracking-tight">Số lượng sản xuất</label>
                                <div class="flex items-center gap-3 bg-white p-1 rounded-xl border border-indigo-200 shadow-sm focus-within:ring-4 focus-within:ring-indigo-100 focus-within:border-indigo-500 transition-all">
                                    <input type="number" wire:model.live="production_quantity" step="0.01" min="0.01" class="flex-1 rounded-lg border-none focus:ring-0 shadow-none font-black text-slate-800 text-lg py-1.5" placeholder="0.00">
                                    <span class="px-4 py-2 bg-indigo-50 text-indigo-700 font-black rounded-lg text-xs uppercase border border-indigo-100">Cơ số</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="overflow-hidden border border-slate-200 rounded-2xl shadow-inner bg-slate-50/30 mb-3">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-emerald-600">
                                    <th class="px-2 py-3 text-center text-[10px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-10 no-print">IN</th>
                                    <th class="px-2 py-2 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 min-w-[350px]">TÊN VẬT TƯ / MÃ VẬT TƯ</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">Đề nghị</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">Thực xuất</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">Thu hồi</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-20">Tồn kho</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">Hãng SX</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-14">ĐVT</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-44">Code / Hạn dùng</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Vị trí</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Ghi chú</th>
                                    <th class="px-2 py-3 border-b border-slate-700 w-10 no-print"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 no-print">
                                @foreach($items as $index => $item)
                                <tr wire:key="item-{{ $index }}" class="hover:bg-slate-50/50 transition duration-150 {{ !($item['is_printed'] ?? true) ? 'no-print' : '' }}">
                                    <td class="px-3 py-1.5 text-center no-print">
                                        <input type="checkbox" wire:model.live="items.{{ $index }}.is_printed" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" wire:model.live.debounce.250ms="items.{{ $index }}.product_search" list="product_list_{{ $index }}" 
                                               class="w-full rounded-lg border-slate-300 text-xs font-bold focus:ring-indigo-500 focus:border-indigo-500 transition placeholder:font-normal uppercase {{ $type === 'production' ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                               placeholder="Mã hoặc tên vật tư..." {{ $type === 'production' ? 'readonly' : '' }}>
                                        <datalist id="product_list_{{ $index }}">
                                            @foreach($products as $product)
                                                <option value="{{ $product->code }} - {{ $product->name }}"></option>
                                            @endforeach
                                        </datalist>
                                        @error("items.{$index}.product_id") <p class="text-red-500 text-[10px] mt-1 no-print">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.{{ $index }}.requested_quantity" 
                                               class="w-full text-center text-xs font-bold rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                               placeholder="1">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.{{ $index }}.quantity" {{ $type === 'production' ? 'readonly' : '' }}
                                               class="w-full text-center text-xs font-black rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0 {{ $type === 'production' ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                               placeholder="0">
                                        @error("items.{$index}.quantity") <p class="text-red-500 text-[10px] mt-1 no-print">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.{{ $index }}.recovered_quantity" 
                                               class="w-full text-center text-xs font-bold rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                               placeholder="0">
                                    </td>
                                    <td class="px-2 py-1.5 text-center">
                                        @if(isset($items[$index]['available_qty']))
                                            <div class="text-xs no-print whitespace-nowrap">
                                                <span class="font-black text-slate-800">{{ number_format(floatval($items[$index]['available_qty']), 0) }}</span>
                                                @if(floatval($items[$index]['available_qty']) >= floatval($items[$index]['quantity'] ?? 0))
                                                    <span class="text-green-600 font-bold block text-[9px] mt-0.5">🟢 Đủ</span>
                                                @else
                                                    <span class="text-red-500 font-bold block text-[9px] mt-0.5">🔴 Thiếu</span>
                                                @endif
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-2 py-4 text-center">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase">{{ $items[$index]['brand'] ?? '-' }}</span>
                                    </td>
                                    <td class="px-1 py-1.5 text-center">
                                        <span class="inline-block px-1.5 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-600 border border-slate-200 min-w-[30px]">
                                            {{ $items[$index]['unit'] ?: '-' }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" wire:model.live="items.{{ $index }}.batch_number" 
                                               class="w-full rounded-lg text-[10px] border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition mb-1" placeholder="Số lô...">
                                        <input type="date" wire:model="items.{{ $index }}.expiry_date" 
                                               class="w-full rounded-lg border-slate-300 text-[9px] focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" wire:model="items.{{ $index }}.warehouse_location"
                                               class="w-full text-[10px] rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0" placeholder="Vị trí...">
                                    </td>
                                    <td class="px-2 py-1.5 w-24">
                                        <input type="text" wire:model="items.{{ $index }}.item_note"
                                               class="w-24 text-[10px] rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Ghi chú...">
                                    </td>
                                    <td class="px-2 py-1.5 text-center no-print">
                                        @if(count($items) > 1 || $type === 'manual')
                                            <button wire:click="removeItem({{ $index }})" class="text-slate-400 hover:text-red-500 transition p-1 rounded-full hover:bg-red-50" title="Xóa dòng">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between mb-8 no-print">
                        @if($this->canAddItem() && $type !== 'production')
                            <button wire:click="addItem" class="text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg font-semibold text-sm flex items-center gap-2 transition border border-indigo-200 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Thêm dòng vật tư
                            </button>
                        @endif
                    </div>

                    <div class="mt-8 mb-4 p-5 bg-slate-50 rounded-2xl border border-slate-200 shadow-sm no-print">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-2">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên Nhân viên vận hành</label>
                                <input type="text" wire:model.live="operator_name" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên người vận hành...">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên Tổ trưởng/ trưởng ca QLTB / vận hành</label>
                                <input type="text" wire:model.live="supervisor_qltb" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên tổ trưởng QLTB...">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên Nhân viên KTSC</label>
                                <select wire:model.live="repair_staff" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-2 px-3 text-[12px] font-bold text-slate-800">
                                    <option value="">-- Chọn nhân viên --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->name }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên THỦ KHO</label>
                                <input type="text" wire:model.live="warehouse_keeper" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên thủ kho...">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên Tổ trưởng / trưởng ca</label>
                                <input type="text" wire:model.live="supervisor_ca" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên tổ trưởng / trưởng ca...">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end items-center gap-2 no-print mt-2">
                        <a href="{{ route('warehouse.inventory') }}" class="px-6 py-2 border border-slate-300 rounded-xl text-slate-600 text-sm font-semibold hover:bg-slate-50 transition duration-150">
                            Hủy bỏ
                        </a>
                        <button wire:click="save" class="bg-indigo-600 text-white px-8 py-2 rounded-xl text-sm font-black hover:bg-indigo-700 transition duration-150 shadow-md flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            {{ $editingStockOutId ? 'Lưu Thay Đổi' : 'Xác nhận xuất kho' }}
                        </button>
                    </div>

                </div>
            </div>

            @if($showZeroStockConfirm)
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm no-print">
                <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md overflow-hidden animate-[slideIn_0.2s_ease-out]">
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <span class="text-2xl">⚠️</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-slate-900 uppercase">Cảnh báo tồn kho</h3>
                                <p class="text-sm text-slate-500 mt-1">Có vật tư với số lượng tồn kho = 0. Bạn vẫn xác nhận xuất kho?</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 mt-8">
                            <button wire:click="cancelSave" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                                Không Đồng Ý
                            </button>
                            <button wire:click="confirmSave" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-black text-sm shadow transition">
                                Đồng Ý Xuất Kho
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- PHẦN IN PDF BỊ ẨN KHI XEM THƯỜNG (KIỂU V-ALPHA ĐỀ NGHỊ CẤP VẬT TƯ SỬA CHỮA) -->
            <div class="hidden print-only print-container inset-0 bg-white w-full text-black" style="font-family: 'Times New Roman', serif; padding: 5mm; line-height: 1.3;">
                <!-- Logo & Title Section -->
                <div class="grid grid-cols-12 items-center mb-3">
                    <!-- Logo & Company Name (Inline side-by-side) -->
                    <div class="col-span-5 flex items-center gap-3">
                        <!-- Image Logo -->
                        <div class="flex flex-col items-center min-w-[65px]">
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/v-alpha-logo.png'))) }}" alt="V-ALPHA Logo" class="w-12 h-auto object-contain">
                        </div>
                        <!-- Company Name & Department -->
                        <div class="text-left font-bold text-slate-800 leading-tight">
                            <p class="text-[9.5px] uppercase tracking-wide">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ THI CÔNG HẠ TẦNG V- ALPHA</p>
                            <p class="text-[8px] uppercase tracking-tight text-slate-600 mt-1">PHÒNG KỸ THUẬT SỬA CHỮA</p>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="col-span-5 text-center">
                        <h1 class="text-sm font-bold uppercase tracking-tight leading-normal" style="font-size: 13px;">
                            ĐỀ NGHỊ CẤP VẬT TƯ SỬA CHỮA & BẢO DƯỠNG<br>
                            KIÊM PHIẾU XUẤT KHO
                        </h1>
                        <p class="text-[10px] mt-1 italic">
                            Ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}
                        </p>
                        <p class="text-[10px] font-bold mt-1">
                            Số Phiếu ĐNSC/BD: <span class="font-bold underline">{{ $document_number ?: '..................................................' }}</span>
                        </p>
                    </div>

                    <!-- Form Code & Project -->
                    <div class="col-span-2 text-right text-[10px] self-start space-y-1">
                        <p class="font-bold text-[9px] text-slate-500 tracking-wider">BM01-ĐNCVT</p>
                        <p class="font-bold mt-2">D.A: <span class="font-black underline">{{ $project_name ?: '_' }}</span></p>
                    </div>
                </div>

                <!-- Metadata Details Table Grid (Matching Row 1 & Row 2 with solid black borders) -->
                <table class="w-full border-collapse border border-black text-[11px] mb-3 font-bold text-black" style="line-height: 1.4;">
                    <tbody>
                        <tr>
                            <!-- Họ và tên người nhận hàng -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" colspan="2" style="width: 35%;">
                                Họ và tên người nhận hàng: <span class="font-normal">{{ $receiver_contact ?: '........................................' }}</span>
                            </td>
                            <!-- BP sử dụng -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 25%;">
                                BP sử dụng : <span class="font-normal">{{ $department ?: '................................' }}</span>
                            </td>
                            <!-- Mã tài sản -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 20%;">
                                Mã tài sản: <span class="font-normal font-mono uppercase">{{ $asset_code ?: '................................' }}</span>
                            </td>
                            <!-- Biển số -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 20%;">
                                Biển số : <span class="font-normal uppercase">{{ $license_plate ?: '................................' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <!-- Mức bảo dưỡng -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 17.5%;">
                                Mức BD : <span class="font-normal">{{ $km_number ?: '................................' }}</span>
                            </td>
                            <!-- Số giờ HĐ -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 17.5%;">
                                Số giờ HĐ : <span class="font-normal">{{ $operating_hours ?: '................................' }}</span>
                            </td>
                            <!-- Tên thiết bị -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 25%;">
                                Tên thiết bị: <span class="font-normal">{{ $device_name ?: '................................' }}</span>
                            </td>
                            <!-- Lý do xuất kho (spans columns 3 & 4) -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" colspan="2" style="width: 40%;">
                                Lý do xuất kho: <span class="font-normal">{{ $note ?: '................................' }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Items Table -->
                <table class="print-table w-full border-collapse border-2 border-black text-[11px] mb-3">
                    <thead>
                        <tr class="bg-gray-100 text-center font-bold">
                            <th class="border border-black px-1 py-1 w-8">STT</th>
                            <th class="border border-black px-2 py-1 text-left">TÊN VẬT TƯ SỬA CHỮA</th>
                            <th class="border border-black px-2 py-1 w-24">MÃ VẬT TƯ</th>
                            <th class="border border-black px-1 py-1 w-12">ĐVT</th>
                            <th class="border border-black px-1 py-1 w-16">ĐỀ NGHỊ</th>
                            <th class="border border-black px-1 py-1 w-16">THỰC XUẤT</th>
                            <th class="border border-black px-1 py-1 w-16">THU HỒI</th>
                            <th class="border border-black px-2 py-1 w-32">GHI CHÚ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $validCount = 0; @endphp
                        @foreach($items as $index => $item)
                            @if($item['product_id'] && ($item['is_printed'] ?? true))
                                @php $validCount++; @endphp
                                <tr>
                                    <td class="border border-black px-1 py-1.5 text-center">{{ $validCount }}</td>
                                    <td class="border border-black px-2 py-1.5 font-bold uppercase">
                                        {{ str_contains($item['product_search'] ?? '', ' - ') ? explode(' - ', $item['product_search'], 2)[1] : ($item['product_search'] ?? '') }}
                                    </td>
                                    <td class="border border-black px-2 py-1.5 text-center font-mono uppercase">
                                        {{ str_contains($item['product_search'] ?? '', ' - ') ? explode(' - ', $item['product_search'], 2)[0] : '' }}
                                    </td>
                                    <td class="border border-black px-1 py-1.5 text-center">{{ $item['unit'] ?: '-' }}</td>
                                    <td class="border border-black px-1 py-1.5 text-center font-bold">{{ (float)($item['requested_quantity'] ?? $item['quantity']) }}</td>
                                    <td class="border border-black px-1 py-1.5 text-center font-bold">{{ (float)$item['quantity'] }}</td>
                                    <td class="border border-black px-1 py-1.5 text-center">{{ (float)($item['recovered_quantity'] ?? 0) }}</td>
                                    <td class="border border-black px-2 py-1.5">{{ $item['item_note'] ?: '' }}</td>
                                </tr>
                            @endif
                        @endforeach
                        @php 
                            $minRows = max(8, ceil($validCount / 8) * 8); 
                        @endphp
                        @for($i = $validCount; $i < $minRows; $i++)
                            <tr>
                                <td class="border border-black px-1 py-2 text-center font-bold">{{ $i + 1 }}</td>
                                <td class="border border-black px-2 py-2"></td>
                                <td class="border border-black px-2 py-2"></td>
                                <td class="border border-black px-1 py-2"></td>
                                <td class="border border-black px-1 py-2"></td>
                                <td class="border border-black px-1 py-2"></td>
                                <td class="border border-black px-1 py-2"></td>
                                <td class="border border-black px-2 py-2"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                <!-- Signatures Section -->
                <div class="grid grid-cols-5 gap-2 text-center text-[10px] mt-8 font-bold leading-normal relative z-10">
                    <div>
                        <p>Nhân viên vận hành</p>
                        <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                        <div style="height: 50px;"></div>
                        <p class="font-bold text-slate-800 text-[11px] mt-1">{{ $operator_name ?: '........................' }}</p>
                    </div>
                    <div>
                        <p>Tổ trưởng/ trưởng ca QLTB / vận hành</p>
                        <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                        <div style="height: 50px;"></div>
                        <p class="font-bold text-slate-800 text-[11px] mt-1">{{ $supervisor_qltb ?: '........................' }}</p>
                    </div>
                    <div>
                        <p>Tổ trưởng / trưởng ca</p>
                        <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                        <div style="height: 50px;"></div>
                        <p class="font-bold text-slate-800 text-[11px] mt-1">{{ $supervisor_ca ?: '........................' }}</p>
                    </div>
                </div>
            </div>
            @elseif($activeTab === 'list')
                <!-- Stock Out List Section -->
                <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden min-h-[600px] main-content">
                    <!-- Print Title (Only visible when printing) -->
                    <div class="hidden print:block text-center mb-8">
                        <h1 class="text-2xl font-black uppercase underline decoration-double">DANH SÁCH PHIẾU XUẤT KHO</h1>
                        <p class="text-[13px] font-bold mt-1">TỪ NGÀY: {{ \Carbon\Carbon::parse($listDateFrom)->format('d/m/Y') }} - ĐẾN NGÀY: {{ \Carbon\Carbon::parse($listDateTo)->format('d/m/Y') }}</p>
                    </div>

                    <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2 no-print">
                        <h2 class="text-[15px] font-black text-slate-900 flex items-center gap-2 uppercase tracking-tight">
                            <span class="p-2 bg-indigo-600 text-white rounded-xl shadow-lg">📋</span>
                            LỊCH SỬ PHIẾU XUẤT KHO
                        </h2>
                        
                        <div class="flex flex-wrap items-center gap-3 no-print">
                            <!-- Date Range -->
                            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-inner focus-within:ring-4 focus-within:ring-indigo-100 transition-all">
                                <div class="flex items-center gap-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Từ ngày</label>
                                    <input type="date" wire:model.live="listDateFrom" class="text-[12px] border-none focus:ring-0 p-0 font-black text-slate-700 bg-transparent">
                                </div>
                                <div class="w-px h-4 bg-slate-200 mx-2"></div>
                                <div class="flex items-center gap-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Đến ngày</label>
                                    <input type="date" wire:model.live="listDateTo" class="text-[12px] border-none focus:ring-0 p-0 font-black text-slate-700 bg-transparent">
                                </div>
                            </div>

                            <!-- Search -->
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="listSearch" placeholder="TÌM MÃ, KHÁCH HÀNG..." class="pl-11 pr-4 py-2.5 w-64 text-[12px] font-black rounded-2xl border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all bg-white placeholder:text-slate-300">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 ml-2">
                                @if(count($selectedIds) > 0)
                                    <div class="flex items-center gap-2 pr-3 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4 duration-300">
                                        <span class="text-[11px] font-black text-indigo-700 bg-indigo-50 px-2.5 py-1.5 rounded-lg border border-indigo-100">CHỌN: {{ count($selectedIds) }}</span>
                                        <button wire:click="deleteSelected" wire:confirm="Xác nhận xóa {{ count($selectedIds) }} phiếu xuất?" class="flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-rose-500 to-rose-600 text-white rounded-xl text-[12px] font-black transition-all hover:scale-105 shadow-md">
                                            <span>🗑️</span> XÓA
                                        </button>
                                        <button wire:click="printSelected" class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-indigo-600 text-indigo-700 hover:bg-indigo-50 rounded-xl text-[12px] font-black transition-all shadow-sm">
                                            <span>🖨️</span> IN GHÉP
                                        </button>
                                    </div>
                                @endif
                                <button wire:click="exportExcel" class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 rounded-xl text-[12px] font-black transition-all shadow-lg shadow-emerald-100">
                                    <span class="text-sm">📊</span> EXCEL
                                </button>
                                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-800 text-white hover:bg-black rounded-xl text-[12px] font-black transition-all shadow-lg">
                                    <span class="text-sm">📄</span> IN PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-[11px] font-black text-white uppercase tracking-widest bg-slate-800 border-b border-slate-700">
                                @php
                                    $idsOnPage = $stockOuts->pluck('id')->toArray();
                                @endphp
                                <tr>
                                    <th class="px-2 py-2 w-10 no-print text-center">
                                        <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $idsOnPage) }}])" {{ count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-2 py-4">MÃ PHIẾU</th>
                                    <th class="px-2 py-2">NGÀY TẠO</th>
                                    <th class="px-2 py-2">KHÁCH HÀNG / BỘ PHẬN</th>
                                    <th class="px-2 py-2">NGƯỜI LIÊN HỆ / MÃ TS</th>
                                    <th class="px-2 py-2">LOẠI XUẤT</th>
                                    <th class="px-2 py-2 text-right">TỔNG TIỀN</th>
                                    <th class="px-2 py-2">GHI CHÚ</th>
                                    <th class="px-2 py-2 text-center no-print">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($stockOuts as $so)
                                    <tr class="hover:bg-indigo-50/30 transition-all group {{ in_array($so->id, $selectedIds) ? 'bg-indigo-50' : '' }}">
                                        <td class="px-2 py-1.5 no-print text-center">
                                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $so->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-2 py-4 font-black text-indigo-700 tracking-tight">{{ $so->code }}</td>
                                        <td class="px-2 py-1.5 text-slate-500 text-[12px] font-bold">{{ $so->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-2 py-1.5 font-black text-slate-800 text-[13px] uppercase tracking-tighter">{{ $so->customer_name ?: '-' }}</td>
                                        <td class="px-2 py-1.5">
                                            <div class="text-[12px] font-bold text-slate-700 uppercase">{{ $so->receiver_name ?: '-' }}</div>
                                            <div class="text-[10px] font-black text-indigo-600">{{ $so->asset_code }}</div>
                                        </td>
                                        <td class="px-2 py-1.5">
                                            @switch($so->type)
                                                @case('repair') <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[10px] font-black uppercase border border-blue-100">🛠️ SỬA CHỮA</span> @break
                                                @case('delivery') <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-black uppercase border border-emerald-100">🚚 GIAO HÀNG</span> @break
                                                @case('disposal') <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-[10px] font-black uppercase border border-red-100">🗑️ HỦY</span> @break
                                                @default <span class="px-2.5 py-1 bg-slate-50 text-slate-600 rounded-lg text-[10px] font-black uppercase border border-slate-100">KHÁC</span>
                                            @endswitch
                                        </td>
                                        <td class="px-2 py-1.5 text-right font-black text-slate-900 text-[14px]">
                                            {{ number_format($so->items->sum('total_amount')) }} đ
                                        </td>
                                        <td class="px-2 py-1.5 text-slate-400 text-[11px] font-bold italic truncate max-w-[150px]" title="{{ $so->note }}">{{ $so->note ?: '-' }}</td>
                                        <td class="px-2 py-1.5 text-center no-print">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="printSingle({{ $so->id }})" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="In phiếu này">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                </button>
                                                <button wire:click="edit({{ $so->id }})" class="p-2 text-amber-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition-all" title="Sửa phiếu">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </button>
                                                <button wire:confirm="Xác nhận xóa phiếu xuất {{ $so->code }}? Tồn kho sẽ được hoàn trả tự động." wire:click="delete({{ $so->id }})" class="p-2 text-rose-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Xóa phiếu">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center gap-2">
                                                <span class="text-4xl text-slate-200">🔍</span>
                                                <p class="text-xs font-bold">Không tìm thấy phiếu xuất nào trong khoảng thời gian này</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-50 no-print">
                        {{ $stockOuts->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- PHẦN IN CHI TIẾT HÀNG LOẠT (Nhanh/Ghép) - THEO MẪU ĐỀ NGHỊ BẢO DƯỠNG V-ALPHA -->
    @if(count($printItems) > 0)
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        @foreach($printItems as $pItem)
        <div class="print-page p-8 bg-white" style="font-family: 'Times New Roman', serif; min-height: 200mm; page-break-after: always; line-height: 1.3;">
            <!-- Logo & Title Section -->
            <div class="grid grid-cols-12 items-center mb-3">
                <!-- Logo & Company Name (Inline side-by-side) -->
                <div class="col-span-5 flex items-center gap-3">
                    <!-- Image Logo -->
                    <div class="flex flex-col items-center min-w-[65px]">
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/v-alpha-logo.png'))) }}" alt="V-ALPHA Logo" class="w-12 h-auto object-contain">
                    </div>
                    <!-- Company Name & Department -->
                    <div class="text-left font-bold text-slate-800 leading-tight">
                        <p class="text-[9.5px] uppercase tracking-wide">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ THI CÔNG HẠ TẦNG V- ALPHA</p>
                        <p class="text-[8px] uppercase tracking-tight text-slate-600 mt-1">PHÒNG KỸ THUẬT SỬA CHỮA</p>
                    </div>
                </div>

                <!-- Title -->
                <div class="col-span-5 text-center">
                    <h1 class="text-sm font-bold uppercase tracking-tight leading-normal" style="font-size: 13px;">
                        ĐỀ NGHỊ CẤP VẬT TƯ SỬA CHỮA & BẢO DƯỠNG<br>
                        KIÊM PHIẾU XUẤT KHO
                    </h1>
                    <p class="text-[10px] mt-1 italic">
                        Ngày {{ $pItem->created_at ? $pItem->created_at->format('d') : '.....' }} 
                        tháng {{ $pItem->created_at ? $pItem->created_at->format('m') : '.....' }} 
                        năm {{ $pItem->created_at ? $pItem->created_at->format('Y') : '2026' }}
                    </p>
                    <p class="text-[10px] font-bold mt-1">
                        Số Phiếu ĐNSC/BD: <span class="font-bold underline">{{ $pItem->document_number ?: '..................................................' }}</span>
                    </p>
                </div>

                <!-- Form Code & Project -->
                <div class="col-span-2 text-right text-[10px] self-start space-y-1">
                    <p class="font-bold text-[9px] text-slate-500 tracking-wider">BM01-ĐNCVT</p>
                    <p class="font-bold mt-2">D.A: <span class="font-black underline">{{ $pItem->project_name ?: '_' }}</span></p>
                </div>
            </div>

            <!-- Metadata Details Table Grid (Matching Row 1 & Row 2 with solid black borders) -->
            <table class="w-full border-collapse border border-black text-[11px] mb-3 font-bold text-black" style="line-height: 1.4;">
                <tbody>
                    <tr>
                        <!-- Họ và tên người nhận hàng -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" colspan="2" style="width: 35%;">
                            Họ và tên người nhận hàng: <span class="font-normal">{{ $pItem->receiver_contact ?: '........................................' }}</span>
                        </td>
                        <!-- BP sử dụng -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 25%;">
                            BP sử dụng : <span class="font-normal">{{ $pItem->department ?: '................................' }}</span>
                        </td>
                        <!-- Mã tài sản -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 20%;">
                            Mã tài sản: <span class="font-normal font-mono uppercase">{{ $pItem->asset_code ?: '................................' }}</span>
                        </td>
                        <!-- Biển số -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 20%;">
                            Biển số : <span class="font-normal uppercase">{{ $pItem->license_plate ?: '................................' }}</span>
                        </td>
                    </tr>
                    <tr>
                        <!-- Mức bảo dưỡng -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 17.5%;">
                            Mức BD : <span class="font-normal">{{ $pItem->km_number ?: '................................' }}</span>
                        </td>
                        <!-- Số giờ HĐ -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 17.5%;">
                            Số giờ HĐ : <span class="font-normal">{{ $pItem->operating_hours ?: '................................' }}</span>
                        </td>
                        <!-- Tên thiết bị -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 25%;">
                            Tên thiết bị: <span class="font-normal">{{ $pItem->device_name ?: '................................' }}</span>
                        </td>
                        <!-- Lý do xuất kho (spans columns 3 & 4) -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" colspan="2" style="width: 40%;">
                            Lý do xuất kho: <span class="font-normal">{{ $pItem->note ?: '................................' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Items Table -->
            <table class="print-table w-full border-collapse border-2 border-black text-[11px] mb-3">
                <thead>
                    <tr class="bg-gray-100 text-center font-bold">
                        <th class="border border-black px-1 py-1 w-8">STT</th>
                        <th class="border border-black px-2 py-1 text-left">TÊN VẬT TƯ SỬA CHỮA</th>
                        <th class="border border-black px-2 py-1 w-24">MÃ VẬT TƯ</th>
                        <th class="border border-black px-1 py-1 w-12">ĐVT</th>
                        <th class="border border-black px-1 py-1 w-16">ĐỀ NGHỊ</th>
                        <th class="border border-black px-1 py-1 w-16">THỰC XUẤT</th>
                        <th class="border border-black px-1 py-1 w-16">THU HỒI</th>
                        <th class="border border-black px-2 py-1 w-32">GHI CHÚ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pItem->items as $idx => $ii)
                    <tr>
                        <td class="border border-black px-1 py-1.5 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-black px-2 py-1.5 font-bold uppercase">
                            {{ $ii->product->name }}
                        </td>
                        <td class="border border-black px-2 py-1.5 text-center font-mono uppercase">
                            {{ $ii->product->code }}
                        </td>
                        <td class="border border-black px-1 py-1.5 text-center">{{ $ii->product->unit ?: '-' }}</td>
                        <td class="border border-black px-1 py-1.5 text-center font-bold">{{ (float)($ii->requested_quantity ?: $ii->quantity) }}</td>
                        <td class="border border-black px-1 py-1.5 text-center font-bold">{{ (float)$ii->quantity }}</td>
                        <td class="border border-black px-1 py-1.5 text-center">{{ (float)($ii->recovered_quantity ?: 0) }}</td>
                        <td class="border border-black px-2 py-1.5">{{ $ii->item_note ?: '' }}</td>
                    </tr>
                    @endforeach
                    @php
                        $validCount = count($pItem->items);
                        $minRows = max(8, ceil($validCount / 8) * 8);
                    @endphp
                    @for($i = $validCount; $i < $minRows; $i++)
                    <tr>
                        <td class="border border-black px-1 py-2 text-center font-bold">{{ $i + 1 }}</td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-1 py-2"></td>
                        <td class="border border-black px-1 py-2"></td>
                        <td class="border border-black px-1 py-2"></td>
                        <td class="border border-black px-1 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                    </tr>
                    @endfor
                </tbody>
            </table>

            <!-- Footer Signatures Section (5 Signatures) -->
            <div class="signatures-section grid grid-cols-5 gap-2 text-center text-[10px] mt-6 font-bold leading-normal">
                <div>
                    <p>Nhân viên vận hành</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-bold text-slate-800 text-[11px] mt-1">{{ $pItem->operator_name ?: '........................' }}</p>
                </div>
                <div>
                    <p>Tổ trưởng/ trưởng ca QLTB / vận hành</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-bold text-slate-800 text-[11px] mt-1">{{ $pItem->supervisor_qltb ?: '........................' }}</p>
                </div>
                <div>
                    <p>Nhân viên KTSC</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-bold text-slate-800 text-[11px] mt-1">{{ $pItem->repair_staff ?: '........................' }}</p>
                </div>
                <div>
                    <p>Thủ kho</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-bold text-slate-800 text-[11px] mt-1">{{ $pItem->warehouse_keeper ?: '........................' }}</p>
                </div>
                <div>
                    <p>Tổ trưởng / trưởng ca</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-bold text-slate-800 text-[11px] mt-1">{{ $pItem->supervisor_ca ?: '........................' }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @script
    <script>
        let isDirty = false;

        // Reset dirty flag when form is loaded or saved
        window.addEventListener('livewire:initialized', () => {
            // Listen for form inputs
            document.addEventListener('input', (e) => {
                isDirty = true;
            });
            
            document.addEventListener('change', (e) => {
                isDirty = true;
            });
        });

        // 3s Toast Notification
        window.showToast = function(message, icon = '✅', duration = 3000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = "custom-toast flex items-center gap-3 text-white px-5 py-3.5 rounded-2xl shadow-2xl pointer-events-auto transform transition-all duration-300 text-xs font-black uppercase tracking-wider";
            toast.innerHTML = `<span class="text-base">${icon}</span> <span>${message}</span>`;
            
            container.appendChild(toast);

            // Auto dismiss
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, duration);
        };

        // Listeners for Livewire events
        $wire.on('stock-out-saved', () => {
            isDirty = false;
            showToast("Lưu phiếu xuất kho thành công!", "✅", 3000);
        });

        $wire.on('stock-out-deleted', (event) => {
            const count = event.count || 1;
            showToast(`Đã xóa thành công ${count} phiếu xuất kho!`, "🗑️", 3000);
        });

        $wire.on('stock-out-printing', () => {
            showToast("Đang tải dữ liệu và chuẩn bị in phiếu...", "🖨️", 3000);
        });

        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 500);
        });

        // Handlers
        window.handleResetForm = function(actionType) {
            if (isDirty) {
                if (!confirm("Dữ liệu phiếu xuất kho hiện tại đang có thay đổi. Bạn có chắc chắn muốn bỏ qua các thay đổi này không?")) {
                    return;
                }
            }
            isDirty = false;
            $wire.resetForm();
            if (actionType === 'add') {
                showToast("Đã khởi tạo phiếu xuất kho mới thành công!", "➕", 3000);
            } else {
                showToast("Đã xóa sạch dữ liệu trên phiếu!", "🗑️", 3000);
            }
        };

        window.handlePrint = function() {
            showToast("Đang chuẩn bị bản in phiếu xuất kho...", "🖨️", 3000);
            setTimeout(() => {
                window.print();
            }, 300);
        };

        window.handleSwitchTab = function(tab) {
            if (isDirty) {
                if (!confirm("Dữ liệu phiếu xuất kho đang có thay đổi chưa lưu. Bạn có chắc chắn muốn chuyển sang danh sách phiếu không?")) {
                    return;
                }
            }
            isDirty = false;
            $wire.switchTab(tab);
        };

        window.handleSave = function() {
            showToast("Đang tiến hành lưu phiếu xuất kho...", "💾", 3000);
            $wire.save();
        };

        window.handleExit = function(e) {
            if (isDirty) {
                if (!confirm("Dữ liệu phiếu xuất kho đang có thay đổi chưa lưu. Bạn có chắc chắn muốn thoát không?")) {
                    e.preventDefault();
                    return false;
                }
            }
            return true;
        };
    </script>
    @endscript
</div>
