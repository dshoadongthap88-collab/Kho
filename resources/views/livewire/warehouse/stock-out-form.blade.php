<div>
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

    <div class="relative flex items-start gap-6">
        <!-- Sidebar Toolbar (Left) -->
        <div class="sidebar-toolbar sticky top-24 hidden md:flex flex-col gap-3 no-print">
            <button onclick="window.print()" class="group flex flex-col items-center justify-center w-16 h-16 bg-white border border-slate-200 rounded-xl shadow-sm hover:bg-indigo-50 hover:border-indigo-200 transition-all duration-200" title="In phiếu xuất">
                <svg class="w-7 h-7 text-slate-600 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span class="text-[10px] font-bold text-slate-500 uppercase mt-1 group-hover:text-indigo-600">In phiếu</span>
            </button>

            <a href="{{ route('warehouse.inventory') }}" class="group flex flex-col items-center justify-center w-16 h-16 bg-white border border-slate-200 rounded-xl shadow-sm hover:bg-slate-50 transition-all duration-200" title="Quay lại">
                <svg class="w-6 h-6 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="text-[10px] font-bold text-slate-400 uppercase mt-1 group-hover:text-slate-600">Thoát</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="flex-1 main-content">
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

            <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden printable-area no-print">
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="text-2xl">📤</span> Phiếu xuất kho
                    </h2>
                    <div class="hidden print-only text-right">
                        <p class="text-sm font-bold text-slate-600">Số phiếu: SO-{{ date('Ymd') }}-XXXX</p>
                        <p class="text-xs text-slate-400">Ngày in: {{ date('d/m/Y H:i') }}</p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Khách hàng / Bộ phận nhận</label>
                            <input type="text" wire:model="customer_name" list="customers_list" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition" placeholder="Chọn hoặc nhập tên...">
                            <datalist id="customers_list">
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->name }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Phòng ban (nếu có)</label>
                            <input type="text" wire:model="receiver_department" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition" placeholder="Ví dụ: Phòng Kế hoạch, Xưởng 1...">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Loại xuất</label>
                            <select wire:model="type" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                <option value="production">Xuất cho sản xuất</option>
                                <option value="delivery">Xuất giao khách hàng</option>
                                <option value="disposal">Xuất hủy</option>
                                <option value="manual">Xuất khác / Thủ công</option>
                            </select>
                        </div>
                    </div>

                    @if($type === 'production')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 p-4 bg-indigo-50 border border-indigo-100 rounded-xl no-print">
                        <div>
                            <label class="block text-sm font-semibold text-indigo-900 mb-2">Thành phẩm cần sản xuất</label>
                            <select wire:model.live="production_product_id" class="w-full rounded-lg border-indigo-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                                <option value="">-- Chọn thành phẩm --</option>
                                @foreach($productionProducts as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->code }} - {{ $prod->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-indigo-900 mb-2">Số lượng sản xuất</label>
                            <input type="number" wire:model.live="production_quantity" step="0.01" min="0.01" class="w-full rounded-lg border-indigo-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition">
                        </div>
                    </div>
                    @endif

                    <div class="overflow-x-auto mb-6">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="px-2 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-10 no-print">In</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 min-w-[250px]">Sản phẩm {{ $type === 'production' ? '(Nguyên liệu)' : '' }}</th>
                                    @if($type === 'production')
                                    <th class="px-2 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-20">Hãng SX</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-24">Tồn kho HT</th>
                                    @endif
                                    <th class="px-2 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-14">ĐVT</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-20">Số lô</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-24">Hạn dùng</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-20">Vị trí</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-20">SL Xuất</th>
                                    <th class="px-2 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-24">Đơn giá</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-14">VAT</th>
                                    <th class="px-2 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 w-28">Thành tiền</th>
                                    <th class="px-2 py-3 border-b border-slate-200 w-10 no-print"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 no-print">
                                @foreach($items as $index => $item)
                                <tr wire:key="item-{{ $index }}" class="hover:bg-slate-50/50 transition duration-150 {{ !$item['is_printed'] ? 'no-print' : '' }}">
                                    <td class="px-3 py-4 text-center no-print">
                                        <input type="checkbox" wire:model="items.{{ $index }}.is_printed" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model.live.debounce.250ms="items.{{ $index }}.product_search" list="product_list_{{ $index }}" 
                                               class="w-full rounded-lg border-slate-300 text-xs font-semibold focus:ring-indigo-500 focus:border-indigo-500 transition placeholder:font-normal {{ $type === 'production' ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                               placeholder="Mã hoặc tên SP..." {{ $type === 'production' ? 'readonly' : '' }}>
                                        <datalist id="product_list_{{ $index }}">
                                            @foreach($products as $product)
                                                <option value="{{ $product->code }} - {{ $product->name }}"></option>
                                            @endforeach
                                        </datalist>
                                        @if($type !== 'production' && isset($items[$index]['brand']) && $items[$index]['brand'])
                                            <p class="text-[9px] text-slate-400 mt-0.5 uppercase font-bold tracking-tight px-1 no-print">Hãng: {{ $items[$index]['brand'] }}</p>
                                        @endif
                                        @error("items.{$index}.product_id") <p class="text-red-500 text-[10px] mt-1 no-print">{{ $message }}</p> @enderror
                                    </td>
                                    @if($type === 'production')
                                    <td class="px-2 py-4 text-center">
                                        <span class="text-xs font-semibold text-slate-600 uppercase">{{ $items[$index]['brand'] ?? '-' }}</span>
                                    </td>
                                    <td class="px-2 py-4 text-center">
                                        @if(isset($items[$index]['available_qty']))
                                            <div class="text-xs no-print whitespace-nowrap">
                                                <span class="font-bold">{{ floatval($items[$index]['available_qty']) }}</span>
                                                @if($items[$index]['is_sufficient'])
                                                    <span class="text-green-600 font-bold block text-[10px] mt-0.5">🟢 Đủ</span>
                                                @else
                                                    <span class="text-red-500 font-bold block text-[10px] mt-0.5">🔴 Thiếu</span>
                                                @endif
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @endif
                                    <td class="px-1 py-4 text-center">
                                        <span class="inline-block px-1.5 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-600 border border-slate-200 min-w-[35px]">
                                            {{ $items[$index]['unit'] ?: '-' }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model.live="items.{{ $index }}.batch_number" 
                                               class="w-full rounded-lg text-xs border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0" placeholder="Số lô...">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="date" wire:model="items.{{ $index }}.expiry_date" 
                                               class="w-full rounded-lg border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model="items.{{ $index }}.warehouse_location" list="location_list_{{ $index }}"
                                               class="w-full text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0" placeholder="Vị trí...">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="number" wire:model.live="items.{{ $index }}.quantity" step="0.0001" min="0" {{ $type === 'production' ? 'readonly' : '' }}
                                               class="w-full text-center text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0 {{ $type === 'production' ? 'bg-slate-100 cursor-not-allowed' : '' }}">
                                        @error("items.{$index}.quantity") <p class="text-red-500 text-[10px] mt-1 no-print">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="number" wire:model.live="items.{{ $index }}.unit_price" step="0.01" min="0"
                                               class="w-full text-right text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="number" wire:model.live="items.{{ $index }}.vat_rate" step="0.1" min="0"
                                               class="w-full text-center text-xs rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-4 text-right font-bold text-indigo-700">
                                        {{ number_format($items[$index]['total_amount'] ?? 0) }} đ
                                    </td>
                                    <td class="px-2 py-4 text-center no-print">
                                        @if(count($items) > 1)
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

                    <div class="mb-8 p-4 bg-slate-50 rounded-xl border border-slate-200 no-print">
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Ghi chú phiếu xuất
                        </label>
                        <textarea wire:model="note" rows="3" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition print:border-none print:p-0" placeholder="Nhập lý do xuất, thông tin vận chuyển..."></textarea>
                    </div>

                    <div class="flex justify-end items-center gap-4 no-print">
                        <a href="{{ route('warehouse.inventory') }}" class="px-6 py-2.5 border border-slate-300 rounded-xl text-slate-600 font-semibold hover:bg-slate-50 transition duration-150 shadow-sm">
                            Hủy bỏ
                        </a>
                        <button wire:click="save" class="bg-indigo-600 text-white px-8 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition duration-150 shadow-md flex items-center gap-2">
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
                            <td class="font-bold">Địa chỉ:</td>
                            <td>{{ $customer_details['address'] ?: '..........................................................' }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Điện thoại:</td>
                            <td>{{ $customer_details['phone'] ?: '..........................................................' }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Email:</td>
                            <td>{{ $customer_details['email'] ?: '..........................................................' }}</td>
                        </tr>
                        <tr>
                            <td class="font-bold">Người liên hệ:</td>
                            <td>{{ $customer_details['contact_person'] ?: '..........................................................' }}</td>
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
                                <th class="border border-slate-800 px-1 py-1.5 text-center">Số lô</th>
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
            </div>
        </div>
    </div>

    <!-- Batch Selection Modal -->
    @if($showBatchModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-[100] no-print p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full overflow-hidden border border-slate-200 animate-in fade-in zoom-in duration-200">
                <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        Chọn lô hàng còn tồn trong kho
                    </h3>
                    <button wire:click="closeBatchModal" class="text-indigo-100 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-6">
                    <p class="mb-4 text-sm text-slate-500">
                        Hệ thống tìm thấy <strong>{{ count($availableBatches) }} lô hàng</strong> của sản phẩm này. Vui lòng chọn một lô để xuất kho:
                    </p>
                    
                    <div class="overflow-x-auto border border-slate-100 rounded-xl">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-100">
                                    <th class="px-4 py-3 text-left">Số lô (Batch)</th>
                                    <th class="px-4 py-3 text-left">Hạn sử dụng</th>
                                    <th class="px-4 py-3 text-left">Vị trí kho</th>
                                    <th class="px-4 py-3 text-right">Tồn khả dụng</th>
                                    <th class="px-4 py-3 text-center w-24">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($availableBatches as $index => $batch)
                                    <tr class="hover:bg-indigo-50/30 transition duration-150">
                                        <td class="px-4 py-4 font-semibold text-slate-700">{{ $batch['batch_number'] ?: 'N/A' }}</td>
                                        <td class="px-4 py-4 text-slate-600">
                                            @if($batch['expiry_date'])
                                                <span class="{{ \Carbon\Carbon::parse($batch['expiry_date'])->isPast() ? 'text-red-500 font-bold' : '' }}">
                                                    {{ \Carbon\Carbon::parse($batch['expiry_date'])->format('d/m/Y') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-slate-600">
                                            <span class="px-2 py-1 bg-slate-100 rounded text-xs">{{ $batch['warehouse_location'] ?: 'Chưa rõ' }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <span class="font-bold text-indigo-600">{{ number_format($batch['stock'], 4) }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <button wire:click="selectBatch({{ $index }})" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-xs font-bold hover:bg-indigo-700 transition">
                                                Chọn lô
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 italic">Không tìm thấy lô hàng nào còn tồn kho.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-100">
                    <button wire:click="closeBatchModal" class="px-4 py-2 border border-slate-300 rounded-xl text-slate-600 text-sm font-semibold hover:bg-white transition shadow-sm">
                        Đóng cửa sổ
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
