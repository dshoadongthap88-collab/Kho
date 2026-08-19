<div>
    <div class="px-6 py-4">
        <h2 class="text-2xl font-bold text-gray-800">Báo Cáo Tổng Hợp Kho</h2>
        <p class="text-sm text-gray-500">Dashboard phân tích trực quan luân chuyển và tồn kho</p>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-6 mb-6">
        <!-- Bar Chart (Nhập - Xuất - Tồn) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Sản lượng Nhập - Xuất - Tồn (Top Giao dịch)</h3>
            <div id="inventoryBarChart" class="w-full" style="min-height: 350px;"></div>
        </div>

        <!-- Pie Chart (Danh mục) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Cơ cấu Tồn kho theo Danh mục</h3>
            <div id="categoryPieChart" class="w-full flex justify-center items-center" style="min-height: 350px;"></div>
        </div>
    </div>

    <!-- Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-6 mb-6">
        <!-- Pareto Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Pareto (80/20) - Luân chuyển vật tư</h3>
            <div id="paretoChart" class="w-full" style="min-height: 350px;"></div>
        </div>

        <!-- Top Receivers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Top Cán Bộ Lãnh Hàng</h3>
            <ul class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topReceivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $receiver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="py-3 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                <?php echo e($index + 1); ?>

                            </div>
                            <span class="font-medium text-gray-700"><?php echo e($receiver->receiver_contact); ?></span>
                        </div>
                        <span class="text-sm font-bold text-gray-900 bg-gray-100 px-2.5 py-1 rounded-lg"><?php echo e($receiver->total_orders); ?> phiếu</span>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <li class="py-3 text-center text-sm text-gray-500">Chưa có dữ liệu xuất kho.</li>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Dead Stock Alert Block -->
    <div class="px-6 pb-8">
        <div class="bg-white rounded-xl shadow-sm border <?php echo e(count($deadStocks) > 0 ? 'border-orange-200' : 'border-green-200'); ?> overflow-hidden">
            <div class="<?php echo e(count($deadStocks) > 0 ? 'bg-orange-50 border-b border-orange-100' : 'bg-green-50 border-b border-green-100'); ?> px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($deadStocks) > 0): ?>
                        <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <h3 class="text-lg font-bold text-orange-800">CẢNH BÁO KHÔNG SỬ DỤNG > 300 NGÀY</h3>
                    <?php else: ?>
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-lg font-bold text-green-800">Hệ thống luân chuyển tốt, không có hàng tồn đọng lâu.</h3>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($deadStocks) > 0): ?>
                    <div class="flex gap-2">
                        <button wire:click="selectAllDeadStocks(<?php echo e(json_encode(array_column($deadStocks, 'product_id'))); ?>)" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-1.5 rounded-md text-sm font-medium transition">
                            Chọn tất cả
                        </button>
                        <button wire:click="printDeadStocks" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-md text-sm font-medium transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            In báo cáo
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($deadStocks) > 0): ?>
                <!-- Thông báo lỗi khi chưa chọn -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                    <div class="bg-red-50 text-red-600 px-6 py-2 text-sm border-b border-red-100 font-medium">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider border-b">
                                <th class="px-6 py-3 w-10">#</th>
                                <th class="px-6 py-3">Mã vật tư</th>
                                <th class="px-6 py-3">Tên vật tư</th>
                                <th class="px-6 py-3 text-center">Tồn kho</th>
                                <th class="px-6 py-3 text-center">Giao dịch cuối</th>
                                <th class="px-6 py-3 text-center">Số ngày đắp chiếu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $deadStocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="hover:bg-orange-50/30 transition">
                                    <td class="px-6 py-3 text-center">
                                        <input type="checkbox" wire:click="toggleSelectProduct(<?php echo e($stock['product_id']); ?>)" 
                                            <?php echo e(in_array($stock['product_id'], $selectedProducts) ? 'checked' : ''); ?>

                                            class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500 cursor-pointer">
                                    </td>
                                    <td class="px-6 py-3 font-mono text-gray-600"><?php echo e($stock['product_code']); ?></td>
                                    <td class="px-6 py-3 font-bold text-gray-800"><?php echo e($stock['product_name']); ?></td>
                                    <td class="px-6 py-3 text-center font-medium"><?php echo e($stock['quantity']); ?> <?php echo e($stock['unit']); ?></td>
                                    <td class="px-6 py-3 text-center text-gray-500"><?php echo e($stock['last_transaction_date']); ?></td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded whitespace-nowrap">
                                            <?php echo e($stock['days_inactive']); ?> ngày
                                        </span>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Scripts for ApexCharts -->
        <?php
        $__scriptKey = '2891001837-2';
        ob_start();
    ?>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            
            // Render Bar Chart (Nhập Xuất Tồn)
            const barData = <?php echo json_encode($barChartData, 15, 512) ?>;
            if(barData.labels.length > 0) {
                new ApexCharts(document.querySelector("#inventoryBarChart"), {
                    series: barData.series,
                    chart: { type: 'bar', height: 350, toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 2 } },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    xaxis: { categories: barData.labels },
                    fill: { opacity: 1 },
                    colors: ['#008FFB', '#FF4560', '#00E396'],
                    tooltip: { y: { formatter: function (val) { return val + " đơn vị" } } }
                }).render();
            } else {
                document.querySelector("#inventoryBarChart").innerHTML = '<div class="flex h-full items-center justify-center text-gray-400">Không có dữ liệu</div>';
            }

            // Render Pie Chart (Danh mục)
            const pieData = <?php echo json_encode($pieChartData, 15, 512) ?>;
            if(pieData.labels.length > 0) {
                new ApexCharts(document.querySelector("#categoryPieChart"), {
                    series: pieData.series,
                    chart: { type: 'pie', height: 350 },
                    labels: pieData.labels,
                    theme: { palette: 'palette2' },
                    legend: { position: 'bottom' }
                }).render();
            } else {
                document.querySelector("#categoryPieChart").innerHTML = '<div class="flex h-full items-center justify-center text-gray-400">Không có dữ liệu</div>';
            }

            // Render Pareto Chart
            const pareto = <?php echo json_encode($paretoData, 15, 512) ?>;
            if(pareto.labels.length > 0) {
                // Calculate cumulative percentage for Pareto
                let total = pareto.series[0].data.reduce((a, b) => a + b, 0);
                let cumulative = 0;
                let paretoLineData = pareto.series[0].data.map(val => {
                    cumulative += val;
                    return parseFloat(((cumulative / total) * 100).toFixed(1));
                });

                pareto.series.push({
                    name: 'Tỷ lệ lũy kế (%)',
                    type: 'line',
                    data: paretoLineData
                });

                new ApexCharts(document.querySelector("#paretoChart"), {
                    series: pareto.series,
                    chart: { type: 'line', height: 350, toolbar: { show: false } },
                    stroke: { width: [0, 3] },
                    dataLabels: { enabled: true, enabledOnSeries: [1] },
                    labels: pareto.labels,
                    xaxis: { type: 'category' },
                    yaxis: [
                        { title: { text: 'Số lượng' } },
                        { opposite: true, title: { text: 'Tỷ lệ (%)' }, min: 0, max: 100 }
                    ],
                    colors: ['#775DD0', '#FF4560']
                }).render();
            } else {
                document.querySelector("#paretoChart").innerHTML = '<div class="flex h-full items-center justify-center text-gray-400">Không có dữ liệu</div>';
            }

            // Lắng nghe event mở tab in
            Livewire.on('open-print-tab', (data) => {
                window.open(data.url, '_blank');
            });
        });
    </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
</div>
<?php /**PATH D:\Project\resources\views\livewire\warehouse\reports\report-dashboard.blade.php ENDPATH**/ ?>