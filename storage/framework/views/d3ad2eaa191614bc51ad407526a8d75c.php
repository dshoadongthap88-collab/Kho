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

    <?php
        $currentHouse = session('current_house', 1);
        if ($currentHouse == 2) {
            $projectName = 'HẬU NGHĨA';
        } elseif ($currentHouse == 3) {
            $projectName = 'CẦN GIUỘC';
        } elseif ($currentHouse == 4) {
            $projectName = 'CẦN GIỜ';
        } else {
            $projectName = 'HÓC MÔN';
        }
    ?>

    <div class="hidden print:block text-center mb-6 border-b-2 border-black pb-4">
        <h1 class="text-2xl font-black uppercase text-black">BÁO CÁO TỔNG HỢP KHO - DỰ ÁN <?php echo e($projectName); ?></h1>
        <p class="text-sm font-bold text-slate-800 mt-1">Giai đoạn: <?php echo e(date('d/m/Y', strtotime($dateFrom))); ?> - <?php echo e(date('d/m/Y', strtotime($dateTo))); ?></p>
    </div>

    <div class="flex justify-between items-center mb-6 no-print">
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
            <p class="text-2xl font-black text-green-700"><?php echo e(number_format($summary->total_import ?? 0)); ?></p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-orange-600 uppercase mb-1">Tổng xuất trong kỳ</p>
            <p class="text-2xl font-black text-orange-700"><?php echo e(number_format($summary->total_export ?? 0)); ?></p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Tổng điều chỉnh</p>
            <p class="text-2xl font-black text-blue-700"><?php echo e(number_format($summary->total_adjust ?? 0)); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" x-data="{ 
            receiverData: <?php if ((object) ('receiverData') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('receiverData'->value()); ?>')<?php echo e('receiverData'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('receiverData'); ?>')<?php endif; ?>,
            assetData: <?php if ((object) ('assetData') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('assetData'->value()); ?>')<?php echo e('assetData'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('assetData'); ?>')<?php endif; ?>,
            topExportData: <?php if ((object) ('topExportData') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('topExportData'->value()); ?>')<?php echo e('topExportData'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('topExportData'); ?>')<?php endif; ?>,
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $warnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="p-4 rounded-xl border-l-4 shadow-sm flex gap-3 
                    <?php echo e($warn['type'] === 'danger' ? 'bg-red-50 border-red-500 text-red-900' : ''); ?>

                    <?php echo e($warn['type'] === 'warning' ? 'bg-orange-50 border-orange-500 text-orange-900' : ''); ?>

                    <?php echo e($warn['type'] === 'info' ? 'bg-blue-50 border-blue-500 text-blue-900' : ''); ?>">
                    <div class="text-2xl"><?php echo e($warn['icon']); ?></div>
                    <div>
                        <p class="text-xs font-black uppercase mb-1"><?php echo e($warn['title']); ?></p>
                        <p class="text-sm leading-relaxed"><?php echo $warn['content']; ?></p>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="lg:col-span-3 p-8 text-center bg-gray-50 rounded-xl border border-dashed text-gray-400">
                    <p class="text-2xl mb-2">✅</p>
                    <p class="text-sm font-medium">Hiện tại không có cảnh báo bất thường nào trong hệ thống.</p>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="lg:col-span-2 mt-8 mb-2 border-l-4 border-indigo-600 pl-4">
            <h2 class="text-xl font-black text-slate-800 uppercase tracking-tight">Phân tích Xuất kho chuyên sâu</h2>
            <p class="text-xs text-gray-400 font-medium italic">Thống kê theo Nhân viên lãnh, Mã tài sản tiêu thụ và Vật tư xuất kho</p>
        </div>

        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Thống kê chung -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-indigo-600 uppercase mb-1">Tổng mã tài sản xuất</p>
                    <p class="text-2xl font-black text-indigo-700"><?php echo e(number_format($totalAssets)); ?> <span class="text-sm font-normal">mã</span></p>
                </div>
                <div class="text-3xl">🏗️</div>
            </div>
            <div class="bg-teal-50 border border-teal-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-teal-600 uppercase mb-1">Tổng số vật tư xuất</p>
                    <p class="text-2xl font-black text-teal-700"><?php echo e(number_format($totalMaterials)); ?> <span class="text-sm font-normal">đơn vị</span></p>
                </div>
                <div class="text-3xl">📦</div>
            </div>

            <!-- Cảnh báo dự đoán -->
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 shadow-sm relative">
                <div class="flex justify-between items-start mb-2">
                    <p class="text-xs font-bold text-purple-600 uppercase flex items-center gap-1"><span>📈</span> Dự đoán đặt hàng (Sử dụng vượt Tồn Kho)</p>
                    <button wire:click="autoCreatePurchasePlan" onclick="confirm('Hệ thống sẽ tự động tạo Kế hoạch mua hàng cho các mã này và gửi thông báo cho Ngôi nhà HR. Bạn có chắc chắn?') || event.stopImmediatePropagation()" class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-[10px] font-bold rounded shadow-sm transition">
                        Tự động lập KH Mua hàng
                    </button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($predictiveStocks->count() > 0): ?>
                    <ul class="text-sm text-purple-900 space-y-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $predictiveStocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li>- <b><?php echo e($stock->name); ?></b> (<?php echo e($stock->code); ?>): Đã xuất <b><?php echo e(number_format($stock->total_out)); ?></b> > Tồn <b><?php echo e(number_format($stock->current_stock)); ?></b></li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-sm text-purple-700 italic mt-2">Không có mã nào có rủi ro thiếu hụt trong chu kỳ này.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Cảnh báo Dead stock -->
            <div class="bg-stone-50 border border-stone-300 rounded-xl p-4 shadow-sm">
                <p class="text-xs font-bold text-stone-600 uppercase mb-2 flex items-center gap-1"><span>🕸️</span> Cảnh báo không sử dụng > 300 ngày</p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($deadStocks->count() > 0): ?>
                    <ul class="text-sm text-stone-900 space-y-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $deadStocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li>- <b><?php echo e($stock->name); ?></b> (<?php echo e($stock->code); ?>): Tồn đọng <b><?php echo e(number_format($stock->quantity)); ?></b> (cập nhật cuối <?php echo e($stock->updated_at->format('d/m/Y')); ?>)</li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-sm text-stone-700 italic">Hệ thống luân chuyển tốt, không có hàng tồn đọng lâu.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
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
<?php /**PATH D:\Project\resources\views/livewire/warehouse/stock-report.blade.php ENDPATH**/ ?>