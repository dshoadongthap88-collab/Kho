<div class="space-y-6">
    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            nav, .sidebar-toolbar, button, a, .no-print, input, select { display: none !important; }
            .bg-white { box-shadow: none !important; border: none !important; }
            body { background: white !important; font-size: 10pt; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #ddd !important; padding: 8px !important; }
            .flex-row, .flex { display: block !important; }
            .print-only { display: block !important; }
            h2 { font-size: 18pt !important; text-align: center !important; margin-bottom: 20px !important; }
        }
    </style>
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2 bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <div>
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <span>🚚</span> Quản lý Giao Hàng
            </h2>
            <p class="text-sm text-slate-500 mt-1">Theo dõi trạng thái giao hàng và thanh toán công nợ</p>
        </div>
        
        <div class="flex flex-wrap gap-3 w-full md:w-auto items-end">
            <!-- Date Filter -->
            <div class="flex items-center gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Từ ngày</label>
                    <input type="date" wire:model.live="dateFrom" class="border border-gray-300 rounded-lg shadow-sm px-3 py-1.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Đến ngày</label>
                    <input type="date" wire:model.live="dateTo" class="border border-gray-300 rounded-lg shadow-sm px-3 py-1.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Search -->
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm khách, mã phiếu..." class="w-full pl-9 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm text-xs">
            </div>

            <!-- Filter Status -->
            <select wire:model.live="filterStatus" class="border border-gray-300 rounded-lg shadow-sm px-3 py-1.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">🚨 Chờ giao hàng</option>
                <option value="delivered">✅ Đã giao</option>
            </select>

            <!-- Export Buttons -->
            <div class="flex gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
                    <div class="flex items-center gap-2 pr-2 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4">
                        <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded">Đã chọn: <?php echo e(count($selectedIds)); ?></span>
                        <button type="button" 
                                wire:click="deleteSelected" 
                                wire:confirm="Xóa <?php echo e(count($selectedIds)); ?> báo cáo đã chọn?" 
                                wire:loading.attr="disabled"
                                class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-black transition cursor-pointer">
                            <span wire:loading.remove wire:target="deleteSelected">🗑️</span>
                            <span wire:loading wire:target="deleteSelected" class="w-4 h-4 border-2 border-rose-600 border-t-transparent rounded-full animate-spin"></span>
                            XÓA
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="button" 
                        wire:click="printSelected" 
                        wire:loading.attr="disabled"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-lg text-xs font-black transition shadow-sm cursor-pointer">
                    <span wire:loading.remove wire:target="printSelected" class="text-sm">🖨️</span>
                    <span wire:loading wire:target="printSelected" class="w-4 h-4 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></span>
                    In Ghép
                </button>
                <button wire:click="exportExcel" class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Xuất báo cáo Excel">
                    <span class="text-sm">📊</span> Excel
                </button>
                <button onclick="window.print()" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-700 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Xuất báo cáo PDF/In">
                    <span class="text-sm">📄</span> PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('message')): ?>
        <div class="bg-green-100 border border-green-200 text-green-800 rounded-lg p-2 flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <?php
                            $idsOnPage = $reports->pluck('id')->toArray();
                        ?>
                        <th class="px-2 py-2 w-10 no-print">
                            <input type="checkbox" wire:click="toggleSelectAll([<?php echo e(implode(',', $idsOnPage)); ?>])" <?php echo e(count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : ''); ?> class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="px-2 py-2">Mã Phiếu Xuất</th>
                        <th class="px-2 py-2">Khách hàng</th>
                        <th class="px-2 py-2 text-center">Tình trạng Giao</th>
                        <th class="px-2 py-2 text-center">Thanh toán</th>
                        <th class="px-2 py-2">Ghi chú</th>
                        <th class="px-2 py-2 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 transition-colors <?php echo e($report->status === 'pending' ? 'bg-red-50/30' : ''); ?> <?php echo e(in_array($report->id, $selectedIds) ? 'bg-indigo-50/20' : ''); ?>">
                            <td class="px-2 py-1.5 no-print">
                                <input type="checkbox" wire:model.live="selectedIds" value="<?php echo e($report->id); ?>" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="px-2 py-1.5 font-semibold text-indigo-700">
                                <?php echo e($report->stockOut->code ?? 'N/A'); ?>

                                <div class="text-[11px] text-slate-400 font-normal mt-1"><?php echo e(optional($report->stockOut->created_at)->format('d/m/Y H:i')); ?></div>
                            </td>
                            <td class="px-2 py-1.5 font-medium text-slate-800">
                                <?php echo e($report->customer_name ?: 'Khách lẻ'); ?>

                            </td>
                            <td class="px-2 py-1.5 text-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->status === 'delivered'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Đã nhận hàng 
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border border-red-500 text-red-600 animate-pulse bg-red-50">
                                        🚨 Chờ đi giao
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->delivered_at): ?>
                                    <div class="text-[10px] text-slate-500 mt-1"><?php echo e(\Carbon\Carbon::parse($report->delivered_at)->format('d/m/Y H:i')); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-2 py-1.5 text-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->payment_status === 'paid'): ?>
                                    <span class="inline-block px-2 text-xs font-semibold rounded bg-emerald-100 text-emerald-800">Đã thanh toán</span>
                                <?php elseif($report->payment_status === 'debt'): ?>
                                    <span class="inline-block px-2 text-xs font-semibold rounded bg-amber-100 text-amber-800">Ghi nợ</span>
                                <?php else: ?>
                                    <span class="inline-block px-2 text-xs font-semibold rounded bg-slate-100 text-slate-600">Chưa TT</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-2 py-1.5 text-sm text-slate-600 max-w-[200px] truncate" title="<?php echo e($report->notes); ?>">
                                <?php echo e($report->notes ?: '-'); ?>

                            </td>
                            <td class="px-2 py-1.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="printSingle(<?php echo e($report->id); ?>)" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition" title="In báo cáo này">🖨️</button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->status !== 'delivered'): ?>
                                        <button wire:click="openConfirmModal(<?php echo e($report->id); ?>)" class="bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-black px-1.5 py-1 text-[11px] rounded-lg shadow-sm transition-all hover:scale-105 uppercase">
                                            Xác nhận
                                        </button>
                                    <?php else: ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report->photo_path): ?>
                                            <a href="<?php echo e(Storage::url($report->photo_path)); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-[11px] font-black underline flex items-center justify-center gap-1 bg-indigo-50 px-1.5 py-1 text-[11px] rounded-lg">
                                                <span>📷</span> ẢNH
                                            </a>
                                        <?php else: ?>
                                            <span class="text-[10px] text-slate-400 font-bold">N/A</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <button wire:confirm="Xác nhận xóa báo cáo giao hàng này?" wire:click="delete(<?php echo e($report->id); ?>)" class="p-1.5 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded transition" title="Xóa">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Không tìm thấy báo cáo giao hàng nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-slate-200">
            <?php echo e($reports->links()); ?>

        </div>
    </div>

    <!-- Confirm Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showConfirmModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showConfirmModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <div class="bg-blue-50 px-4 py-3 border-b border-blue-100">
                        <h3 class="text-lg leading-6 font-bold text-blue-900 flex items-center gap-2">
                            <span>✅</span> Xác nhận Giao Hàng Thành Công
                        </h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        
                        <!-- Upload Ảnh -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Ảnh Minh Chứng (Bắt buộc / Tùy chọn) <span class="text-blue-500">📷</span></label>
                            <input type="file" wire:model="photo" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 rounded-lg p-1">
                            <div wire:loading wire:target="photo" class="text-xs text-blue-500 mt-1">Đang tải ảnh lên...</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($photo): ?>
                                <div class="mt-2 rounded border border-slate-200 overflow-hidden w-24 relative">
                                    <img src="<?php echo e($photo->temporaryUrl()); ?>" class="w-full h-auto object-cover">
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['photo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Trạng thái Thanh toán -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Trạng thái Thanh toán / Công Nợ</label>
                            <select wire:model="paymentStatus" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="paid">✅ Tiền mặt / Đã thanh toán</option>
                                <option value="bank_transfer">🏦 Chuyển khoản công ty</option>
                                <option value="debt">📝 Ghi Nợ Khách Hàng</option>
                                <option value="unpaid">⏳ Chưa thanh toán (chờ xử lý)</option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paymentStatus'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Ghi chú -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Ghi chú thêm</label>
                            <textarea wire:model="notes" rows="3" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Người thân nhận hộ, tiền chuyển khoản, v.v..."></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-xl">
                        <button type="button" wire:click="saveCompletion" wire:loading.attr="disabled" class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm transition items-center gap-2">
                            Xác nhận hoàn thành
                            <div wire:loading wire:target="saveCompletion" class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin ml-2"></div>
                        </button>
                        <button type="button" wire:click="$set('showConfirmModal', false)" class="inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none sm:w-auto sm:text-sm">
                            Đóng
                        </button>
                    </div>
                </div>
            </div>
    <!-- PHẦN IN CHI TIẾT HÀNG LOẠT (GIAO HÀNG / CHỨNG TỪ GỐC) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($printItems) > 0): ?>
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $printItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="print-page p-8 bg-white" style="font-family: 'Times New Roman', serif; min-height: 297mm; page-break-after: always;">
            
            <div style="text-align: center; margin-bottom: 16px;">
                <h1 style="font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 0;">CÔNG TY CPĐT VÀ THI CÔNG HẠ TẦNG VINALPHA</h1>
            </div>
            <div class="flex justify-between items-start mb-6 border-b-2 border-slate-900 pb-4">
                <div>
                    <h1 class="text-xl font-black uppercase tracking-tighter text-slate-900">BÁO CÁO GIAO HÀNG</h1>
                    <p class="text-xs font-bold text-slate-500 italic">Mã tham chiếu: <?php echo e($pItem->stockOut->code ?? 'N/A'); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Thời gian giao</p>
                    <p class="font-black text-slate-800 text-sm"><?php echo e($pItem->delivered_at ? $pItem->delivered_at->format('d/m/Y H:i') : 'Chưa giao'); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 mb-6">
                <div class="space-y-2">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Khách hàng nhận</p>
                        <p class="font-black text-slate-800 text-lg uppercase"><?php echo e($pItem->customer_name); ?></p>
                    </div>
                </div>
                <div class="text-right space-y-2">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Thanh toán</p>
                        <p class="font-black text-emerald-700 text-sm uppercase">
                            <?php echo e($pItem->payment_status === 'paid' ? 'Đã thu tiền mặt' : ($pItem->payment_status === 'debt' ? 'Ghi nợ công nợ' : ($pItem->payment_status === 'bank_transfer' ? 'Chuyển khoản' : 'Chưa thu tiền'))); ?>

                        </p>
                    </div>
                </div>
            </div>

            <!-- Bảng vật tư chi tiết -->
            <div class="mb-6">
                <p class="font-black text-[11px] uppercase mb-2 text-slate-800">Chi tiết vật tư bàn giao:</p>
                <table class="w-full border-collapse border border-slate-900">
                    <thead>
                        <tr class="bg-slate-100 uppercase text-[9px] font-black text-slate-800">
                            <th class="border border-slate-900 px-2 py-1.5 text-center">STT</th>
                            <th class="border border-slate-900 px-2 py-1.5 text-left">Tên vật tư / Quy cách</th>
                            <th class="border border-slate-900 px-2 py-1.5 text-right">Số lượng</th>
                            <th class="border border-slate-900 px-2 py-1.5 text-center font-bold">ĐVT</th>
                        </tr>
                    </thead>
                    <tbody class="text-[11px]">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pItem->stockOut): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pItem->stockOut->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $ii): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="border border-slate-900 px-2 py-1 text-center"><?php echo e($idx + 1); ?></td>
                                <td class="border border-slate-900 px-2 py-1 font-bold"><?php echo e($ii->product->name); ?></td>
                                <td class="border border-slate-900 px-2 py-1 text-right font-black"><?php echo e(number_format($ii->quantity)); ?></td>
                                <td class="border border-slate-900 px-2 py-1 text-center italic"><?php echo e($ii->product->unit); ?></td>
                            </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Chứng từ gốc (Ảnh) -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pItem->photo_path): ?>
            <div class="mt-6 border-t pt-4">
                <p class="font-black text-[11px] uppercase mb-3 text-slate-800">Ảnh minh chứng giao hàng (Chứng từ gốc):</p>
                <div class="w-full flex justify-center">
                    <img src="<?php echo e(Storage::url($pItem->photo_path)); ?>" class="max-h-[120mm] rounded-lg border-2 border-slate-200 shadow-sm object-contain">
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid grid-cols-2 gap-2 text-center mt-auto pt-10 border-t border-slate-100">
                <div>
                    <p class="font-bold text-xs uppercase text-slate-800">Khách nhận hàng ký tên</p>
                    <div style="height: 60px;"></div>
                    <p class="text-[10px] uppercase font-black"><?php echo e($pItem->customer_name); ?></p>
                </div>
                <div>
                    <p class="font-bold text-xs uppercase text-slate-800">Người đi giao ký tên</p>
                    <div style="height: 60px;"></div>
                    <p class="text-[10px] text-slate-400">............................................</p>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
        $__scriptKey = '8285322-2';
        ob_start();
    ?>
    <script>
        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
</div>
<?php /**PATH D:\Project\resources\views\livewire\warehouse\delivery-report-list.blade.php ENDPATH**/ ?>