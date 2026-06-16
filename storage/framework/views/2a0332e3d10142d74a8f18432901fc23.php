<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Báo cáo Tổng hợp Từ Các Ngôi nhà</h1>
        <p class="text-sm text-slate-500">Xem số liệu thống kê xuyên suốt toàn bộ hệ thống</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
            <h3 class="text-indigo-100 font-medium mb-1">Tổng Số Ngôi Nhà (Dự Án)</h3>
            <div class="text-4xl font-black"><?php echo e($projects->count()); ?></div>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg">
            <h3 class="text-emerald-100 font-medium mb-1">Tổng Số Nhân Sự (Tài khoản)</h3>
            <div class="text-4xl font-black"><?php echo e($totalUsers); ?></div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg">
            <h3 class="text-purple-100 font-medium mb-1">Hệ thống đang chạy</h3>
            <div class="text-4xl font-black">Ổn định</div>
        </div>
    </div>

    <h2 class="text-xl font-bold text-slate-800 mb-4">Chi tiết từng Ngôi nhà</h2>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col h-full">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">🏢</span>
                        <?php echo e($project->name); ?>

                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Mã DA: <?php echo e($project->code ?? 'N/A'); ?></p>
                </div>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">Đang vận hành</span>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mt-auto">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="text-sm text-slate-500 font-medium mb-1">Nhân sự tham gia</div>
                    <div class="text-2xl font-bold text-slate-800"><?php echo e($stats[$project->id]['users'] ?? 0); ?> <span class="text-sm font-normal text-slate-500">người</span></div>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div class="text-sm text-slate-500 font-medium mb-1">Giá trị tồn kho (Ước tính)</div>
                    <div class="text-xl font-bold text-slate-800"><?php echo e(number_format($stats[$project->id]['stock_value'] ?? 0, 0, ',', '.')); ?> <span class="text-sm font-normal text-slate-500">đ</span></div>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-slate-100 flex justify-between items-center">
                <span class="text-sm text-slate-500"><?php echo e($stats[$project->id]['active_orders'] ?? 0); ?> đơn hàng đang xử lý</span>
                <button class="text-indigo-600 font-bold text-sm hover:text-indigo-800 transition-colors">Xem chi tiết &rarr;</button>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
</div>
<?php /**PATH D:\Project\resources\views/livewire/hr/global-report.blade.php ENDPATH**/ ?>