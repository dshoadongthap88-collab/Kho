<div>
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

    <div class="flex justify-between items-center mb-6">
        <div class="flex gap-4 items-end bg-white p-3 rounded-xl border shadow-sm">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Từ ngày</label>
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Đến ngày</label>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
        </div>
        
        <button type="button" onclick="window.print()" class="flex items-center gap-1.5 px-6 py-2.5 bg-slate-800 hover:bg-black text-white rounded-xl text-sm font-black transition shadow-lg cursor-pointer no-print">
            <span class="text-lg">📄</span> IN BÁO CÁO PDF
        </button>
    </div>

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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" x-data="{ 
            receiverData: @entangle('receiverData'),
            assetData: @entangle('assetData'),
            topExportData: @entangle('topExportData'),
            charts: { receiver: null, asset: null, topExport: null },
            init() {
                const common = { chart: { toolbar: { show: false }, animations: { enabled: true } } };
                
                // NEW Stock-out Charts
                this.charts.receiver = new ApexCharts($refs.receiverChart, { 
                    ...common, chart: { ...common.chart, type: 'bar', height: 350 }, 
                    series: (this.receiverData && this.receiverData.series && this.receiverData.series.length > 0) ? this.receiverData.series : [{name: 'Trống', data: [0]}], 
                    xaxis: { categories: (this.receiverData && this.receiverData.labels) ? this.receiverData.labels : [] }, 
                    colors: ['#8B5CF6'],
                    title: { text: 'Top 10 Nhân viên lãnh hàng (Số lượng)', style: { fontWeight: 'bold' } } 
                });
                this.charts.receiver.render();

                this.charts.asset = new ApexCharts($refs.assetChart, { 
                    ...common, chart: { ...common.chart, type: 'bar', height: 350 }, 
                    series: (this.assetData && this.assetData.series && this.assetData.series.length > 0) ? this.assetData.series : [{name: 'Trống', data: [0]}], 
                    xaxis: { categories: (this.assetData && this.assetData.labels) ? this.assetData.labels : [] }, 
                    colors: ['#F43F5E'],
                    title: { text: 'Top 10 Mã tài sản tiêu thụ vật tư', style: { fontWeight: 'bold' } } 
                });
                this.charts.asset.render();

                this.charts.topExport = new ApexCharts($refs.topExportChart, { 
                    ...common, chart: { ...common.chart, type: 'bar', height: 350 }, 
                    series: (this.topExportData && this.topExportData.series && this.topExportData.series.length > 0) ? this.topExportData.series : [{name: 'Trống', data: [0]}], 
                    xaxis: { categories: (this.topExportData && this.topExportData.labels) ? this.topExportData.labels : [] }, 
                    colors: ['#FB923C'],
                    title: { text: 'Top 10 Sản phẩm xuất kho nhiều nhất', style: { fontWeight: 'bold' } } 
                });
                this.charts.topExport.render();
                
                this.$watch('receiverData', val => { if(!val || !val.series) return; this.charts.receiver.updateOptions({ xaxis: { categories: val.labels } }, false, false); this.charts.receiver.updateSeries(val.series); });
                this.$watch('assetData', val => { if(!val || !val.series) return; this.charts.asset.updateOptions({ xaxis: { categories: val.labels } }, false, false); this.charts.asset.updateSeries(val.series); });
                this.$watch('topExportData', val => { if(!val || !val.series) return; this.charts.topExport.updateOptions({ xaxis: { categories: val.labels } }, false, false); this.charts.topExport.updateSeries(val.series); });
            }
         }">
        
        <div class="lg:col-span-2 mb-2 no-print">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight border-l-4 border-red-600 pl-4 mb-4">Hệ thống Cảnh báo & Phân tích thông minh</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($warnings as $warn)
                <div class="p-4 rounded-xl border-l-4 shadow-sm flex gap-3 
                    {{ $warn['type'] === 'danger' ? 'bg-red-50 border-red-500 text-red-900' : '' }}
                    {{ $warn['type'] === 'warning' ? 'bg-orange-50 border-orange-500 text-orange-900' : '' }}
                    {{ $warn['type'] === 'info' ? 'bg-blue-50 border-blue-500 text-blue-900' : '' }}">
                    <div class="text-2xl">{{ $warn['icon'] }}</div>
                    <div>
                        <p class="text-xs font-black uppercase mb-1">{{ $warn['title'] }}</p>
                        <p class="text-sm leading-relaxed">{!! $warn['content'] !!}</p>
                    </div>
                </div>
                @empty
                <div class="lg:col-span-3 p-8 text-center bg-gray-50 rounded-xl border border-dashed text-gray-400">
                    <p class="text-2xl mb-2">✅</p>
                    <p class="text-sm font-medium">Hiện tại không có cảnh báo bất thường nào trong hệ thống.</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-2 mt-8 mb-2 border-l-4 border-indigo-600 pl-4 no-print">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">Phân tích Xuất kho chuyên sâu</h2>
            <p class="text-xs text-gray-400 font-medium italic">Thống kê theo Nhân viên lãnh, Mã tài sản tiêu thụ và Vật tư xuất kho</p>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div x-ref="receiverChart"></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border">
            <div x-ref="assetChart"></div>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border lg:col-span-2">
            <div x-ref="topExportChart"></div>
        </div>
    </div>
</div>
