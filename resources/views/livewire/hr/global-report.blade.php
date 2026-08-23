<div class="px-4 py-6" x-data="{ tab: @entangle('activeTab'), selectedWarningLow: [], selectedWarningHigh: [], selectedRows: [] }">
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-end gap-4 print:hidden">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Báo Cáo Tổng Hợp (HR)</h1>
            <p class="text-slate-500 mt-2 text-sm">Thống kê số liệu, cảnh báo tồn kho và chi tiết xuất kho toàn hệ thống</p>
        </div>
        
        <!-- Tabs Navigation -->
        <div class="bg-slate-100 p-1 rounded-lg flex space-x-1 overflow-x-auto">
            <button wire:click="setTab('overview')" :class="{ 'bg-white shadow text-indigo-600': tab === 'overview', 'text-slate-600 hover:text-slate-800': tab !== 'overview' }" class="px-4 py-2 text-sm font-semibold rounded-md transition-all whitespace-nowrap">
                Tổng Quan
            </button>
            <button wire:click="setTab('inventory_warnings')" :class="{ 'bg-white shadow text-indigo-600': tab === 'inventory_warnings', 'text-slate-600 hover:text-slate-800': tab !== 'inventory_warnings' }" class="px-4 py-2 text-sm font-semibold rounded-md transition-all whitespace-nowrap">
                Cảnh Báo Tồn Kho
            </button>
            <button wire:click="setTab('stock_out_details')" :class="{ 'bg-white shadow text-indigo-600': tab === 'stock_out_details', 'text-slate-600 hover:text-slate-800': tab !== 'stock_out_details' }" class="px-4 py-2 text-sm font-semibold rounded-md transition-all whitespace-nowrap">
                Báo Cáo Xuất Kho
            </button>
        </div>
    </div>

    <!-- TAB 1: TỔNG QUAN -->
    <div x-show="tab === 'overview'" class="print:hidden">
        <!-- Overview Filters (Compact) -->
        <div class="filter-grid mb-3">
            <div class="filter-field filter-wide flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg shadow-sm border border-slate-200">
                <label class="text-xs font-bold text-slate-500 uppercase">Từ ngày:</label>
                <input type="date" wire:model.live="overviewStartDate" class="border-none bg-transparent p-0 text-sm focus:ring-0 font-medium text-slate-700 w-32">
                <span class="text-slate-300">|</span>
                <label class="text-xs font-bold text-slate-500 uppercase">Đến ngày:</label>
                <input type="date" wire:model.live="overviewEndDate" class="border-none bg-transparent p-0 text-sm focus:ring-0 font-medium text-slate-700 w-32">
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Tổng Số Dự Án</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $projects->count() }}</h3>
                </div>
                <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-lg">🏢</div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Tổng Số Nhân Sự</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $totalUsers }}</h3>
                </div>
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-lg">👥</div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Tổng Số Đơn Xuất</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalOrdersAllProjects) }}</h3>
                </div>
                <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center text-lg">📄</div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Tổng SL Vật Tư Xuất</p>
                    <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalItemsAllProjects) }}</h3>
                </div>
                <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center text-lg">🔧</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4" wire:ignore>
            <!-- Biểu đồ Đường -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-3">
                <h3 class="text-sm font-bold text-slate-800 mb-2">Biến Động Đơn Xuất</h3>
                <div id="chart-orders-timeline" class="w-full h-[260px]"></div>
            </div>
            
            <!-- Biểu đồ Tròn -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-3">
                <h3 class="text-sm font-bold text-slate-800 mb-2">Tỷ Trọng SL Vật Tư / Dự Án</h3>
                <div id="chart-items-donut" class="w-full h-[260px] flex items-center justify-center"></div>
            </div>

            <!-- Biểu đồ Cột -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-3">
                <h3 class="text-sm font-bold text-slate-800 mb-2">So Sánh Tổng Số Đơn Xuất</h3>
                <div id="chart-orders-bar" class="w-full h-[260px]"></div>
            </div>
        </div>
    </div>

    <!-- TAB 2: CẢNH BÁO TỒN KHO -->
    <div x-show="tab === 'inventory_warnings'" style="display: none;" class="print-section">
        <!-- Filters (Hidden in print) -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-wrap gap-4 items-end print:hidden">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kho Dự Án</label>
                <select wire:model.live="warningProject" class="border-slate-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Tất cả các kho --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>

        </div>

        <!-- UI Cards for Screen Only -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 print:hidden">
            <!-- Sắp hết hàng -->
            <div class="bg-amber-50 rounded-xl shadow-sm border border-amber-200 overflow-hidden transition-opacity">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-amber-800 uppercase flex items-center gap-2">
                            <span>⚠️</span> Cảnh báo sắp hết hàng (< Tồn tối thiểu)
                        </h3>
                        <button @click="window.print()" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white text-[10px] font-bold rounded shadow-sm transition flex items-center gap-1">
                            <span>🖨️</span> In báo cáo
                        </button>
                    </div>
                    
                    @if(count($lowStockProducts ?? []) > 0)
                        <div class="mb-3 px-2 flex items-center gap-2 border-b border-amber-200 pb-3">
                            <input type="checkbox" @change="selectedWarningLow = $event.target.checked ? Array.from(document.querySelectorAll('.row-cb-low')).map(cb => cb.value) : []" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-sm font-bold text-amber-800">Chọn tất cả</span>
                        </div>
                        <ul class="text-sm text-amber-900 space-y-2 max-h-[500px] overflow-y-auto px-2">
                            @foreach($lowStockProducts ?? [] as $index => $item)
                                @php
                                    $missingQty = $item->min_stock - $item->quantity;
                                @endphp
                                <li class="flex items-start gap-3 hover:bg-amber-100 p-2 rounded transition-colors">
                                    <input type="checkbox" value="{{ $index }}" x-model="selectedWarningLow" class="row-cb-low mt-1 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                    <div>
                                        <b class="text-base">{{ $item->name }}</b> <span class="text-gray-600">({{ $item->code }})</span>
                                        <span class="text-xs text-amber-700 ml-2">ĐVT: {{ $item->unit ?? '-' }}</span><br>
                                        <span class="text-sm text-amber-800 mt-1 inline-block">
                                            Tồn kho: <b class="text-amber-900">{{ number_format($item->quantity) }}</b> | 
                                            Tồn min: {{ number_format($item->min_stock) }} | 
                                            Thiếu: <b class="text-red-600">{{ number_format($missingQty > 0 ? $missingQty : 0) }}</b> | 
                                            Dự Án: <b>{{ $item->project_name }}</b>
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-amber-700 italic p-2">Không có vật tư nào dưới định mức tồn kho tối thiểu.</p>
                    @endif
                </div>
            </div>

            <!-- Dư thừa -->
            <div class="bg-rose-50 rounded-xl shadow-sm border border-rose-200 overflow-hidden transition-opacity">
                <div class="p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-rose-800 uppercase flex items-center gap-2">
                            <span>🚨</span> Cảnh báo hàng dư thừa (> Tồn tối đa)
                        </h3>
                        <button @click="window.print()" class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold rounded shadow-sm transition flex items-center gap-1">
                            <span>🖨️</span> In báo cáo
                        </button>
                    </div>
                    
                    @if(count($highStockProducts ?? []) > 0)
                        <div class="mb-3 px-2 flex items-center gap-2 border-b border-rose-200 pb-3">
                            <input type="checkbox" @change="selectedWarningHigh = $event.target.checked ? Array.from(document.querySelectorAll('.row-cb-high')).map(cb => cb.value) : []" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                            <span class="text-sm font-bold text-rose-800">Chọn tất cả</span>
                        </div>
                        <ul class="text-sm text-rose-900 space-y-2 max-h-[500px] overflow-y-auto px-2">
                            @foreach($highStockProducts ?? [] as $index => $item)
                                @php
                                    $excessQty = $item->quantity - $item->max_stock;
                                @endphp
                                <li class="flex items-start gap-3 hover:bg-rose-100 p-2 rounded transition-colors">
                                    <input type="checkbox" value="{{ $index }}" x-model="selectedWarningHigh" class="row-cb-high mt-1 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <b class="text-base">{{ $item->name }}</b> <span class="text-gray-600">({{ $item->code }})</span>
                                        <span class="text-xs text-rose-700 ml-2">ĐVT: {{ $item->unit ?? '-' }}</span><br>
                                        <span class="text-sm text-rose-800 mt-1 inline-block">
                                            Tồn kho: <b class="text-rose-900">{{ number_format($item->quantity) }}</b> | 
                                            Tồn max: {{ number_format($item->max_stock) }} | 
                                            Dư: <b class="text-red-600">{{ number_format($excessQty > 0 ? $excessQty : 0) }}</b> | 
                                            Dự Án: <b>{{ $item->project_name }}</b>
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-rose-700 italic p-2">Không có vật tư nào vượt quá định mức tồn kho tối đa.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Print Layout (Table Form) -->
        <div class="hidden print:block w-full text-black">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-black uppercase">PHIẾU CẢNH BÁO TỒN KHO</h2>
                <p class="mt-1 text-sm">Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
            </div>

            <!-- Table for Low Stock -->
            <div :class="{ 'hidden': selectedWarningHigh.length > 0 && selectedWarningLow.length === 0 }" class="mb-4">
                <p class="font-bold text-sm italic mb-1">Dự Án: {{ $warningProject ? $projects->where('id', $warningProject)->first()->name : 'Tất cả các kho' }}</p>
                <h3 class="font-bold uppercase mb-2 text-sm">I. Danh sách vật tư sắp hết hàng (< Tồn tối thiểu)</h3>
                @if(count($lowStockProducts ?? []) > 0)
                    <table class="w-full text-sm border-collapse border border-gray-400">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-400 p-1 text-center w-10">STT</th>
                                <th class="border border-gray-400 p-1 text-center w-28">Mã vật tư</th>
                                <th class="border border-gray-400 p-1 text-left">Tên vật tư</th>
                                <th class="border border-gray-400 p-1 text-center w-16">ĐVT</th>
                                <th class="border border-gray-400 p-1 text-right w-24">Số lượng tồn</th>
                                <th class="border border-gray-400 p-1 text-right w-24">Số lượng thiếu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockProducts ?? [] as $index => $item)
                                @php
                                    $missingQty = $item->min_stock - $item->quantity;
                                @endphp
                                <tr :class="{ 'hidden': selectedWarningLow.length > 0 && !selectedWarningLow.includes('{{ $index }}') }">
                                    <td class="border border-gray-400 p-1 text-center">{{ $index + 1 }}</td>
                                    <td class="border border-gray-400 p-1 text-center">{{ $item->code }}</td>
                                    <td class="border border-gray-400 p-1">{{ $item->name }}</td>
                                    <td class="border border-gray-400 p-1 text-center">{{ $item->unit ?? '-' }}</td>
                                    <td class="border border-gray-400 p-1 text-right font-bold">{{ number_format($item->quantity) }}</td>
                                    <td class="border border-gray-400 p-1 text-right font-bold text-red-600">{{ number_format($missingQty > 0 ? $missingQty : 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="italic text-sm">Không có dữ liệu.</p>
                @endif
            </div>

            <!-- Table for High Stock -->
            <div :class="{ 'hidden': selectedWarningLow.length > 0 && selectedWarningHigh.length === 0 }" class="mb-4 mt-6">
                <p class="font-bold text-sm italic mb-1">Dự Án: {{ $warningProject ? $projects->where('id', $warningProject)->first()->name : 'Tất cả các kho' }}</p>
                <h3 class="font-bold uppercase mb-2 text-sm">II. Danh sách vật tư dư thừa (> Tồn tối đa)</h3>
                @if(count($highStockProducts ?? []) > 0)
                    <table class="w-full text-sm border-collapse border border-gray-400">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-400 p-1 text-center w-10">STT</th>
                                <th class="border border-gray-400 p-1 text-center w-28">Mã vật tư</th>
                                <th class="border border-gray-400 p-1 text-left">Tên vật tư</th>
                                <th class="border border-gray-400 p-1 text-center w-16">ĐVT</th>
                                <th class="border border-gray-400 p-1 text-right w-24">Số lượng tồn</th>
                                <th class="border border-gray-400 p-1 text-right w-24">Số lượng dư</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($highStockProducts ?? [] as $index => $item)
                                @php
                                    $excessQty = $item->quantity - $item->max_stock;
                                @endphp
                                <tr :class="{ 'hidden': selectedWarningHigh.length > 0 && !selectedWarningHigh.includes('{{ $index }}') }">
                                    <td class="border border-gray-400 p-1 text-center">{{ $index + 1 }}</td>
                                    <td class="border border-gray-400 p-1 text-center">{{ $item->code }}</td>
                                    <td class="border border-gray-400 p-1">{{ $item->name }}</td>
                                    <td class="border border-gray-400 p-1 text-center">{{ $item->unit ?? '-' }}</td>
                                    <td class="border border-gray-400 p-1 text-right font-bold">{{ number_format($item->quantity) }}</td>
                                    <td class="border border-gray-400 p-1 text-right font-bold text-red-600">{{ number_format($excessQty > 0 ? $excessQty : 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="italic text-sm">Không có dữ liệu.</p>
                @endif
            </div>

            <!-- Print Signatures -->
            <div class="flex mt-6 w-full justify-between px-10 text-center">
                <div class="w-1/3">
                    <p class="font-bold text-sm uppercase">Trưởng nhóm kho</p>
                    <p class="text-xs mt-1 text-gray-500 italic">(Ký và ghi rõ họ tên)</p>
                </div>
                <div class="w-1/3">
                    <p class="font-bold text-sm uppercase">Quản lý kho</p>
                    <p class="text-xs mt-1 text-gray-500 italic">(Ký và ghi rõ họ tên)</p>
                </div>
                <div class="w-1/3">
                    <p class="font-bold text-sm uppercase">TBP.KTSC</p>
                    <p class="text-xs mt-1 text-gray-500 italic">(Ký và đóng dấu)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: CHI TIẾT XUẤT KHO -->
    <div x-show="tab === 'stock_out_details'" style="display: none;" class="print-section">
        
        <!-- Filters (Hidden in print) -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 filter-grid print:hidden">
            <div class="filter-field">
                <label class="form-label">Dự Án</label>
                <select wire:model.live="selectedProject" class="input-sm">
                    <option value="">-- Tất cả dự án --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label class="form-label">Từ Ngày</label>
                <input type="date" wire:model.live="startDate" class="input-sm">
            </div>
            <div class="filter-field">
                <label class="form-label">Đến Ngày</label>
                <input type="date" wire:model.live="endDate" class="input-sm">
            </div>
            <div class="filter-actions">
                <button @click="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow font-semibold flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    In Báo Cáo Chọn Lọc
                </button>
            </div>
        </div>

        <!-- Print Header (Only visible in print) -->
        <div class="hidden print:block text-center mb-8">
            <h2 class="text-2xl font-black uppercase">Báo Cáo Chi Tiết Vật Tư Xuất Kho</h2>
            <p class="mt-2 text-gray-600">Dự án: {{ $selectedProject ? $projects->where('id', $selectedProject)->first()->name : 'Tất cả' }}</p>
            <p class="text-gray-600">Thời gian: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Từ đầu' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Nay' }}</p>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto print-overflow-visible">
                <table class="w-full text-left border-collapse">
                    <thead class="print:static">
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                            <th class="p-3 w-10 text-center print:hidden">
                                <input type="checkbox" @change="selectedRows = $event.target.checked ? Array.from(document.querySelectorAll('.row-cb-details')).map(cb => cb.value) : []" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="p-3 font-medium">STT</th>
                            <th class="p-3 font-medium">Ngày xuất</th>
                            <th class="p-3 font-medium">Dự án</th>
                            <th class="p-3 font-medium">Mã tài sản</th>
                            <th class="p-3 font-medium">NV Sửa chữa</th>
                            <th class="p-3 font-medium">Mã vật tư</th>
                            <th class="p-3 font-medium">Tên vật tư</th>
                            <th class="p-3 font-medium text-right">SL Xuất</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($stockOutDetails ?? [] as $index => $row)
                            <tr :class="{ 'print:hidden': selectedRows.length > 0 && !selectedRows.includes('{{ $index }}') }" class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 text-center print:hidden">
                                    <input type="checkbox" value="{{ $index }}" x-model="selectedRows" class="row-cb-details rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="p-3 text-slate-500">{{ $index + 1 }}</td>
                                <td class="p-3 whitespace-nowrap text-slate-600">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                                <td class="p-3 font-medium text-slate-700">{{ $row->project_name }}</td>
                                <td class="p-3">
                                    <span class="font-mono text-xs text-indigo-600 bg-indigo-50 rounded px-2 py-1 inline-block">{{ $row->asset_code ?: 'N/A' }}</span>
                                </td>
                                <td class="p-3 text-slate-600">{{ $row->repair_staff ?: 'N/A' }}</td>
                                <td class="p-3 font-semibold text-slate-700">{{ $row->product_code }}</td>
                                <td class="p-3 text-slate-600">{{ $row->product_name }}</td>
                                <td class="p-3 text-right font-bold text-slate-800">{{ number_format($row->quantity) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-slate-400">Không có dữ liệu xuất kho nào phù hợp.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-200 text-xs text-slate-500 text-right print:hidden">
                Hiển thị tối đa 500 bản ghi gần nhất. Hãy dùng bộ lọc ngày để xem chi tiết hơn.
            </div>
        </div>
        
        <!-- Print Footer (Only visible in print) -->
        <div class="hidden print:grid grid-cols-3 mt-16 px-10 pb-10 w-full gap-4">
            <div class="text-center">
                <p class="font-bold text-sm">Nhân viên kho</p>
                <p class="text-xs mt-1 text-gray-500">(Ký và ghi rõ họ tên)</p>
            </div>
            <div class="text-center">
                <p class="font-bold text-sm">Trưởng nhóm</p>
                <p class="text-xs mt-1 text-gray-500">(Ký và ghi rõ họ tên)</p>
            </div>
            <div class="text-center">
                <p class="font-bold text-sm">Quản lý</p>
                <p class="text-xs mt-1 text-gray-500">(Ký và đóng dấu)</p>
            </div>
        </div>
    </div>

    <!-- Tải thư viện ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let timelineChart, donutChart, barChart;

            const initCharts = () => {
                const projectNames = @json($projectNames);
                const projectOrders = @json($projectOrders);
                const projectItems = @json($projectItems);
                const dates = @json($dates);
                const orderCounts = @json($orderCounts);

                if (document.querySelector("#chart-orders-timeline") && !document.querySelector("#chart-orders-timeline").hasChildNodes()) {
                    var timelineOptions = {
                        series: [{ name: "Số đơn xuất kho", data: orderCounts }],
                        chart: { type: 'area', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
                        colors: ['#4f46e5'],
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
                        dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 3 },
                        xaxis: { categories: dates, tickAmount: 10, labels: { style: { colors: '#64748b' } } },
                        yaxis: { labels: { style: { colors: '#64748b' } } }
                    };
                    timelineChart = new ApexCharts(document.querySelector("#chart-orders-timeline"), timelineOptions);
                    timelineChart.render();
                }

                if (document.querySelector("#chart-items-donut") && !document.querySelector("#chart-items-donut").hasChildNodes()) {
                    var donutOptions = {
                        series: projectItems.map(item => Number(item)),
                        chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
                        labels: projectNames,
                        colors: ['#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'],
                        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { show: true }, value: { show: true, formatter: function (val) { return new Intl.NumberFormat('vi-VN').format(val) + " SP" } } } } } },
                        dataLabels: { enabled: false }, legend: { position: 'bottom' }
                    };
                    donutChart = new ApexCharts(document.querySelector("#chart-items-donut"), donutOptions);
                    donutChart.render();
                }

                if (document.querySelector("#chart-orders-bar") && !document.querySelector("#chart-orders-bar").hasChildNodes()) {
                    var barOptions = {
                        series: [{ name: 'Đơn xuất kho', data: projectOrders }],
                        chart: { type: 'bar', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
                        plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '45%', distributed: true } },
                        colors: ['#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'],
                        dataLabels: { enabled: true, style: { fontSize: '12px', colors: ['#fff'] } },
                        legend: { show: false },
                        xaxis: { categories: projectNames, labels: { style: { colors: '#64748b', fontSize: '12px' } } },
                        yaxis: { labels: { style: { colors: '#64748b' } } }
                    };
                    barChart = new ApexCharts(document.querySelector("#chart-orders-bar"), barOptions);
                    barChart.render();
                }
            };
            
            // Initial render
            initCharts();
            
            // Lắng nghe sự kiện cập nhật dữ liệu từ backend Livewire
            Livewire.on('charts-updated', (data) => {
                const chartData = data[0]; // data là mảng chứa các tham số được dispatch
                
                if (timelineChart) {
                    timelineChart.updateSeries([{ name: "Số đơn xuất kho", data: chartData.orderCounts }]);
                    timelineChart.updateOptions({ xaxis: { categories: chartData.dates } });
                }
                
                if (donutChart) {
                    donutChart.updateSeries(chartData.projectItems.map(item => Number(item)));
                    donutChart.updateOptions({ labels: chartData.projectNames });
                }
                
                if (barChart) {
                    barChart.updateSeries([{ name: 'Đơn xuất kho', data: chartData.projectOrders }]);
                    barChart.updateOptions({ xaxis: { categories: chartData.projectNames } });
                }
            });
            
            // Re-render khi chuyển tab nếu biểu đồ bị xóa khỏi DOM
            Livewire.hook('morph.updated', ({ el, component }) => {
                if (!document.querySelector("#chart-orders-timeline").hasChildNodes()) {
                    initCharts();
                }
            });
        });
    </script>
    
    <style>
        /* Print styles */
        @media print {
            .print-overflow-visible {
                max-height: none !important;
                overflow: visible !important;
            }
            @page {
                size: A4 portrait;
            }
        }
    </style>
</div>