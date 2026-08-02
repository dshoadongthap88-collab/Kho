<div class="bg-white p-6 rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Cập Nhật ODO & Giờ Máy Hàng Ngày</h2>
        
        <div class="flex items-center space-x-4">
            <div class="flex items-center">
                <label for="autoCronToggle" class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" id="autoCronToggle" class="sr-only" wire:model.live="autoCronEnabled">
                        <div class="block w-10 h-6 rounded-full transition-colors <?php echo e($autoCronEnabled ? 'bg-emerald-500' : 'bg-rose-500'); ?>"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform <?php echo e($autoCronEnabled ? 'translate-x-4' : ''); ?>"></div>
                    </div>
                    <div class="ml-3 text-gray-700 font-medium">Auto Cron (00:01)</div>
                </label>
            </div>
            
            <input type="date" wire:model.live="selectedDate" class="border-gray-300 rounded-md shadow-sm">
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="mb-4 p-4 text-green-700 bg-green-100 rounded-lg">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($dailyRecords)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Chưa có dữ liệu cho ngày này</h3>
            <p class="mt-1 text-sm text-gray-500">Bấm nút bên dưới để tự động lấy danh sách tài sản và tạo bản ghi chờ duyệt.</p>
            <div class="mt-6">
                <button wire:click="generateNewDay" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Tạo ngày mới
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto border rounded-lg mb-4">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-sky-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-sky-700 uppercase tracking-wider">Trạng Thái</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-sky-700 uppercase tracking-wider">Mã TS</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-sky-700 uppercase tracking-wider">Tên Tài Sản</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-sky-700 uppercase tracking-wider">ODO cũ</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-sky-700 uppercase tracking-wider">ODO mới</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-sky-700 uppercase tracking-wider">Giờ hoạt động</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-sky-700 uppercase tracking-wider">Người cập nhật</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-sky-700 uppercase tracking-wider">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $dailyRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="<?php echo e($record['status'] == 'approved' ? 'bg-gray-50' : ''); ?>">
                        <td class="px-3 py-3 whitespace-nowrap text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($record['status'] == 'approved'): ?>
                                <span class="px-2 py-1 text-xs font-bold rounded bg-gray-200 text-gray-600">Đã chốt</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-bold rounded bg-yellow-100 text-yellow-800">Chờ duyệt</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900 font-bold"><?php echo e($record['asset_code']); ?></td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 font-medium"><?php echo e($record['asset_name']); ?></td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><?php echo e(number_format($record['old_odo'], 1)); ?></td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-sky-600 font-bold">
                            <?php echo e($record['status'] == 'approved' ? number_format($record['new_odo'], 1) : number_format($record['old_odo'] + (float)$record['hours_diff'], 1)); ?>

                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                            <input type="number" wire:model.live="dailyRecords.<?php echo e($index); ?>.hours_diff" <?php echo e($record['status'] == 'approved' ? 'disabled' : ''); ?> class="w-20 border-gray-300 rounded-md shadow-sm text-sm" step="0.1">
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                            <input type="text" wire:model="dailyRecords.<?php echo e($index); ?>.operator" <?php echo e($record['status'] == 'approved' ? 'disabled' : ''); ?> class="w-full border-gray-300 rounded-md shadow-sm text-sm" placeholder="Nhập tên">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($record['status'] == 'approved' && $record['updated_by_name']): ?>
                                <div class="text-xs text-sky-600 mt-1">Bởi: <?php echo e($record['updated_by_name']); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-center text-sm font-medium">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($record['status'] != 'approved'): ?>
                                <button wire:click="updateSingleRecord(<?php echo e($index); ?>)" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-md shadow-sm transition text-xs font-bold">CẬP NHẬT</button>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">Đã khóa</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
            $hasPending = collect($dailyRecords)->where('status', 'pending')->count() > 0;
        ?>

        <div class="flex justify-between items-center mt-4">
            <div>
                <button wire:click="cancelUpdate" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md font-bold hover:bg-gray-300 transition">HỦY CẬP NHẬT</button>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPending): ?>
            <div>
                <button wire:click="updateBatch" class="px-6 py-2 bg-green-600 text-white rounded-md font-bold hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow transition">
                    CẬP NHẬT TẤT CẢ
                </button>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\Project\resources\views\components\maintenance\daily-odo-manager.blade.php ENDPATH**/ ?>