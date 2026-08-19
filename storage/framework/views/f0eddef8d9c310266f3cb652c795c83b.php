<div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
    <div class="mb-4 p-2 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-3 shadow-sm no-print">
        <span class="text-xl">✅</span> <span class="font-semibold text-sm"><?php echo session('success'); ?></span>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
    <div class="mb-4 p-2 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3 shadow-sm no-print">
        <span class="text-xl">❌</span> <span class="font-semibold text-sm"><?php echo e(session('error')); ?></span>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('info')): ?>
    <div class="mb-4 p-2 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl flex items-center gap-3 shadow-sm no-print">
        <span class="text-xl">ℹ️</span> <span class="font-semibold text-sm"><?php echo e(session('info')); ?></span>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="flex flex-wrap gap-2 mb-6 no-print">
        <button wire:click="$set('activeTab', 'stocktake')"
            class="px-5 py-2 rounded-lg text-sm font-bold transition <?php echo e($activeTab === 'stocktake' ? 'bg-indigo-700 text-white shadow-md' : 'bg-white text-gray-600 border hover:bg-gray-50'); ?>">
            📋 Phiếu kiểm kê
        </button>
        <button wire:click="$set('activeTab', 'daily')"
            class="px-5 py-2 rounded-lg text-sm font-bold transition <?php echo e($activeTab === 'daily' ? 'bg-orange-600 text-white shadow-md' : 'bg-white text-gray-600 border hover:bg-gray-50'); ?>">
            ☀️ Kiểm kê hàng ngày
        </button>
        <button wire:click="$set('activeTab', 'periodic')"
            class="px-5 py-2 rounded-lg text-sm font-bold transition <?php echo e($activeTab === 'periodic' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-gray-600 border hover:bg-gray-50'); ?>">
            📊 Kiểm kê định kỳ & Toàn bộ (Excel)
        </button>
        <button wire:click="$set('activeTab', 'sync')"
            class="px-5 py-2 rounded-lg text-sm font-bold transition <?php echo e($activeTab === 'sync' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border hover:bg-gray-50'); ?>">
            🔄 Đồng bộ tồn kho
        </button>
        <button wire:click="$set('activeTab', 'chat_ai')"
            class="px-5 py-2 rounded-lg text-sm font-bold transition <?php echo e($activeTab === 'chat_ai' ? 'bg-purple-700 text-white shadow-md' : 'bg-white text-gray-600 border hover:bg-gray-50'); ?>">
            💬 Chat AI Kiểm kê
        </button>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'stocktake'): ?>
    <div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCount): ?>
        <div class="bg-white rounded-xl shadow border print:border-none print:shadow-none print:mb-0 mb-6">
            <div class="px-5 py-4 border-b print:border-none flex items-center justify-between">
                <div>
                    <div class="hidden print:block mb-6">
                        <div style="font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; text-align: left; color: black;">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ HẠ TẦNG V-ALPHA</div>
                        <div style="font-size: 14px; margin-bottom: 20px; font-style: italic; text-align: left; color: black;">Dự án: <?php echo e(\App\Models\Project::find(session('current_house', 1))?->name ?? 'Nội bộ'); ?></div>
                    </div>
                    <h2 class="text-base font-black text-indigo-800 uppercase print:text-xl print:text-black">📋 PHIẾU KIỂM KÊ KHO</h2>
                    <p class="text-sm font-bold text-gray-500 mt-1">Mã phiếu: <?php echo e($currentCount->code); ?></p>
                    <p class="text-xs text-gray-400 mt-0.5 no-print">Nhập số lượng kiểm đếm thực tế vào cột "Thực tế". Hệ thống sẽ tính chênh lệch tự động.</p>
                    <p class="text-xs text-gray-400 hidden print:block mt-1">Ngày in: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
                </div>
                <div class="flex gap-2 no-print">
                    <button onclick="window.print()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-black transition cursor-pointer flex items-center gap-1">
                        🖨️ In phiếu
                    </button>
                    <button wire:click="confirmStockCount(<?php echo e($currentCount->id); ?>)"
                        wire:confirm="Xác nhận hoàn tất kiểm kê? Hệ thống sẽ tự động điều chỉnh tồn kho theo số liệu thực tế."
                        wire:loading.attr="disabled"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-black transition cursor-pointer flex items-center gap-1">
                        <span wire:loading.remove wire:target="confirmStockCount">✅ Xác nhận</span>
                        <span wire:loading wire:target="confirmStockCount" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    </button>
                    <button wire:click="cancelStockCount(<?php echo e($currentCount->id); ?>)"
                        wire:confirm="Hủy phiếu kiểm kê này?"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-bold transition cursor-pointer">
                        ✖ Hủy phiếu
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Vị trí</th>
                            <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">TÊN VẬT TƯ / MÃ VẬT TƯ</th>
                            <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Tồn hệ thống</th>
                            <th class="px-2 py-2 text-center text-[10px] font-bold text-yellow-600 uppercase no-print">Thực tế (Nhập)</th>
                            <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-400 uppercase hidden print:table-cell">Thực tế</th>
                            <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Chênh lệch</th>
                            <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase no-print">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $currentCount->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-gray-50 <?php echo e($item->difference != 0 && $item->actual_quantity !== null ? ($item->difference < 0 ? 'bg-red-50' : 'bg-green-50') : ''); ?>">
                            <td class="px-4 py-2 text-xs font-bold text-indigo-700"><?php echo e($item->product->location ?? '-'); ?></td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-800">
                                <div class="font-bold"><?php echo e($item->product->name ?? '-'); ?></div>
                                <div class="text-[10px] text-gray-400 font-mono mt-0.5"><?php echo e($item->product->code ?? '-'); ?></div>
                            </td>
                            <td class="px-4 py-2 text-center text-sm font-black text-gray-700"><?php echo e(number_format($item->system_quantity)); ?></td>
                            <td class="px-4 py-2 text-center no-print">
                                <input type="number" 
                                    value="<?php echo e($item->actual_quantity); ?>"
                                    wire:change="updateActualQty(<?php echo e($item->id); ?>, $event.target.value)"
                                    class="w-24 text-center border border-yellow-300 rounded-lg px-1.5 py-1 text-[11px] text-xs font-bold focus:ring-yellow-500 focus:border-yellow-500 bg-yellow-50"
                                    placeholder="0">
                            </td>
                            <td class="px-4 py-2 text-center font-black text-indigo-600 hidden print:table-cell">
                                <?php echo e($item->actual_quantity !== null ? number_format($item->actual_quantity) : ''); ?>

                            </td>
                            <td class="px-4 py-2 text-center text-sm font-black 
                                <?php echo e($item->difference < 0 ? 'text-red-600' : ($item->difference > 0 ? 'text-green-600' : 'text-gray-400')); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->actual_quantity !== null): ?>
                                    <?php echo e($item->difference > 0 ? '+' : ''); ?><?php echo e(number_format($item->difference)); ?>

                                <?php else: ?>
                                    <span class="text-gray-300 text-xs">Chưa nhập</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-400 no-print"><?php echo e($item->note); ?></td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="hidden print:grid grid-cols-3 text-center mt-12 gap-2 pb-10">
                <div>
                    <p class="font-bold text-sm text-black">Thủ kho</p>
                    <p class="text-[10px] italic text-gray-500">(Ký, ghi rõ họ tên)</p>
                </div>
                <div>
                    <p class="font-bold text-sm text-black">Nhân viên kiểm kê</p>
                    <p class="text-[10px] italic text-gray-500">(Ký, ghi rõ họ tên)</p>
                </div>
                <div>
                    <p class="font-bold text-sm text-black">Quản lý kho</p>
                    <p class="text-[10px] italic text-gray-500">(Ký, ghi rõ họ tên)</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        
        <div class="bg-white rounded-xl border shadow-sm p-5 mb-6 no-print">
            <h2 class="text-sm font-black text-gray-700 uppercase mb-3">➕ Tạo phiếu kiểm kê mới</h2>
            <p class="text-xs text-gray-400 mb-3">Hệ thống sẽ tự động tải toàn bộ danh sách sản phẩm và số tồn kho hiện tại vào phiếu kiểm kê để bạn đối chiếu thực tế.</p>
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Ghi chú phiếu kiểm kê</label>
                    <input type="text" wire:model="countNote" placeholder="VD: Kiểm kê tháng 5/2026..." class="w-full rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
                </div>
                <button wire:click="createNewStockCount('full')" wire:loading.attr="disabled"
                    class="px-6 py-2 bg-indigo-700 hover:bg-indigo-800 text-white rounded-lg text-sm font-black transition shadow cursor-pointer">
                    <span wire:loading.remove wire:target="createNewStockCount">📋 Tạo phiếu kiểm kê Toàn bộ</span>
                    <span wire:loading wire:target="createNewStockCount">Đang tải...</span>
                </button>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="bg-white rounded-xl shadow border overflow-hidden no-print">
            <div class="px-5 py-3 border-b flex flex-wrap justify-between items-center gap-2">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-bold text-gray-700">Lịch sử phiếu kiểm kê</h3>
                    <div class="flex items-center gap-1 ml-4 no-print" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'bulk-actions-toolbar-container'; ?>wire:key="bulk-actions-toolbar-container">
                        <span class="text-[10px] font-bold <?php echo e(count($selectedStockCounts) > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400'); ?> px-2 py-1 rounded-full transition-colors">
                            Đã chọn <?php echo e(count($selectedStockCounts)); ?>

                        </span>
                        
                        <button type="button" 
                            wire:click="bulkPrint"
                            wire:loading.attr="disabled"
                            <?php echo e(count($selectedStockCounts) == 0 ? 'disabled' : ''); ?>

                            class="p-1.5 rounded-lg transition-all <?php echo e(count($selectedStockCounts) > 0 ? 'bg-blue-50 text-blue-600 hover:bg-blue-100 cursor-pointer shadow-sm' : 'bg-gray-50 text-gray-300 cursor-not-allowed opacity-50'); ?>" 
                            title="In các phiếu đã chọn">
                            <span wire:loading.remove wire:target="bulkPrint">🖨️</span>
                            <span wire:loading wire:target="bulkPrint" class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin block"></span>
                        </button>

                        <button type="button" 
                            x-on:click="if(confirm('Bạn có chắc chắn muốn XÓA các phiếu đã chọn?')) $wire.bulkDelete()"
                            wire:loading.attr="disabled"
                            <?php echo e(count($selectedStockCounts) == 0 ? 'disabled' : ''); ?>

                            class="p-1.5 rounded-lg transition-all <?php echo e(count($selectedStockCounts) > 0 ? 'bg-red-50 text-red-600 hover:bg-red-100 cursor-pointer shadow-sm' : 'bg-gray-50 text-gray-300 cursor-not-allowed opacity-50'); ?>" 
                            title="Xóa các phiếu đã chọn">
                            <span wire:loading.remove wire:target="bulkDelete">🗑️</span>
                            <span wire:loading wire:target="bulkDelete" class="w-4 h-4 border-2 border-red-500 border-t-transparent rounded-full animate-spin block"></span>
                        </button>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedStockCounts) === 1): ?>
                        <?php $firstId = reset($selectedStockCounts); ?>
                        <button type="button" wire:click="editStockCount(<?php echo e($firstId); ?>)" 
                            wire:loading.attr="disabled"
                            class="p-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-all cursor-pointer shadow-sm" 
                            title="Tiếp tục chỉnh sửa phiếu này">
                            ✏️
                        </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <input type="text" wire:model.live="listSearch" placeholder="Tìm mã phiếu..." class="rounded-lg border-gray-200 shadow-sm text-xs focus:ring-indigo-500 w-48">
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-center w-10">
                            <input type="checkbox" 
                                <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'select-all-checkbox'; ?>wire:key="select-all-checkbox"
                                wire:click="toggleSelectAll([<?php echo e(implode(',', $stockCounts->pluck('id')->toArray())); ?>])"
                                <?php echo e(count($selectedStockCounts) >= count($stockCounts) && count($selectedStockCounts) > 0 ? 'checked' : ''); ?>

                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Mã phiếu</th>
                        <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Trạng thái</th>
                        <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Ghi chú</th>
                        <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Người tạo</th>
                        <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Ngày tạo</th>
                        <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-400 uppercase no-print">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stockCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $statusMap = [
                            'pending' => ['label' => '⏳ Đang kiểm', 'class' => 'bg-yellow-100 text-yellow-700'],
                            'completed' => ['label' => '✅ Hoàn thành', 'class' => 'bg-green-100 text-green-700'],
                            'cancelled' => ['label' => '✖ Đã hủy', 'class' => 'bg-red-100 text-red-700'],
                        ];
                        $s = $statusMap[$sc->status] ?? ['label' => $sc->status, 'class' => 'bg-gray-100 text-gray-600'];
                    ?>
                    <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'sc-row-'.e($sc->id).''; ?>wire:key="sc-row-<?php echo e($sc->id); ?>" class="hover:bg-gray-50 transition-colors <?php echo e(in_array($sc->id, $selectedStockCounts) ? 'bg-indigo-50/50' : ''); ?>">
                        <td class="px-2 py-1.5 text-center">
                            <input type="checkbox" wire:model.live="selectedStockCounts" value="<?php echo e($sc->id); ?>" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-2 py-1.5 text-sm font-bold text-indigo-700"><?php echo e($sc->code); ?></td>
                        <td class="px-2 py-1.5 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo e($s['class']); ?>"><?php echo e($s['label']); ?></span>
                        </td>
                        <td class="px-2 py-1.5 text-xs text-gray-500"><?php echo e($sc->note); ?></td>
                        <td class="px-2 py-1.5 text-xs text-gray-600">👤 <?php echo e($sc->creator->name ?? '-'); ?></td>
                        <td class="px-2 py-1.5 text-xs text-gray-400 font-mono"><?php echo e($sc->created_at->format('d/m/Y H:i')); ?></td>
                        <td class="px-2 py-1.5 text-center no-print">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="editStockCount(<?php echo e($sc->id); ?>)" 
                                    wire:loading.attr="disabled"
                                    class="p-1 text-indigo-600 hover:bg-indigo-50 rounded transition" title="Sửa">
                                    ✏️
                                </button>
                                <button type="button"
                                    x-on:click="if(confirm('Xóa phiếu <?php echo e($sc->code); ?>?')) $wire.deleteStockCount(<?php echo e($sc->id); ?>)"
                                    class="p-1 text-red-600 hover:bg-red-50 rounded transition" title="Xóa">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 italic text-sm">Chưa có phiếu kiểm kê nào. Tạo phiếu đầu tiên để bắt đầu!</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
            <div class="px-4 py-3 border-t"><?php echo e($stockCounts->links()); ?></div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'daily'): ?>
    <div class="bg-white rounded-xl border shadow-sm p-8 text-center max-w-2xl mx-auto my-12">
        <div class="w-20 h-20 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">☀️</div>
        <h2 class="text-2xl font-black text-gray-800 uppercase mb-4">Kiểm kê hàng ngày</h2>
        <p class="text-gray-500 mb-8 leading-relaxed">
            Hệ thống sẽ tự động chọn ngẫu nhiên <b>10 vật tư</b> dựa trên vị trí kho và quy tắc chống trùng lặp (không chọn lại vật tư đã kiểm trong 7 ngày qua).
        </p>
        <button wire:click="createDailyStockCount" wire:loading.attr="disabled"
            class="px-8 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-black shadow-lg transition-all transform hover:scale-105 active:scale-95 flex items-center gap-3 mx-auto cursor-pointer">
            <span wire:loading.remove wire:target="createDailyStockCount">📋 Tạo 10 mã kiểm kê ngay</span>
            <span wire:loading wire:target="createDailyStockCount" class="flex items-center gap-2">
                <span class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Đang chọn mã...
            </span>
        </button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'periodic'): ?>
    <div class="space-y-6">
        <div class="bg-white rounded-xl border shadow-sm p-2">
            <h2 class="text-base font-black text-emerald-800 uppercase mb-4">📊 Kiểm kê định kỳ & Toàn bộ (Excel)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="bg-emerald-50 rounded-xl p-5 border border-emerald-100">
                    <h3 class="text-sm font-bold text-emerald-900 mb-2">Bước 1: Tải file mẫu</h3>
                    <p class="text-xs text-emerald-700 mb-4">Chọn xuất toàn bộ hoặc lọc theo vị trí kho để kiểm kê.</p>
                    
                    <div class="mb-4">
                        <input type="text" wire:model.live="locationFilter" placeholder="Lọc theo vị trí (VD: A1, B...)" 
                            class="w-full text-xs rounded-lg border-emerald-200 focus:ring-emerald-500 mb-2">
                    </div>

                    <button wire:click="exportPeriodicTemplate" 
                        class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-black shadow-md transition-all flex items-center justify-center gap-2">
                        📥 Xuất mẫu Excel (Toàn bộ)
                    </button>
                </div>

                
                <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-inner">
                    <h3 class="text-sm font-bold text-gray-700 mb-2">Bước 2: Tải lên kết quả</h3>
                    <p class="text-xs text-gray-400 mb-4">Sau khi điền số lượng thực tế vào cột tương ứng, hãy tải file lên để hệ thống cập nhật vào phiếu.</p>
                    
                    <div class="space-y-3">
                        <input type="file" wire:model="excelFile" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['excelFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-[10px] font-bold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <button wire:click="importPeriodicResults" wire:loading.attr="disabled"
                            class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-black shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="importPeriodicResults">📤 Tải lên & Cập nhật</span>
                            <span wire:loading wire:target="importPeriodicResults" class="flex items-center gap-2">
                                <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Đang xử lý...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCount && $currentCount->type === 'periodic'): ?>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-2 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📝</span>
                <div>
                    <p class="text-sm font-bold text-blue-800">Phiếu kiểm kê hiện tại: <?php echo e($currentCount->code); ?></p>
                    <p class="text-xs text-blue-600">Bạn có thể quay lại tab "Phiếu kiểm kê" để xem chi tiết và xác nhận điều chỉnh.</p>
                </div>
            </div>
            <button wire:click="$set('activeTab', 'stocktake')" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-black shadow-sm">Xem chi tiết</button>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'sync'): ?>
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b bg-gray-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-base font-black text-blue-800 uppercase">🔄 Đồng bộ tồn kho hệ thống</h2>
                <p class="text-xs text-gray-400 mt-1">So sánh số lượng tồn hiện tại với tổng lịch sử giao dịch (Nhập - Xuất) để tìm sai lệch.</p>
            </div>
            <button wire:click="checkInventorySync" wire:loading.attr="disabled"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-black shadow-md transition-all">
                🔍 Kiểm tra sai lệch
            </button>
        </div>

        <div class="p-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($syncCheckResults)): ?>
                <div class="py-12 text-center text-gray-400 italic">
                    Nhấn "Kiểm tra sai lệch" để bắt đầu quá trình đối soát dữ liệu.
                </div>
            <?php else: ?>
                <div class="mb-4 flex items-center justify-between">
                    <span class="text-sm font-bold text-red-600">⚠️ Tìm thấy <?php echo e(count($syncCheckResults)); ?> sản phẩm có sai lệch dữ liệu</span>
                    <button wire:click="syncInventory" wire:confirm="Xác nhận đồng bộ lại toàn bộ tồn kho theo lịch sử giao dịch?"
                        class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-black shadow-lg animate-pulse">
                        🚀 Đồng bộ ngay
                    </button>
                </div>

                <div class="border rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Sản phẩm</th>
                                <th class="px-4 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Tồn thực tế (Stored)</th>
                                <th class="px-4 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Tính toán (History)</th>
                                <th class="px-4 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Chênh lệch</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $syncCheckResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="px-2 py-1.5">
                                    <div class="text-xs font-bold text-gray-800"><?php echo e($res['product_name']); ?></div>
                                    <div class="text-[10px] text-gray-400 font-mono"><?php echo e($res['product_code']); ?></div>
                                </td>
                                <td class="px-2 py-1.5 text-center font-bold"><?php echo e(number_format($res['stored_qty'])); ?></td>
                                <td class="px-2 py-1.5 text-center font-bold text-indigo-600"><?php echo e(number_format($res['calculated_qty'])); ?></td>
                                <td class="px-2 py-1.5 text-center font-black text-red-600"><?php echo e(number_format($res['difference'])); ?></td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isPrintingMultiple): ?>
    <div class="fixed inset-0 z-[9999] bg-white overflow-y-auto p-8 no-print-bg">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-8 no-print">
                <h1 class="text-2xl font-black text-gray-800">🖨️ IN HÀNG LOẠT (<?php echo e(count($printBatchCodes)); ?> PHIẾU)</h1>
                <div class="flex gap-2">
                    <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-black shadow-lg">IN NGAY</button>
                    <button wire:click="$set('isPrintingMultiple', false)" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold">ĐÓNG</button>
                </div>
            </div>

            <div class="space-y-12">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $printBatchCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $currentProject = \App\Models\Project::find(session('current_house', 1));
                    $projectName = $currentProject ? $currentProject->name : 'Nội bộ';
                ?>
                <div class="border-b-4 border-double border-gray-300 pb-8 last:border-0 print:border-none">
                    <div style="font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; text-align: left; color: black;">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ HẠ TẦNG V-ALPHA</div>
                    <div style="font-size: 14px; margin-bottom: 20px; font-style: italic; text-align: left; color: black;">Dự án: <?php echo e($projectName); ?></div>
                    
                    <div class="flex justify-between items-end mb-4">
                        <div>
                            <h2 class="text-xl font-black text-gray-900">PHIẾU KIỂM KÊ KHO</h2>
                            <p class="text-sm font-bold text-gray-500">Mã phiếu: <?php echo e($code); ?></p>
                        </div>
                        <div class="text-right text-xs text-gray-400">
                            Ngày in: <?php echo e(now()->format('d/m/Y H:i')); ?>

                        </div>
                    </div>

                    <table class="w-full border-collapse border border-gray-300 text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-300 px-3 py-2 text-left">Vị trí</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">TÊN VẬT TƯ / MÃ VẬT TƯ</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">Hệ thống</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">Thực tế</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">Chênh lệch</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = collect($printBatchItems)->where('count_code', $code); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="border border-gray-300 px-3 py-1 font-mono text-xs font-bold text-indigo-700"><?php echo e($item['location']); ?></td>
                                <td class="border border-gray-300 px-3 py-1 text-xs">
                                    <div class="font-bold text-gray-800"><?php echo e($item['product_name']); ?></div>
                                    <div class="text-[10px] text-gray-400 font-mono mt-0.5"><?php echo e($item['product_code']); ?></div>
                                </td>
                                <td class="border border-gray-300 px-3 py-1 text-center font-bold"><?php echo e(number_format($item['system_qty'])); ?></td>
                                <td class="border border-gray-300 px-3 py-1 text-center font-black text-indigo-600"><?php echo e(number_format($item['actual_qty'])); ?></td>
                                <td class="border border-gray-300 px-3 py-1 text-center font-bold <?php echo e($item['difference'] < 0 ? 'text-red-600' : 'text-green-600'); ?>">
                                    <?php echo e($item['difference'] > 0 ? '+' : ''); ?><?php echo e(number_format($item['difference'])); ?>

                                </td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                    
                    
                    <div class="grid grid-cols-3 text-center mt-12 gap-2 pb-10">
                        <div>
                            <p class="font-bold text-sm text-black">Thủ kho</p>
                            <p class="text-[10px] italic text-gray-500">(Ký, ghi rõ họ tên)</p>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-black">Nhân viên kiểm kê</p>
                            <p class="text-[10px] italic text-gray-500">(Ký, ghi rõ họ tên)</p>
                        </div>
                        <div>
                            <p class="font-bold text-sm text-black">Quản lý kho</p>
                            <p class="text-[10px] italic text-gray-500">(Ký, ghi rõ họ tên)</p>
                        </div>
                    </div>
                </div>
                <div class="page-break"></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .fixed { position: static !important; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
    </style>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'chat_ai'): ?>
    <div class="bg-white rounded-xl border shadow-sm max-w-4xl mx-auto overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-purple-50 border-b border-purple-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🤖</span>
                <div>
                    <h3 class="text-sm font-bold text-purple-900">Trợ lý Kiểm kê AI (ERP AI Agent)</h3>
                    <p class="text-[10px] text-purple-600">Đối thoại trực tiếp với AI để lấy danh sách cần kiểm kê và cập nhật số liệu bằng tiếng Việt.</p>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCountId): ?>
                <?php $activeCount = \App\Models\StockCount::find($currentCountId); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeCount): ?>
                <span class="px-2.5 py-1 bg-purple-100 border border-purple-200 text-purple-800 text-xs font-bold rounded-lg flex items-center gap-1.5 animate-pulse">
                    🎯 Đang ghi nhận phiếu: <?php echo e($activeCount->code); ?>

                </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Message logs -->
        <div class="p-2 h-[400px] overflow-y-auto space-y-4 bg-slate-50 flex flex-col justify-end">
            <div class="space-y-4 overflow-y-auto pr-2 flex-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $chatMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="flex <?php echo e($msg['sender'] === 'user' ? 'justify-end' : 'justify-start'); ?> items-start gap-2.5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg['sender'] === 'ai'): ?>
                            <div class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center text-sm font-bold shrink-0">AI</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex flex-col w-full max-w-[450px] leading-1.5 p-2 border-gray-200 rounded-r-xl rounded-bl-xl <?php echo e($msg['sender'] === 'user' ? 'bg-indigo-600 text-white rounded-l-xl rounded-br-xl' : 'bg-white text-gray-800'); ?> shadow-sm">
                            <p class="text-sm font-normal whitespace-pre-wrap"><?php echo \Illuminate\Mail\Markdown::parse($msg['text']); ?></p>
                            <span class="text-[10px] font-normal text-right mt-1.5 <?php echo e($msg['sender'] === 'user' ? 'text-indigo-200' : 'text-gray-400'); ?>"><?php echo e($msg['timestamp']); ?></span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($msg['sender'] === 'user'): ?>
                            <div class="w-8 h-8 rounded-full bg-indigo-700 text-white flex items-center justify-center text-sm font-bold shrink-0 font-mono">U</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        <!-- Quick actions / Prompts -->
        <div class="px-6 py-3 bg-white border-t border-gray-100 flex flex-wrap gap-2">
            <span class="text-xs text-gray-400 self-center font-semibold">Gợi ý nhanh:</span>
            <button wire:click="$set('chatInput', 'Kiểm kê hôm nay'); sendChatMessage();" class="px-3 py-1 bg-purple-50 hover:bg-purple-100 border border-purple-200 text-purple-700 text-xs font-bold rounded-lg transition cursor-pointer">
                📋 Lấy danh sách kiểm kê hôm nay
            </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentCountId): ?>
                <?php $activeCount = \App\Models\StockCount::with('items.product')->find($currentCountId); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeCount && $activeCount->items->isNotEmpty()): ?>
                    <?php $firstItem = $activeCount->items->first(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($firstItem && $firstItem->product): ?>
                    <button wire:click="$set('chatInput', '<?php echo e($firstItem->product->code); ?> còn 15'); sendChatMessage();" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 border text-slate-700 text-xs font-bold rounded-lg transition cursor-pointer">
                        ✏️ Thử: "<?php echo e($firstItem->product->code); ?> còn 15"
                    </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Chat input form -->
        <form wire:submit.prevent="sendChatMessage" class="p-2 bg-white border-t flex gap-3">
            <input type="text" 
                wire:model="chatInput"
                placeholder="Nhập tin nhắn..." 
                class="flex-1 rounded-xl border-gray-200 text-sm focus:ring-purple-500 focus:border-purple-500 shadow-sm"
                required>
            <button type="submit" class="px-6 py-2.5 bg-purple-700 hover:bg-purple-800 text-white rounded-xl text-sm font-black transition shadow cursor-pointer flex items-center gap-1">
                Gửi 🚀
            </button>
        </form>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>


<style>
    @media print {
        @page { margin: 0; size: auto; }
        .no-print, header, nav, aside, footer { display: none !important; }
        body { background: white !important; margin: 0; padding: 10mm; }
        .shadow, .border { box-shadow: none !important; border: none !important; }
        table { width: 100% !important; border-collapse: collapse !important; border: 1px solid #ddd !important; }
        th, td { border: 1px solid #ddd !important; padding: 8px !important; text-align: left; }
        th { background-color: #f9f9f9 !important; -webkit-print-color-adjust: exact; }
        .bg-blue-50, .bg-green-50, .bg-indigo-50 { background-color: white !important; }
        .text-indigo-600, .text-blue-600 { color: black !important; }
    }
</style>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/stock-count-form.blade.php ENDPATH**/ ?>