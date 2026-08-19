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
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Tổng Số Dự Án</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $projects->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center text-xl">🏢</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Tổng Số Nhân Sự</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $totalUsers }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-xl">👥</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Tổng Số Đơn Xuất</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1">{{ number_format($totalOrdersAllProjects) }}</h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center text-xl">📄</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Tổng SL Vật Tư Xuất</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1">{{ number_format($totalItemsAllProjects) }}</h3>
                </div>
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center text-xl">🔧</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8" wire:ignore>
            <!-- Biểu đồ Đường -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-5">
                <h3 class="text-base font-bold text-slate-800 mb-4">Biến Động Đơn Xuất Kho (30 Ngày Qua)</h3>
                <div id="chart-orders-timeline" class="w-full h-[350px]"></div>
            </div>
            
            <!-- Biểu đồ Tròn -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
                <h3 class="text-base font-bold text-slate-800 mb-4">Tỷ Trọng SL Vật Tư / Dự Án</h3>
                <div id="chart-items-donut" class="w-full h-[350px] flex items-center justify-center"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6" wire:ignore>
            <!-- Biểu đồ Cột -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
                <h3 class="text-base font-bold text-slate-800 mb-4">So Sánh Tổng Số Đơn Xuất Kho Giữa Các Dự Án</h3>
                <div id="chart-orders-bar" class="w-full h-[400px]"></div>
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
            <div class="ml-auto">
                <button @click="window.print()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow font-semibold flex items-center gap-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    In Cảnh Báo Chọn Lọc
                </button>
            </div>
        </div>

        <!-- Print Header (Only visible in print) -->
        <div class="hidden print:block text-center mb-8">
            <h2 class="text-2xl font-black uppercase">Báo Cáo Cảnh Báo Tồn Kho</h2>
            <p class="mt-2 text-gray-600">Dự án: {{ $warningProject ? $projects->where('id', $warningProject)->first()->name : 'Tất cả các kho' }}</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Sắp hết hàng -->
            <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden transition-opacity"
                 :class="{ 'print:hidden': selectedWarningHigh.length > 0 && selectedWarningLow.length === 0 }">
                <div class="bg-red-50 px-5 py-4 border-b border-red-100 flex items-center justify-between">
                    <h3 class="font-bold text-red-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Vật Tư Sắp Hết (Tồn kho < Min)
                    </h3>
                    <span class="bg-red-200 text-red-800 py-1 px-3 rounded-full text-xs font-bold">{{ count($lowStockProducts ?? []) }} mục</span>
                </div>
                <div class="overflow-x-auto print-overflow-visible" style="max-height: 500px;">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-slate-50 z-10 print:static">
                            <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                <th class="p-3 w-10 text-center print:hidden">
                                    <input type="checkbox" @change="selectedWarningLow = $event.target.checked ? Array.from(document.querySelectorAll('.row-cb-low')).map(cb => cb.value) : []" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
                                </th>
                                <th class="p-3 font-medium">Mã VT</th>
                                <th class="p-3 font-medium">Tên Vật Tư</th>
                                <th class="p-3 font-medium text-right">Định mức Min</th>
                                <th class="p-3 font-medium text-right text-red-600">Tồn kho HT</th>
                                <th class="p-3 font-medium">Kho Dự án</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($lowStockProducts ?? [] as $index => $item)
                                <tr :class="{ 'print:hidden': selectedWarningLow.length > 0 && !selectedWarningLow.includes('{{ $index }}') }" class="hover:bg-slate-50 transition-colors">
                                    <td class="p-3 text-center print:hidden">
                                        <input type="checkbox" value="{{ $index }}" x-model="selectedWarningLow" class="row-cb-low rounded border-slate-300 text-red-600 focus:ring-red-500">
                                    </td>
                                    <td class="p-3 font-semibold text-slate-700">{{ $item->code }}</td>
                                    <td class="p-3 text-slate-600">{{ $item->name }}</td>
                                    <td class="p-3 text-right text-slate-500">{{ number_format($item->min_stock) }} {{ $item->unit }}</td>
                                    <td class="p-3 text-right font-bold text-red-600">{{ number_format($item->quantity) }} {{ $item->unit }}</td>
                                    <td class="p-3 text-slate-600">{{ $item->project_name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400">Không có vật tư nào dưới định mức.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Dư thừa -->
            <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden transition-opacity"
                 :class="{ 'print:hidden': selectedWarningLow.length > 0 && selectedWarningHigh.length === 0 }">
                <div class="bg-amber-50 px-5 py-4 border-b border-amber-100 flex items-center justify-between">
                    <h3 class="font-bold text-amber-800 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Vật Tư Dư Thừa (Tồn kho > Max)
                    </h3>
                    <span class="bg-amber-200 text-amber-800 py-1 px-3 rounded-full text-xs font-bold">{{ count($highStockProducts ?? []) }} mục</span>
                </div>
                <div class="overflow-x-auto print-overflow-visible" style="max-height: 500px;">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-slate-50 z-10 print:static">
                            <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                <th class="p-3 w-10 text-center print:hidden">
                                    <input type="checkbox" @change="selectedWarningHigh = $event.target.checked ? Array.from(document.querySelectorAll('.row-cb-high')).map(cb => cb.value) : []" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                </th>
                                <th class="p-3 font-medium">Mã VT</th>
                                <th class="p-3 font-medium">Tên Vật Tư</th>
                                <th class="p-3 font-medium text-right">Định mức Max</th>
                                <th class="p-3 font-medium text-right text-amber-600">Tồn kho HT</th>
                                <th class="p-3 font-medium">Kho Dự án</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($highStockProducts ?? [] as $index => $item)
                                <tr :class="{ 'print:hidden': selectedWarningHigh.length > 0 && !selectedWarningHigh.includes('{{ $index }}') }" class="hover:bg-slate-50 transition-colors">
                                    <td class="p-3 text-center print:hidden">
                                        <input type="checkbox" value="{{ $index }}" x-model="selectedWarningHigh" class="row-cb-high rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                    </td>
                                    <td class="p-3 font-semibold text-slate-700">{{ $item->code }}</td>
                                    <td class="p-3 text-slate-600">{{ $item->name }}</td>
                                    <td class="p-3 text-right text-slate-500">{{ number_format($item->max_stock) }} {{ $item->unit }}</td>
                                    <td class="p-3 text-right font-bold text-amber-600">{{ number_format($item->quantity) }} {{ $item->unit }}</td>
                                    <td class="p-3 text-slate-600">{{ $item->project_name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-400">Không có vật tư nào vượt định mức.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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

    <!-- TAB 3: CHI TIẾT XUẤT KHO -->
    <div x-show="tab === 'stock_out_details'" style="display: none;" class="print-section">
        
        <!-- Filters (Hidden in print) -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-wrap gap-4 items-end print:hidden">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Dự Án</label>
                <select wire:model.live="selectedProject" class="border-slate-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Tất cả dự án --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Từ Ngày</label>
                <input type="date" wire:model.live="startDate" class="border-slate-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Đến Ngày</label>
                <input type="date" wire:model.live="endDate" class="border-slate-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="ml-auto">
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
            const renderCharts = () => {
                const projectNames = @json($projectNames);
                const projectOrders = @json($projectOrders);
                const projectItems = @json($projectItems);
                const dates = @json($dates);
                const orderCounts = @json($orderCounts);

                if (document.querySelector("#chart-orders-timeline") && !document.querySelector("#chart-orders-timeline").hasChildNodes()) {
                    var timelineOptions = {
                        series: [{ name: "Số đơn xuất kho", data: orderCounts }],
                        chart: { type: 'area', height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
                        colors: ['#4f46e5'],
                        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
                        dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 3 },
                        xaxis: { categories: dates, tickAmount: 10, labels: { style: { colors: '#64748b' } } },
                        yaxis: { labels: { style: { colors: '#64748b' } } }
                    };
                    new ApexCharts(document.querySelector("#chart-orders-timeline"), timelineOptions).render();
                }

                if (document.querySelector("#chart-items-donut") && !document.querySelector("#chart-items-donut").hasChildNodes()) {
                    var donutOptions = {
                        series: projectItems.map(item => Number(item)),
                        chart: { type: 'donut', height: 350, fontFamily: 'inherit' },
                        labels: projectNames,
                        colors: ['#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'],
                        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { show: true }, value: { show: true, formatter: function (val) { return new Intl.NumberFormat('vi-VN').format(val) + " SP" } } } } } },
                        dataLabels: { enabled: false }, legend: { position: 'bottom' }
                    };
                    new ApexCharts(document.querySelector("#chart-items-donut"), donutOptions).render();
                }

                if (document.querySelector("#chart-orders-bar") && !document.querySelector("#chart-orders-bar").hasChildNodes()) {
                    var barOptions = {
                        series: [{ name: 'Đơn xuất kho', data: projectOrders }],
                        chart: { type: 'bar', height: 400, toolbar: { show: false }, fontFamily: 'inherit' },
                        plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '45%', distributed: true } },
                        colors: ['#4f46e5', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4'],
                        dataLabels: { enabled: true, style: { fontSize: '12px', colors: ['#fff'] } },
                        legend: { show: false },
                        xaxis: { categories: projectNames, labels: { style: { colors: '#64748b', fontSize: '12px' } } },
                        yaxis: { labels: { style: { colors: '#64748b' } } }
                    };
                    new ApexCharts(document.querySelector("#chart-orders-bar"), barOptions).render();
                }
            };
            
            // Initial render
            renderCharts();
            
            // Re-render when Livewire updates (for tab switching)
            Livewire.hook('morph.updated', ({ el, component }) => {
                renderCharts();
            });
        });
    </script>
    
    <style>
        /* Print styles */
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .print\:hidden {
                display: none !important;
            }
            .print-overflow-visible {
                max-height: none !important;
                overflow: visible !important;
            }
            @page {
                size: A4 landscape;
                margin: 1cm;
            }
        }
    </style>
</div>