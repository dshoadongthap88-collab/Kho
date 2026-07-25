<div>
    <div class="mb-8">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Báo cáo Tổng hợp Từ Các Ngôi nhà</h1>
        <p class="text-slate-500 mt-2 text-lg">Phân tích chi tiết mức độ sử dụng tài sản và tình trạng tồn kho trên toàn hệ thống</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
            <div class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8 11V7a2 2 0 114 0v4h-4z"></path></svg>
            </div>
            <h3 class="text-indigo-100 font-medium mb-1 relative z-10">Tổng Số Ngôi Nhà</h3>
            <div class="text-5xl font-black relative z-10"><?php echo e($projects->count()); ?></div>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
            <div class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
            </div>
            <h3 class="text-emerald-100 font-medium mb-1 relative z-10">Tổng Số Nhân Sự</h3>
            <div class="text-5xl font-black relative z-10"><?php echo e($totalUsers); ?></div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
            <div class="absolute right-0 top-0 opacity-10 transform translate-x-4 -translate-y-4">
                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
            </div>
            <h3 class="text-purple-100 font-medium mb-1 relative z-10">Trạng thái</h3>
            <div class="text-4xl font-black relative z-10 mt-1">Đồng bộ</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <?php $pStats = $stats[$project->id] ?? []; ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
            <!-- Header của Ngôi nhà -->
            <div class="bg-slate-50 border-b border-slate-200 p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center text-lg shadow-inner">
                        🏢
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800"><?php echo e($project->name); ?></h2>
                        <p class="text-slate-500 font-medium text-xs mt-0.5">Mã: <span class="text-indigo-600"><?php echo e($project->code ?? "N/A"); ?></span> &bull; <span class="text-emerald-600"><?php echo e($pStats["users"] ?? 0); ?> NS</span></p>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <!-- Cảnh báo Tồn kho -->
                    <div class="bg-orange-50 border border-orange-200 rounded-lg px-2 py-1.5 flex items-center gap-2" title="Sắp hết tồn kho">
                        <span class="text-base">⚠️</span>
                        <div class="text-lg font-black text-orange-600 leading-none"><?php echo e($pStats["low_stock_count"] ?? 0); ?></div>
                    </div>
                    <!-- Cảnh báo Lâu không dùng -->
                    <div class="bg-red-50 border border-red-200 rounded-lg px-2 py-1.5 flex items-center gap-2" title="Chưa dùng > 300 ngày">
                        <span class="text-base">🛑</span>
                        <div class="text-lg font-black text-red-600 leading-none"><?php echo e($pStats["obsolete_stock_count"] ?? 0); ?></div>
                    </div>
                </div>
            </div>

            <!-- Thống kê Top 5 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-0 divide-y md:divide-y-0 md:divide-x divide-slate-100 flex-1">
                <!-- Top Tài sản -->
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded border bg-blue-50 text-blue-600 flex items-center justify-center text-xs">🚜</span>
                            Top 5 Tài Sản
                        </h3>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($pStats["top_assets"]) && count($pStats["top_assets"]) > 0): ?>
                        <div class="space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pStats["top_assets"]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="group flex items-center justify-between p-3 rounded-xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs font-bold group-hover:bg-blue-200 group-hover:text-blue-700">
                                        <?php echo e($index + 1); ?>

                                    </div>
                                    <div class="font-semibold text-slate-700 group-hover:text-blue-800 font-mono">
                                        <?php echo e($asset->asset_code); ?>

                                    </div>
                                </div>
                                <div class="text-sm font-bold text-slate-600 group-hover:text-blue-700 bg-slate-100 group-hover:bg-blue-200 px-3 py-1 rounded-full">
                                    <?php echo e(number_format($asset->usage_count)); ?> lần xuất
                                </div>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10 bg-slate-50 rounded-xl border border-slate-100 border-dashed">
                            <span class="text-4xl block mb-2">🤷‍♂️</span>
                            <p class="text-slate-500 font-medium">Chưa có dữ liệu xuất tài sản</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Top Vật tư -->
                <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-slate-700 flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded border bg-purple-50 text-purple-600 flex items-center justify-center text-xs">🔧</span>
                            Top 5 Vật Tư Tiêu Hao
                        </h3>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($pStats["top_materials"]) && count($pStats["top_materials"]) > 0): ?>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pStats["top_materials"]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="group flex items-center justify-between p-2 rounded-lg border border-slate-100 hover:border-purple-200 hover:bg-purple-50 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-md bg-slate-100 text-slate-500 flex items-center justify-center text-[10px] font-bold group-hover:bg-purple-200 group-hover:text-purple-700">
                                        <?php echo e($index + 1); ?>

                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-700 group-hover:text-purple-800 line-clamp-1">
                                            <?php echo e($mat->name); ?>

                                        </div>
                                        <div class="text-xs text-slate-500 font-mono mt-0.5">Mã: <?php echo e($mat->code); ?></div>
                                    </div>
                                </div>
                                <div class="text-sm font-bold text-slate-600 group-hover:text-purple-700 bg-slate-100 group-hover:bg-purple-200 px-3 py-1 rounded-full flex-shrink-0">
                                    <?php echo e(number_format($mat->total_used)); ?>

                                </div>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-10 bg-slate-50 rounded-xl border border-slate-100 border-dashed">
                            <span class="text-4xl block mb-2">📦</span>
                            <p class="text-slate-500 font-medium">Chưa có dữ liệu xuất vật tư</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
</div><?php /**PATH D:\Project\resources\views/livewire/hr/global-report.blade.php ENDPATH**/ ?>