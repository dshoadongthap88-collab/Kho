<div>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            nav, .sidebar-toolbar, button, a, .no-print, input[type="date"], select { display: none !important; }
            .bg-white { box-shadow: none !important; border: none !important; }
            body { background: white !important; font-size: 10pt; }
            .grid-cols-3 { display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important; gap: 1rem; }
            .grid-cols-2 { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 1rem; }
            .chart-container { page-break-inside: avoid; }
            .apexcharts-toolbar { display: none !important; }
        }
    </style>

    @php
        $currentHouse = session('current_house', 1);
        if ($currentHouse == 2) {
            $projectName = 'HẬU NGHĨA';
        } elseif ($currentHouse == 3) {
            $projectName = 'CẦN GIỜ';
        } elseif ($currentHouse == 4) {
            $projectName = 'CẦN GIUỘC';
        } else {
            $projectName = 'HÓC MÔN';
        }
    @endphp

    <div class="hidden print:block text-center mb-6 border-b-2 border-black pb-4">
        <h1 class="text-2xl font-black uppercase text-black">BÁO CÁO TỔNG HỢP KHO - DỰ ÁN {{ $projectName }}</h1>
        <p class="text-sm font-bold text-slate-800 mt-1">Giai đoạn: {{ date('d/m/Y', strtotime($dateFrom)) }} - {{ date('d/m/Y', strtotime($dateTo)) }}</p>
    </div>

    <div class="flex justify-between items-center mb-6 no-print">
        <div class="flex gap-2 items-end bg-white p-3 rounded-xl border shadow-sm">
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

    <div class="grid grid-cols-3 gap-2 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-xl p-2 shadow-sm">
            <p class="text-xs font-bold text-green-600 uppercase mb-1">Tổng nhập trong kỳ</p>
            <p class="text-2xl font-black text-green-700">{{ number_format($summary->total_import ?? 0) }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-2 shadow-sm">
            <p class="text-xs font-bold text-orange-600 uppercase mb-1">Tổng xuất trong kỳ</p>
            <p class="text-2xl font-black text-orange-700">{{ number_format($summary->total_export ?? 0) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-2 shadow-sm">
            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Tổng điều chỉnh</p>
            <p class="text-2xl font-black text-blue-700">{{ number_format($summary->total_adjust ?? 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 mb-8" x-data="{ 
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
        
        <div class="lg:col-span-2 mb-2">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight border-l-4 border-red-600 pl-4 mb-4">Hệ thống Cảnh báo & Phân tích thông minh</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @forelse($warnings as $warn)
                <div class="p-2 rounded-xl border-l-4 shadow-sm flex gap-3 
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

        <div class="lg:col-span-2 mt-8 mb-2 border-l-4 border-indigo-600 pl-4">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">Phân tích Xuất kho chuyên sâu</h2>
            <p class="text-xs text-gray-400 font-medium italic">Thống kê theo Nhân viên lãnh, Mã tài sản tiêu thụ và Vật tư xuất kho</p>
        </div>

        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-2 mb-6">
            <!-- Thống kê chung -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-2 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-indigo-600 uppercase mb-1">Tổng mã tài sản xuất</p>
                    <p class="text-2xl font-black text-indigo-700">{{ number_format($totalAssets) }} <span class="text-sm font-normal">mã</span></p>
                </div>
                <div class="text-3xl">🏗️</div>
            </div>
            <div class="bg-teal-50 border border-teal-200 rounded-xl p-2 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-teal-600 uppercase mb-1">Tổng số vật tư xuất</p>
                    <p class="text-2xl font-black text-teal-700">{{ number_format($totalMaterials) }} <span class="text-sm font-normal">đơn vị</span></p>
                </div>
                <div class="text-3xl">📦</div>
            </div>

            <!-- Cảnh báo dự đoán -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-2 shadow-sm relative">
                <div class="flex justify-between items-start mb-2">
                    <p class="text-xs font-bold text-purple-600 uppercase flex items-center gap-1"><span>📈</span> Dự đoán đặt hàng (Sử dụng vượt Tồn Kho)</p>
                    <button wire:click="autoCreatePurchasePlan" onclick="confirm('Hệ thống sẽ tự động tạo Kế hoạch mua hàng cho các mã này và gửi thông báo cho Ngôi nhà HR. Bạn có chắc chắn?') || event.stopImmediatePropagation()" class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-[10px] font-bold rounded shadow-sm transition">
                        Tự động lập KH Mua hàng
                    </button>
                </div>
                @if($predictiveStocks->count() > 0)
                    <ul class="text-sm text-purple-900 space-y-1">
                    @foreach($predictiveStocks as $stock)
                        <li>- <b>{{ $stock->name }}</b> ({{ $stock->code }}): Đã xuất <b>{{ number_format($stock->total_out) }}</b> > Tồn <b>{{ number_format($stock->current_stock) }}</b></li>
                    @endforeach
                    </ul>
                @else
                    <p class="text-sm text-purple-700 italic mt-2">Không có mã nào có rủi ro thiếu hụt trong chu kỳ này.</p>
                @endif
            </div>

            <!-- Cảnh báo Dead stock -->
            <div class="bg-stone-50 border border-stone-300 rounded-xl p-2 shadow-sm" x-data="{
                selectAll: false,
                selectedItems: [],
                toggleAll() {
                    if (this.selectAll) {
                        this.selectedItems = Array.from(document.querySelectorAll('.dead-stock-item')).map(el => el.value);
                    } else {
                        this.selectedItems = [];
                    }
                },
                printDeadStock() {
                    if (this.selectedItems.length === 0) {
                        alert('Vui lòng chọn ít nhất 1 vật tư để in báo cáo.');
                        return;
                    }
                    
                    let printHtml = `
                        <div style='font-family: sans-serif; margin: 20px;'>
                            <h2 style='margin:0; text-transform:uppercase; font-size:18px;'>KHO KỸ THUẬT SỬA CHỮA</h2>
                            <h3 style='margin:5px 0 15px 0; font-size:16px;'>DỰ ÁN : {{ $projectName }}</h3>
                            <h1 style='text-align:center; font-size: 22px; font-weight: bold; margin-bottom: 20px;'>BÁO CÁO VẬT TƯ CHƯA SỬ DỤNG</h1>
                            <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                                <thead>
                                    <tr>
                                        <th style='border: 1px solid #000; padding: 8px;'>STT</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>Mã vật tư</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>Tên vật tư</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>ĐVT</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>Số lượng</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>Ngày đặt</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    this.selectedItems.forEach((item, index) => {
                        let obj = JSON.parse(item);
                        printHtml += `
                            <tr>
                                <td style='border: 1px solid #000; padding: 8px; text-align: center;'>${index + 1}</td>
                                <td style='border: 1px solid #000; padding: 8px;'>${obj.code}</td>
                                <td style='border: 1px solid #000; padding: 8px;'>${obj.name}</td>
                                <td style='border: 1px solid #000; padding: 8px; text-align: center;'>${obj.unit || ''}</td>
                                <td style='border: 1px solid #000; padding: 8px; text-align: right;'>${obj.quantity}</td>
                                <td style='border: 1px solid #000; padding: 8px; text-align: center;'>${obj.date}</td>
                            </tr>
                        `;
                    });
                    
                    printHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    let printFrame = document.createElement('iframe');
                    printFrame.style.position = 'fixed';
                    printFrame.style.right = '0';
                    printFrame.style.bottom = '0';
                    printFrame.style.width = '0';
                    printFrame.style.height = '0';
                    printFrame.style.border = '0';
                    document.body.appendChild(printFrame);
                    
                    let frameDoc = printFrame.contentWindow.document;
                    frameDoc.open();
                    frameDoc.write('<html><head><title>In báo cáo tồn đọng</title></head><body>' + printHtml + '</body></html>');
                    frameDoc.close();
                    
                    setTimeout(() => {
                        printFrame.contentWindow.focus();
                        printFrame.contentWindow.print();
                        document.body.removeChild(printFrame);
                    }, 500);
                }
            }">
                <div class="flex justify-between items-center mb-2">
                    <p class="text-xs font-bold text-stone-600 uppercase flex items-center gap-1"><span>🕸️</span> Cảnh báo không sử dụng > 300 ngày</p>
                    @if($deadStocks->count() > 0)
                    <button @click="printDeadStock()" class="px-3 py-1 bg-stone-700 hover:bg-stone-900 text-white text-[10px] font-bold rounded shadow-sm transition flex items-center gap-1 no-print">
                        <span>🖨️</span> In báo cáo
                    </button>
                    @endif
                </div>
                
                @if($deadStocks->count() > 0)
                    <div class="mb-2 px-2 flex items-center gap-2 border-b border-stone-200 pb-2 no-print">
                        <input type="checkbox" x-model="selectAll" @change="toggleAll()" class="rounded border-gray-300 text-stone-600 focus:ring-stone-500">
                        <span class="text-xs font-bold text-stone-700">Chọn tất cả</span>
                    </div>
                    <ul class="text-sm text-stone-900 space-y-1 max-h-60 overflow-y-auto px-2">
                    @foreach($deadStocks as $stock)
                        @php
                            $stockData = json_encode([
                                'code' => $stock->code,
                                'name' => $stock->name,
                                'unit' => $stock->unit,
                                'quantity' => number_format($stock->quantity),
                                'date' => $stock->updated_at->format('d/m/Y')
                            ]);
                        @endphp
                        <li class="flex items-start gap-2 hover:bg-stone-100 p-1 rounded">
                            <input type="checkbox" x-model="selectedItems" value="{{ $stockData }}" class="dead-stock-item mt-1 rounded border-gray-300 text-stone-600 focus:ring-stone-500 no-print">
                            <div>
                                <b>{{ $stock->name }}</b> ({{ $stock->code }})
                                <span class="text-xs text-stone-600 ml-1">ĐVT: {{ $stock->unit ?? '-' }}</span><br>
                                <span class="text-xs text-stone-600">Tồn đọng: <b>{{ number_format($stock->quantity) }}</b> (Ngày nhập: {{ $stock->updated_at->format('d/m/Y') }})</span>
                            </div>
                        </li>
                    @endforeach
                    </ul>
                @else
                    <p class="text-sm text-stone-700 italic">Hệ thống luân chuyển tốt, không có hàng tồn đọng lâu.</p>
                @endif
            </div>

            <!-- Cảnh báo Excess stock (Hàng thừa) -->
            <div class="bg-rose-50 border border-rose-300 rounded-xl p-2 shadow-sm mt-2" x-data="{
                selectAllExcess: false,
                selectedExcessItems: [],
                toggleAllExcess() {
                    if (this.selectAllExcess) {
                        this.selectedExcessItems = Array.from(document.querySelectorAll('.excess-stock-item')).map(el => el.value);
                    } else {
                        this.selectedExcessItems = [];
                    }
                },
                printExcessStock() {
                    if (this.selectedExcessItems.length === 0) {
                        alert('Vui lòng chọn ít nhất 1 vật tư để in báo cáo.');
                        return;
                    }
                    
                    let printHtml = `
                        <div style='font-family: sans-serif; margin: 20px;'>
                            <h2 style='margin:0; text-transform:uppercase; font-size:18px;'>KHO KỸ THUẬT SỮA CHỮA VINALPHA</h2>
                            <h3 style='margin:5px 0 15px 0; font-size:16px;'>DỰ ÁN : {{ $projectName }}</h3>
                            <h1 style='text-align:center; font-size: 22px; font-weight: bold; margin-bottom: 20px;'>VẬT TƯ DƯ THỪA</h1>
                            <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                                <thead>
                                    <tr>
                                        <th style='border: 1px solid #000; padding: 8px;'>STT</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>Mã vật tư</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>Tên vật tư</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>ĐVT</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>Tồn kho</th>
                                        <th style='border: 1px solid #000; padding: 8px;'>Dư thừa</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    this.selectedExcessItems.forEach((item, index) => {
                        let obj = JSON.parse(item);
                        printHtml += `
                            <tr>
                                <td style='border: 1px solid #000; padding: 8px; text-align: center;'>${index + 1}</td>
                                <td style='border: 1px solid #000; padding: 8px;'>${obj.code}</td>
                                <td style='border: 1px solid #000; padding: 8px;'>${obj.name}</td>
                                <td style='border: 1px solid #000; padding: 8px; text-align: center;'>${obj.unit || ''}</td>
                                <td style='border: 1px solid #000; padding: 8px; text-align: right;'>${obj.quantity}</td>
                                <td style='border: 1px solid #000; padding: 8px; text-align: right; color: red; font-weight: bold;'>${obj.excess}</td>
                            </tr>
                        `;
                    });
                    
                    printHtml += `
                                </tbody>
                            </table>
                        </div>
                    `;
                    
                    let printFrame = document.createElement('iframe');
                    printFrame.style.position = 'fixed';
                    printFrame.style.right = '0';
                    printFrame.style.bottom = '0';
                    printFrame.style.width = '0';
                    printFrame.style.height = '0';
                    printFrame.style.border = '0';
                    document.body.appendChild(printFrame);
                    
                    let frameDoc = printFrame.contentWindow.document;
                    frameDoc.open();
                    frameDoc.write('<html><head><title>In báo cáo hàng thừa</title></head><body>' + printHtml + '</body></html>');
                    frameDoc.close();
                    
                    setTimeout(() => {
                        printFrame.contentWindow.focus();
                        printFrame.contentWindow.print();
                        document.body.removeChild(printFrame);
                    }, 500);
                }
            }">
                <div class="flex justify-between items-center mb-2">
                    <p class="text-xs font-bold text-rose-600 uppercase flex items-center gap-1"><span>📦</span> Cảnh báo hàng dư thừa (> Tồn tối đa)</p>
                    @if(isset($excessStocks) && $excessStocks->count() > 0)
                    <button @click="printExcessStock()" class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold rounded shadow-sm transition flex items-center gap-1 no-print">
                        <span>🖨️</span> In báo cáo
                    </button>
                    @endif
                </div>
                
                @if(isset($excessStocks) && $excessStocks->count() > 0)
                    <div class="mb-2 px-2 flex items-center gap-2 border-b border-rose-200 pb-2 no-print">
                        <input type="checkbox" x-model="selectAllExcess" @change="toggleAllExcess()" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                        <span class="text-xs font-bold text-rose-700">Chọn tất cả</span>
                    </div>
                    <ul class="text-sm text-rose-900 space-y-1 max-h-60 overflow-y-auto px-2">
                    @foreach($excessStocks as $stock)
                        @php
                            $excessQty = $stock->quantity - $stock->max_stock;
                            $stockData = json_encode([
                                'code' => $stock->code,
                                'name' => $stock->name,
                                'unit' => $stock->unit,
                                'quantity' => number_format($stock->quantity),
                                'excess' => number_format($excessQty)
                            ]);
                        @endphp
                        <li class="flex items-start gap-2 hover:bg-rose-100 p-1 rounded">
                            <input type="checkbox" x-model="selectedExcessItems" value="{{ $stockData }}" class="excess-stock-item mt-1 rounded border-gray-300 text-rose-600 focus:ring-rose-500 no-print">
                            <div>
                                <b>{{ $stock->name }}</b> ({{ $stock->code }})
                                <span class="text-xs text-rose-600 ml-1">ĐVT: {{ $stock->unit ?? '-' }}</span><br>
                                <span class="text-xs text-rose-600">Tồn kho: <b>{{ number_format($stock->quantity) }}</b> | Tồn max: {{ number_format($stock->max_stock) }} | Dư: <b class="text-red-600">{{ number_format($excessQty) }}</b></span>
                            </div>
                        </li>
                    @endforeach
                    </ul>
                @else
                    <p class="text-sm text-rose-700 italic">Không có vật tư nào vượt quá định mức tồn kho tối đa.</p>
                @endif
            </div>
        </div>

        <div class="bg-white p-2 rounded-xl shadow-sm border">
            <div x-ref="receiverChart"></div>
        </div>
        <div class="bg-white p-2 rounded-xl shadow-sm border">
            <div x-ref="assetChart"></div>
        </div>
        <div class="bg-white p-2 rounded-xl shadow-sm border lg:col-span-2">
            <div x-ref="topExportChart"></div>
        </div>
    </div>
</div>
