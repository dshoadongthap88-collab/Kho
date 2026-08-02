<div class="px-4">
    <!-- Thống kê tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl font-bold">📦</div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Tổng thiết bị</p>
                <p class="text-2xl font-black text-slate-800"><?php echo e($totalAssets); ?></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl font-bold">✅</div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Đang hoạt động</p>
                <p class="text-2xl font-black text-slate-800"><?php echo e($activeAssets); ?></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xl font-bold">🔧</div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Đang sửa chữa</p>
                <p class="text-2xl font-black text-slate-800"><?php echo e($maintenanceAssets); ?></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-yellow-300 shadow-sm flex items-center gap-4 bg-yellow-50">
            <div class="w-12 h-12 rounded-full bg-yellow-200 flex items-center justify-center text-yellow-700 text-xl font-bold">⚠️</div>
            <div>
                <p class="text-sm text-yellow-700 font-medium">Sắp bảo dưỡng</p>
                <p class="text-2xl font-black text-yellow-800"><?php echo e($warningCount); ?></p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-red-300 shadow-sm flex items-center gap-4 bg-red-50">
            <div class="w-12 h-12 rounded-full bg-red-200 flex items-center justify-center text-red-700 text-xl font-bold">🔴</div>
            <div>
                <p class="text-sm text-red-700 font-medium">Quá hạn</p>
                <p class="text-2xl font-black text-red-800"><?php echo e($overdueCount); ?></p>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2">
            <span>✅</span> <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-800">Danh Sách Kế Hoạch Bảo Dưỡng (Cảnh Báo)</h2>
        <div class="w-full md:w-1/3 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm mã, tên thiết bị..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã Kế Hoạch</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Thiết bị</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hạng Mục</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Odo / Giờ Hiện Tại</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Odo / Giờ Định Mức</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Cảnh báo</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $currentVal = $plan->asset ? max($plan->asset->current_odo, $plan->asset->current_hours) : 0; // Đơn giản hoá hiển thị
                            
                            $isOverdue = $currentVal >= $plan->maintenance_odo;
                            $isWarning = !$isOverdue && ($currentVal >= $plan->maintenance_odo - 50); // Cách 50 đv
                            
                            $rowClass = $isOverdue ? 'bg-red-50' : ($isWarning ? 'bg-yellow-50' : 'bg-white');
                            $badgeClass = $isOverdue ? 'bg-red-200 text-red-800 border-red-300' : ($isWarning ? 'bg-yellow-200 text-yellow-800 border-yellow-300' : 'bg-green-100 text-green-800 border-green-200');
                            $badgeText = $isOverdue ? 'Quá Hạn' : ($isWarning ? 'Sắp Đến Hạn' : 'Bình Thường');
                        ?>
                        <tr class="<?php echo e($rowClass); ?> transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900"><?php echo e($plan->plan_code); ?></td>
                            <td class="px-6 py-4 text-sm font-semibold text-indigo-700">
                                <?php echo e($plan->asset->asset_code ?? ''); ?><br>
                                <span class="text-xs text-gray-500"><?php echo e($plan->asset->name ?? ''); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800"><?php echo e($plan->category); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right text-gray-900"><?php echo e(number_format($currentVal)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right text-indigo-600"><?php echo e(number_format($plan->maintenance_odo)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full border <?php echo e($badgeClass); ?>"><?php echo e($badgeText); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="markAsCompleted(<?php echo e($plan->id); ?>)" class="text-green-700 hover:text-green-900 mx-2 bg-green-100 border border-green-200 px-3 py-1.5 rounded-lg shadow-sm" title="Đã hoàn thành">Đã bảo dưỡng ✅</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <p class="mb-2 text-3xl">🎉</p>
                                <p>Không có kế hoạch bảo dưỡng nào đang chờ.</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            <?php echo e($plans->links()); ?>

        </div>
    </div>
</div>
<?php /**PATH D:\Project\resources\views\livewire\warehouse\maintenance-dashboard.blade.php ENDPATH**/ ?>