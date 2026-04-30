<div style="font-family: 'Times New Roman', Times, serif;">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0; /* Xóa margin mặc định để ẩn URL/Date của trình duyệt */
            }
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
    </style>

    <div class="relative flex items-start gap-2">
        <!-- Sidebar Toolbar (Left) -->
        <div class="sidebar-toolbar sticky top-24 hidden md:flex flex-col gap-4 no-print">
            <div class="flex flex-col gap-2 mb-4 bg-slate-100 p-2 rounded-2xl shadow-inner border border-slate-200">
                <button wire:click="switchTab('form')" class="w-14 h-14 flex items-center justify-center rounded-xl transition-all {{ $activeTab === 'form' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-600 hover:bg-white' }}" title="LẬP PHIẾU MỚI">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
                <button wire:click="switchTab('list')" class="w-14 h-14 flex items-center justify-center rounded-xl transition-all {{ $activeTab === 'list' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-slate-600 hover:bg-white' }}" title="DANH SÁCH PHIẾU">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </button>
            </div>

            <button onclick="window.print()" class="group flex flex-col items-center justify-center w-16 h-16 bg-white border border-slate-200 rounded-2xl shadow-sm hover:bg-indigo-50 hover:border-indigo-200 transition-all duration-300" title="IN PHIẾU XUẤT">
                <svg class="w-7 h-7 text-slate-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span class="text-[9px] font-black text-slate-500 uppercase mt-1 group-hover:text-indigo-600">IN PHIẾU</span>
            </button>

            <a href="{{ route('warehouse.inventory') }}" class="group flex flex-col items-center justify-center w-16 h-16 bg-white border border-slate-200 rounded-2xl shadow-sm hover:bg-slate-50 transition-all duration-300" title="QUAY LẠI">
                <svg class="w-6 h-6 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="text-[9px] font-black text-slate-400 uppercase mt-1 group-hover:text-slate-600">THOÁT</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="flex-1 main-content">
            @if($activeTab === 'form')
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg shadow-sm border border-green-200 no-print">
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
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg shadow-sm border border-red-200 no-print">
                    <span class="flex items-center gap-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ session('error') }}
                    </span>
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden printable-area no-print">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-3 flex items-center justify-between">
                    <h2 class="text-[16px] font-black text-slate-900 flex items-center gap-3 uppercase tracking-tight">
                        <span class="p-2.5 bg-indigo-600 text-white rounded-2xl shadow-lg shadow-indigo-100">📤</span>
                        PHIẾU XUẤT KHO MỚI
                    </h2>
                    <div class="hidden print-only text-right">
                        <p class="text-sm font-bold text-slate-600 uppercase">Số phiếu: SO-{{ date('Ymd') }}-XXXX</p>
                        <p class="text-xs text-slate-400 font-bold">Ngày in: {{ date('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-2">
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
                            <input type="text" wire:model="receiver_name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên người nhận...">
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
                    </div>

                    @if($type === 'production')
                    <!-- Production BOM Selection Area -->
                    <div class="mb-4 p-4 bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl shadow-inner no-print">
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
                                <tr class="bg-slate-800">
                                    <th class="px-2 py-3 text-center text-[10px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-10 no-print">IN</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 min-w-[220px]">Sản phẩm / Vật tư</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-20">SL Xuất</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Tồn kho</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-20">Hãng SX</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-14">ĐVT</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-32">Mã Code NCC / Hạn dùng</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-20">Vị trí</th>
                                    <th class="px-2 py-3 text-right text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Đơn giá</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-14">VAT</th>
                                    <th class="px-2 py-3 text-right text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-28">Thành tiền</th>
                                    <th class="px-2 py-3 border-b border-slate-700 w-10 no-print"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 no-print">
                                @foreach($items as $index => $item)
                                <tr wire:key="item-{{ $index }}" class="hover:bg-slate-50/50 transition duration-150 {{ !$item['is_printed'] ? 'no-print' : '' }}">
                                    <td class="px-3 py-1.5 text-center no-print">
                                        <input type="checkbox" wire:model.live="items.{{ $index }}.is_printed" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" wire:model.live.debounce.250ms="items.{{ $index }}.product_search" list="product_list_{{ $index }}" 
                                               class="w-full rounded-lg border-slate-300 text-xs font-bold focus:ring-indigo-500 focus:border-indigo-500 transition placeholder:font-normal uppercase {{ $type === 'production' ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                               placeholder="Mã hoặc tên SP..." {{ $type === 'production' ? 'readonly' : '' }}>
                                        <datalist id="product_list_{{ $index }}">
                                            @foreach($products as $product)
                                                <option value="{{ $product->code }} - {{ $product->name }}"></option>
                                            @endforeach
                                        </datalist>
                                        @error("items.{$index}.product_id") <p class="text-red-500 text-[10px] mt-1 no-print">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.{{ $index }}.quantity" {{ $type === 'production' ? 'readonly' : '' }}
                                               class="w-full text-center text-xs font-black rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0 {{ $type === 'production' ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                               placeholder="0">
                                        @error("items.{$index}.quantity") <p class="text-red-500 text-[10px] mt-1 no-print">{{ $message }}</p> @enderror
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
                                        <span class="inline-block px-1.5 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-600 border border-slate-200 min-w-[35px]">
                                            {{ $items[$index]['unit'] ?: '-' }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" wire:model.live="items.{{ $index }}.batch_number" 
                                               class="w-full rounded-lg text-[10px] border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition mb-1" placeholder="Mã Code NCC...">
                                        <input type="date" wire:model="items.{{ $index }}.expiry_date" 
                                               class="w-full rounded-lg border-slate-300 text-[9px] focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" wire:model="items.{{ $index }}.warehouse_location" list="location_list_{{ $index }}"
                                               class="w-full text-[10px] rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0" placeholder="Vị trí...">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="number" wire:model.live="items.{{ $index }}.unit_price" step="1" min="0"
                                               class="w-full text-right text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="number" wire:model.live="items.{{ $index }}.vat_rate" step="0.1" min="0"
                                               class="w-full text-center text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-1.5 text-right font-black text-indigo-700 text-xs">
                                        {{ number_format($items[$index]['total_amount'] ?? 0) }} đ
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
                                Thêm dòng sản phẩm
                            </button>
                        @endif
                    </div>

                    <div class="mb-4 p-3 bg-slate-50 rounded-xl border border-slate-200 no-print">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1 px-1 flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Ghi chú phiếu xuất
                        </label>
                        <textarea wire:model="note" rows="1" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Nhập lý do xuất, thông tin vận chuyển..."></textarea>
                    </div>

                    <div class="flex justify-end items-center gap-4 no-print mt-2">
                        <a href="{{ route('warehouse.inventory') }}" class="px-6 py-2 border border-slate-300 rounded-xl text-slate-600 text-sm font-semibold hover:bg-slate-50 transition duration-150">
                            Hủy bỏ
                        </a>
                        <button wire:click="save" class="bg-indigo-600 text-white px-8 py-2 rounded-xl text-sm font-black hover:bg-indigo-700 transition duration-150 shadow-md flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Xác nhận xuất kho
                        </button>
                    </div>

                </div>
            </div>

            <!-- PHẦN IN PDF BỊ ẨN KHI XEM THƯỜNG -->
            <div class="hidden print-only print-container inset-0 bg-white w-full text-black" style="font-family: 'Times New Roman', serif; padding: 15mm;">
                <!-- 1/5 Header: Company Info -->
                <div class="mb-2">
                    <h1 class="text-xl font-bold uppercase">CÔNG TY TNHH ABC</h1>
                    <p class="text-[13px]">Địa chỉ: 123 Long An</p>
                    <p class="text-[13px]">Điện thoại: 0708091050</p>
                </div>
                
                <div class="text-center mb-4 mt-2">
                    <h2 class="text-3xl font-bold uppercase tracking-widest text-slate-900">PHIẾU XUẤT KHO</h2>
                    <p class="italic text-[13px] mt-1">Ngày {{ date('d') }} tháng {{ date('m') }} năm {{ date('Y') }}</p>
                    <p class="text-[13px] font-bold">Số: SO-{{ date('Ymd') }}-XX</p>
                </div>
                <div style="border-bottom: 2px solid #000; margin-bottom: 12px;"></div>

                <!-- 1/5 Customer Info -->
                <div style="margin-bottom: 20px;">
                    <table class="w-full text-sm">
                        <tr>
                            <td class="font-bold w-48">Khách hàng / Đơn vị nhận:</td>
                            <td>{{ $customer_name ?: '..........................................................' }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Người liên hệ:</td>
                            <td>{{ $receiver_name ?: '..........................................................' }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Mã tài sản:</td>
                            <td>{{ $asset_code ?: '..........................................................' }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Địa chỉ:</td>
                            <td>{{ $customer_details['address'] ?: '..........................................................' }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Điện thoại:</td>
                            <td>{{ $customer_details['phone'] ?: '..........................................................' }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Lý do xuất:</td>
                            <td>{{ $note ?: '..........................................................' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- 3/5 Items Table -->
                <div>
                    <table class="w-full border-collapse border border-slate-800 text-[12px] mb-2 page-break-inside-avoid">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-slate-800 px-1 py-1.5">STT</th>
                                <th class="border border-slate-800 px-1 py-1.5 text-left">Mã & Tên SP</th>
                                <th class="border border-slate-800 px-1 py-1.5 text-center">ĐVT</th>
                                <th class="border border-slate-800 px-1 py-1.5 text-center">Mã Code NCC</th>
                                <th class="border border-slate-800 px-1 py-1.5 text-center">Hạn dùng</th>
                                <th class="border border-slate-800 px-1 py-1.5 text-center">Số lượng</th>
                                <th class="border border-slate-800 px-1 py-1.5 text-right">Đơn giá</th>
                                <th class="border border-slate-800 px-1 py-1.5 text-center">VAT(%)</th>
                                <th class="border border-slate-800 px-1 py-1.5 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; $validCount = 0; @endphp
                            @foreach($items as $index => $item)
                                @if($item['product_id'] && $item['is_printed'])
                                    @php 
                                        $itemTotal = floatval($item['total_amount'] ?? 0);
                                        $grandTotal += $itemTotal; 
                                        $validCount++;
                                    @endphp
                                    <tr>
                                        <td class="border border-slate-800 px-1 py-1.5 text-center">{{ $validCount }}</td>
                                        <td class="border border-slate-800 px-1 py-1.5 font-bold uppercase">{{ $item['product_search'] }}</td>
                                        <td class="border border-slate-800 px-1 py-1.5 text-center">{{ $item['unit'] }}</td>
                                        <td class="border border-slate-800 px-1 py-1.5 text-center">{{ $item['batch_number'] }}</td>
                                        <td class="border border-slate-800 px-1 py-1.5 text-center">{{ $item['expiry_date'] ? date('d/m/Y', strtotime($item['expiry_date'])) : '' }}</td>
                                        <td class="border border-slate-800 px-1 py-1.5 text-center">{{ (float)$item['quantity'] }}</td>
                                        <td class="border border-slate-800 px-1 py-1.5 text-right">{{ number_format(floatval($item['unit_price'])) }}</td>
                                        <td class="border border-slate-800 px-1 py-1.5 text-center">{{ floatval($item['vat_rate']) }}%</td>
                                        <td class="border border-slate-800 px-1 py-1.5 text-right font-bold">{{ number_format($itemTotal) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            @for($i = $validCount; $i < 10; $i++)
                                <tr>
                                    <td class="border border-slate-800 px-1 py-1.5 text-center text-transparent">_</td>
                                    <td class="border border-slate-800 px-1 py-1.5"></td>
                                    <td class="border border-slate-800 px-1 py-1.5"></td>
                                    <td class="border border-slate-800 px-1 py-1.5"></td>
                                    <td class="border border-slate-800 px-1 py-1.5"></td>
                                    <td class="border border-slate-800 px-1 py-1.5"></td>
                                    <td class="border border-slate-800 px-1 py-1.5"></td>
                                    <td class="border border-slate-800 px-1 py-1.5"></td>
                                    <td class="border border-slate-800 px-1 py-1.5"></td>
                                </tr>
                            @endfor
                            <tr>
                                <td colspan="8" class="border border-slate-800 px-1 py-1.5 text-right font-bold uppercase">Tổng cộng:</td>
                                <td class="border border-slate-800 px-1 py-1.5 text-right font-bold text-[14px]">{{ number_format($grandTotal) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mb-2 mt-2">
                        <p class="italic text-sm">Số tiền viết bằng chữ: <strong>{{ app(\App\Livewire\Warehouse\StockOutForm::class)->numberToWords($grandTotal) }}</strong></p>
                    </div>

                    <div class="grid grid-cols-4 gap-4 text-center mt-4">
                        <div>
                            <p class="font-bold text-sm">Khách hàng nhận</p>
                            <p class="text-xs italic">(Ký, ghi rõ họ tên)</p>
                            <div style="height: 80px;"></div>
                            <p class="font-bold uppercase text-xs">{{ current((array)($customer_details['contact_person'] ?? '..........')) ?: '........................' }}</p>
                        </div>
                        <div>
                            <p class="font-bold text-sm">Nhân viên giao hàng</p>
                            <p class="text-xs italic">(Ký, ghi rõ họ tên)</p>
                            <div style="height: 80px;"></div>
                            <p class="font-bold uppercase text-xs">........................</p>
                        </div>
                        <div>
                            <p class="font-bold text-sm">Kế toán</p>
                            <p class="text-xs italic">(Ký, ghi rõ họ tên)</p>
                            <div style="height: 80px;"></div>
                            <p class="font-bold uppercase text-xs">........................</p>
                        </div>
                        <div>
                            <p class="font-bold text-sm">Quản lý</p>
                            <p class="text-xs italic">(Ký, ghi rõ họ tên)</p>
                            <div style="height: 80px;"></div>
                            <p class="font-bold uppercase text-xs">........................</p>
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

                    <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 no-print">
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
                                    <th class="px-6 py-4 w-10 no-print text-center">
                                        <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $idsOnPage) }}])" {{ count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-2 py-4">MÃ PHIẾU</th>
                                    <th class="px-6 py-4">NGÀY TẠO</th>
                                    <th class="px-6 py-4">KHÁCH HÀNG / BỘ PHẬN</th>
                                    <th class="px-6 py-4">NGƯỜI LIÊN HỆ / MÃ TS</th>
                                    <th class="px-6 py-4">LOẠI XUẤT</th>
                                    <th class="px-6 py-4 text-right">TỔNG TIỀN</th>
                                    <th class="px-6 py-4">GHI CHÚ</th>
                                    <th class="px-6 py-4 text-center no-print">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($stockOuts as $so)
                                    <tr class="hover:bg-indigo-50/30 transition-all group {{ in_array($so->id, $selectedIds) ? 'bg-indigo-50' : '' }}">
                                        <td class="px-6 py-4 no-print text-center">
                                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $so->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-2 py-4 font-black text-indigo-700 tracking-tight">{{ $so->code }}</td>
                                        <td class="px-6 py-4 text-slate-500 text-[12px] font-bold">{{ $so->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 font-black text-slate-800 text-[13px] uppercase tracking-tighter">{{ $so->customer_name ?: '-' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="text-[12px] font-bold text-slate-700 uppercase">{{ $so->receiver_name ?: '-' }}</div>
                                            <div class="text-[10px] font-black text-indigo-600">{{ $so->asset_code }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @switch($so->type)
                                                @case('repair') <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[10px] font-black uppercase border border-blue-100">🛠️ SỬA CHỮA</span> @break
                                                @case('delivery') <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-black uppercase border border-emerald-100">🚚 GIAO HÀNG</span> @break
                                                @case('disposal') <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-[10px] font-black uppercase border border-red-100">🗑️ HỦY</span> @break
                                                @default <span class="px-2.5 py-1 bg-slate-50 text-slate-600 rounded-lg text-[10px] font-black uppercase border border-slate-100">KHÁC</span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 text-right font-black text-slate-900 text-[14px]">
                                            {{ number_format($so->items->sum('total_amount')) }} đ
                                        </td>
                                        <td class="px-6 py-4 text-slate-400 text-[11px] font-bold italic truncate max-w-[150px]" title="{{ $so->note }}">{{ $so->note ?: '-' }}</td>
                                        <td class="px-6 py-4 text-center no-print">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="printSingle({{ $so->id }})" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="In phiếu này">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
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
                                                <p class="text-sm font-bold">Không tìm thấy phiếu xuất nào trong khoảng thời gian này</p>
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

    <!-- PHẦN IN CHI TIẾT HÀNG LOẠT (Nhanh/Ghép) -->
    @if(count($printItems) > 0)
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        @foreach($printItems as $pItem)
        <div class="print-page p-8 bg-white" style="font-family: 'Times New Roman', serif; min-height: 297mm; page-break-after: always;">
            <!-- Header -->
            <div class="flex justify-between items-start mb-6 border-b-2 border-slate-900 pb-4">
                <div>
                    <h1 class="text-xl font-black uppercase tracking-tighter">CÔNG TY TNHH SANE</h1>
                    <p class="text-xs font-bold text-slate-600">Lô B2, KCN Đức Hòa 1, Long An</p>
                    <p class="text-xs font-bold text-slate-600">Hotline: 0909.XXX.XXX</p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-black text-slate-900 uppercase">PHIẾU XUẤT KHO</h2>
                    <p class="text-sm font-bold mt-1">Số: <span class="text-indigo-700">{{ $pItem->code }}</span></p>
                    <p class="text-[10px] italic text-slate-500">In lúc: {{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <!-- Info -->
            <div class="grid grid-cols-2 gap-8 mb-8">
                <div class="space-y-1">
                    <p class="text-sm"><span class="font-bold uppercase text-[10px] text-slate-400 block tracking-widest">Đơn vị nhận hàng</span> 
                       <span class="font-black text-slate-800 text-lg uppercase">{{ $pItem->customer_name }}</span>
                    </p>
                    <p class="text-xs"><span class="font-bold">Người nhận:</span> {{ $pItem->receiver_name }}</p>
                    <p class="text-xs"><span class="font-bold">Mã tài sản:</span> {{ $pItem->asset_code }}</p>
                    <p class="text-xs"><span class="font-bold">Lý do:</span> {{ $pItem->note ?: 'Xuất vật tư sản xuất/giao hàng' }}</p>
                </div>
                <div class="space-y-1 text-right">
                    <p class="text-sm"><span class="font-bold uppercase text-[10px] text-slate-400 block tracking-widest">Ngày chứng từ</span> 
                       <span class="font-black text-slate-800">{{ $pItem->created_at->format('d/m/Y') }}</span>
                    </p>
                    <p class="text-xs"><span class="font-bold">Người lập:</span> {{ $pItem->creator->name ?? 'Admin' }}</p>
                </div>
            </div>

            <!-- Table -->
            <table class="w-full border-collapse border-2 border-slate-900 mb-6">
                <thead>
                    <tr class="bg-slate-100 uppercase text-[10px] font-black">
                        <th class="border border-slate-900 px-2 py-2 text-center w-10">STT</th>
                        <th class="border border-slate-900 px-2 py-2 text-left">Sản phẩm / Vật tư</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-16">Mã Code NCC</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-16">ĐVT</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-20">Số lượng</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-24">Đơn giá</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-24">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @foreach($pItem->items as $idx => $ii)
                    <tr>
                        <td class="border border-slate-900 px-2 py-2 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-slate-900 px-2 py-2 font-bold">{{ $ii->product->name }} ({{ $ii->product->code }})</td>
                        <td class="border border-slate-900 px-2 py-2 text-center">{{ $ii->batch_number ?: '-' }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-center">{{ $ii->product->unit }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-right font-bold">{{ number_format($ii->quantity) }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-right">{{ number_format($ii->unit_price) }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-right font-black">{{ number_format($ii->total_amount) }}</td>
                    </tr>
                    @endforeach
                    @for($i = count($pItem->items); $i < 5; $i++)
                    <tr>
                        <td class="border border-slate-900 px-2 py-2 text-center text-transparent">_</td>
                        <td class="border border-slate-900 px-2 py-2"></td>
                        <td class="border border-slate-900 px-2 py-2"></td>
                        <td class="border border-slate-900 px-2 py-2"></td>
                        <td class="border border-slate-900 px-2 py-2"></td>
                        <td class="border border-slate-900 px-2 py-2"></td>
                        <td class="border border-slate-900 px-2 py-2"></td>
                    </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-black">
                        <td colspan="6" class="border border-slate-900 px-2 py-2 text-right uppercase">Tổng cộng:</td>
                        <td class="border border-slate-900 px-2 py-2 text-right text-sm">{{ number_format($pItem->items->sum('total_amount')) }} đ</td>
                    </tr>
                </tfoot>
            </table>

            <!-- Footer signatures -->
            <div class="grid grid-cols-4 gap-4 text-center mt-8">
                <div>
                    <p class="font-bold text-xs uppercase">Người nhận</p>
                    <p class="text-[9px] italic">(Ký, ghi rõ họ tên)</p>
                </div>
                <div>
                    <p class="font-bold text-xs uppercase">Người giao</p>
                    <p class="text-[9px] italic">(Ký, ghi rõ họ tên)</p>
                </div>
                <div>
                    <p class="font-bold text-xs uppercase">Kế toán</p>
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
    </script>
    @endscript
</div>
