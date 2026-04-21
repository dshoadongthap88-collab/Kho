<div>
    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            nav, .sidebar-toolbar, button, a, .no-print, input, select { display: none !important; }
            .bg-white { box-shadow: none !important; border: none !important; }
            body { background: white !important; font-size: 10pt; }
            .grid-cols-3, .grid-cols-2 { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .chart-container { page-break-inside: avoid; }
        }
    </style>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-green-600 uppercase mb-1">Tổng nhập trong kỳ</p>
            <p class="text-2xl font-black text-green-700">{{ number_format($summary->total_import ?? 0) }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-orange-600 uppercase mb-1">Tổng xuất trong kỳ</p>
            <p class="text-2xl font-black text-orange-700">{{ number_format($summary->total_export ?? 0) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Tổng điều chỉnh</p>
            <p class="text-2xl font-black text-blue-700">{{ number_format($summary->total_adjust ?? 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Từ ngày</label>
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Đến ngày</label>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Loại giao dịch</label>
                <select wire:model.live="filterType" class="rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
                    <option value="">Tất cả</option>
                    <option value="import">Nhập kho</option>
                    <option value="export">Xuất kho</option>
                    <option value="adjust">Điều chỉnh</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tìm sản phẩm</label>
                <input type="text" wire:model.live.debounce.300ms="filterProduct" placeholder="Nhập mã hoặc tên sản phẩm..."
                       class="w-full rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            
            <!-- Export Buttons -->
            <div class="flex gap-2 mb-0.5">
                <button wire:click="exportExcel" class="flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Xuất báo cáo Excel">
                    <span class="text-sm">📊</span> Excel
                </button>
                <button onclick="window.print()" class="flex items-center gap-1.5 px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Xuất báo cáo PDF/In">
                    <span class="text-sm">📄</span> PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Charts Dashboard Area -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" x-data="{ 
            barData: @entangle('barData'),
            pieData: @entangle('pieData'),
            paretoData: @entangle('paretoData'),
            heatMapData: @entangle('heatMapData'),
            charts: { bar: null, pie: null, pareto: null, heat: null },
            init() {
                const common = { chart: { toolbar: { show: false }, animations: { enabled: true } } };
                
                // Bar Chart
                this.charts.bar = new ApexCharts($refs.barChart, { 
                    ...common, 
                    chart: { ...common.chart, type: 'bar', height: 350 }, 
                    series: (this.barData && this.barData.series && this.barData.series.length > 0) ? this.barData.series : [{name: 'Trống', data: [0]}], 
                    xaxis: { categories: (this.barData && this.barData.labels) ? this.barData.labels : [] }, 
                    colors: ['#10B981', '#F59E0B', '#6366F1'],
                    title: { text: 'Nhập - Xuất - Tồn (Top 10 SP)', style: { fontWeight: 'bold' } } 
                });
                this.charts.bar.render();

                // Pie Chart
                let validPie = this.pieData && this.pieData.series && this.pieData.series.length > 0;
                this.charts.pie = new ApexCharts($refs.pieChart, { 
                    ...common, 
                    chart: { ...common.chart, type: 'donut', height: 350 }, 
                    series: validPie ? this.pieData.series : [0.001], 
                    labels: validPie ? this.pieData.labels : ['Trống'], 
                    colors: ['#6366F1', '#10B981', '#F59E0B', '#EC4899', '#8B5CF6'],
                    title: { text: 'Cơ cấu Tồn kho theo Danh mục', style: { fontWeight: 'bold' } } 
                });
                this.charts.pie.render();

                // Pareto Chart
                let validPareto = this.paretoData && this.paretoData.labels && this.paretoData.labels.length > 0;
                this.charts.pareto = new ApexCharts($refs.paretoChart, { 
                    ...common, 
                    chart: { ...common.chart, type: 'line', height: 350 }, 
                    series: [
                        { name: 'Số lượng tồn', type: 'column', data: validPareto ? this.paretoData.quantities : [0] }, 
                        { name: 'Tỷ lệ lũy kế %', type: 'line', data: validPareto ? this.paretoData.percentages : [0] }
                    ], 
                    labels: validPareto ? this.paretoData.labels : ['Trống'],
                    stroke: { width: [0, 4] }, 
                    colors: ['#4F46E5', '#EF4444'],
                    yaxis: [{title:{text:'Số lượng'}}, {opposite:true, max:100, title:{text:'%'}}], 
                    title: { text: 'Phân tích Pareto (Hàng chủ lực 80/20)', style: { fontWeight: 'bold' } } 
                });
                this.charts.pareto.render();

                // HeatMap Chart
                let validHeat = this.heatMapData && this.heatMapData.length > 0;
                this.charts.heat = new ApexCharts($refs.heatMapChart, { 
                    ...common, 
                    chart: { ...common.chart, type: 'heatmap', height: 350 }, 
                    series: validHeat ? this.heatMapData : [{name: 'Trống', data: []}], 
                    colors: ['#10B981'],
                    plotOptions: {
                        heatmap: {
                            colorScale: {
                                ranges: [
                                    { from: 0, to: 0, color: '#F3F4F6', name: 'Trống' },
                                    { from: 1, to: 10, color: '#60A5FA', name: 'Ít' },
                                    { from: 11, to: 50, color: '#F59E0B', name: 'TB' },
                                    { from: 51, to: 10000, color: '#EF4444', name: 'Nhiều' }
                                ]
                            }
                        }
                    },
                    title: { text: 'Sức khỏe tồn kho (Theo hạn dùng)', style: { fontWeight: 'bold' } } 
                });
                this.charts.heat.render();
                
                // Watch updates from Livewire
                this.$watch('barData', val => {
                    if(!val || !val.series) return;
                    this.charts.bar.updateOptions({ xaxis: { categories: val.labels } }, false, false);
                    this.charts.bar.updateSeries(val.series);
                });
                this.$watch('pieData', val => {
                    if(!val || !val.series) return;
                    let hasData = val.series.length && val.series.reduce((a,b)=>a+b, 0) > 0;
                    this.charts.pie.updateOptions({ labels: hasData ? val.labels : ['Trống'] }, false, false);
                    this.charts.pie.updateSeries(hasData ? val.series : [0.001]);
                });
                this.$watch('paretoData', val => {
                    if(!val || !val.labels) return;
                    let hasData = val.labels.length > 0;
                    this.charts.pareto.updateOptions({ labels: hasData ? val.labels : ['Trống'] }, false, false);
                    this.charts.pareto.updateSeries([{name:'Số lượng', data: hasData ? val.quantities : [0]}, {name:'%', data: hasData ? val.percentages : [0]}]);
                });
                this.$watch('heatMapData', val => {
                    if(!val || !val.length) return;
                    this.charts.heat.updateSeries(val);
                });
            }
         }">
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div x-ref="barChart"></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div x-ref="pieChart"></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div x-ref="paretoChart"></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div x-ref="heatMapChart"></div>
        </div>
    </div>

    <!-- Transaction List -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
            <h3 class="text-sm font-bold text-gray-700">Chi tiết giao dịch gần đây</h3>
            <span class="text-[10px] text-gray-400">Trang {{ $transactions->currentPage() }} / {{ $transactions->lastPage() }}</span>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Thời gian</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Sản phẩm</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Loại</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Số lượng</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Người thực hiện</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Ghi chú</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 italic-rows">
                @forelse($transactions as $tx)
                @php
                    $typeColors = [
                        'import' => 'bg-green-50 text-green-700 border-green-100',
                        'export' => 'bg-amber-50 text-amber-700 border-amber-100',
                        'adjust' => 'bg-blue-50 text-blue-700 border-blue-100',
                        'reserve' => 'bg-purple-50 text-purple-700 border-purple-100',
                    ];
                    $typeLabels = [
                        'import' => '📥 Nhập',
                        'export' => '📤 Xuất',
                        'adjust' => '⚙️ Đ/chỉnh',
                        'reserve' => '🔒 Giữ',
                    ];
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-gray-800">{{ $tx->product->name ?? '' }}</div>
                        <div class="text-[10px] text-gray-400 font-mono">{{ $tx->product->code ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $typeColors[$tx->type] ?? 'bg-gray-50 text-gray-600' }}">
                            {{ $typeLabels[$tx->type] ?? $tx->type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-black {{ $tx->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tx->quantity >= 0 ? '+' : '' }}{{ number_format($tx->quantity, 2) }}
                    </td>
                    <td class="px-4 py-3 text-xs font-medium text-gray-600 italic">👤 {{ $tx->creator->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-[10px] text-gray-400 leading-tight">{{ Str::limit($tx->note, 50) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 italic">Dữ liệu trống trong khoảng thời gian này...</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
