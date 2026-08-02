<div x-data x-on:trigger-print.window="setTimeout(() => window.print(), 300)">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            .print-only { display: block !important; }
            /* Hide global layout elements when printing */
            nav, h1 { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
        }
    </style>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Date Filter Standard -->
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:ring-2 focus-within:ring-amber-100">
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Từ ngày</label>
                    <input type="date" wire:model.live="dateFrom" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                </div>
                <div class="w-px h-4 bg-slate-200 mx-1"></div>
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Đến ngày</label>
                    <input type="date" wire:model.live="dateTo" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                </div>
            </div>

            <!-- Search Standard -->
            <div class="relative w-64">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm số PO, nhà cung cấp..." class="w-full pl-9 pr-4 py-2 text-xs font-bold rounded-xl border-slate-200 focus:ring-amber-500 shadow-sm transition-all">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filter Status -->
            <select wire:model.live="filterStatus" class="border-slate-200 rounded-xl px-4 py-2 text-xs font-bold focus:ring-amber-500 shadow-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">📊 Đã trình</option>
                <option value="confirmed">✅ Đã duyệt</option>
                <option value="received">📦 Đã nhận</option>
                <option value="cancelled">❌ Đã hủy</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
                <div class="flex items-center gap-2 pr-3 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4 duration-300">
                    <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-2 py-1 rounded">Chọn: <?php echo e(count($selectedIds)); ?></span>
                    <button type="button" wire:click="deleteSelected" wire:confirm="Xóa <?php echo e(count($selectedIds)); ?> đơn hàng đã chọn?" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-black transition cursor-pointer">
                        <span wire:loading.remove wire:target="deleteSelected">🗑️</span>
                        <span wire:loading wire:target="deleteSelected" class="w-3 h-3 border-2 border-rose-600 border-t-transparent rounded-full animate-spin"></span>
                        XÓA
                    </button>
                    <button type="button" wire:click="printSelected" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white rounded-lg text-xs font-black transition cursor-pointer">
                        <span wire:loading.remove wire:target="printSelected">🖨️</span>
                        <span wire:loading wire:target="printSelected" class="w-3 h-3 border-2 border-amber-600 border-t-transparent rounded-full animate-spin"></span>
                        IN GỘP
                    </button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button wire:click="exportExcel" class="bg-emerald-600 font-black hover:bg-emerald-700 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md shadow-emerald-100">
                <span>📊</span> EXCEL
            </button>
            <button onclick="window.print()" class="bg-slate-800 font-black hover:bg-slate-900 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md">
                <span>📄</span> IN PDF
            </button>
            <button wire:click="openModal" class="bg-amber-600 font-black hover:bg-amber-700 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md shadow-amber-100">
                <span>➕</span> TẠO ĐỀ XUẤT
            </button>
            <button wire:click="openOfficeModal" class="bg-indigo-600 font-black hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-xs transition shadow-md shadow-indigo-100">
                🏢 MUA VĂN PHÒNG
            </button>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 no-print">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 no-print">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border no-print">
        <table class="w-full text-left border-collapse">
            <thead>
                <?php
                    $idsOnPage = $orders->pluck('id')->toArray();
                ?>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-black tracking-widest">
                    <th class="px-6 py-4 w-10 text-center no-print bg-slate-100/30">
                        <input type="checkbox" wire:click="toggleSelectAll([<?php echo e(implode(',', $idsOnPage)); ?>])" <?php echo e(count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : ''); ?> class="rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                    </th>
                    <th class="px-4 py-3">Số PO</th>
                    <th class="px-4 py-3">Nhà cung cấp</th>
                    <th class="px-4 py-3">Người đặt</th>
                    <th class="px-4 py-3">Ngày đặt hàng</th>
                    <th class="px-4 py-3">Ngày dự kiến giao</th>
                    <th class="px-4 py-3">Tổng tiền</th>
                    <th class="px-4 py-3 no-print">Trạng thái</th>
                    <th class="px-4 py-3 text-right no-print">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50/80 transition group <?php echo e(in_array($order->id, $selectedIds) ? 'bg-amber-50 is-selected' : ''); ?>">
                        <td class="px-6 py-4 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedIds" value="<?php echo e($order->id); ?>" class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                        </td>
                        <td class="px-4 py-3 font-mono font-black text-indigo-700"><?php echo e($order->po_number); ?></td>
                        <td class="px-4 py-3 text-gray-800"><?php echo e($order->supplier->name ?? 'N/A'); ?></td>
                        <td class="px-4 py-3 text-gray-700 text-sm">
                            <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full text-xs">👤 <?php echo e($order->user?->name ?? 'Chưa ghi'); ?></span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm"><?php echo e($order->order_date?->format('d/m/Y')); ?></td>
                        <td class="px-4 py-3 text-gray-600 text-sm"><?php echo e($order->expected_delivery_date?->format('d/m/Y')); ?></td>
                        <td class="px-4 py-3 font-semibold text-amber-700"><?php echo e(number_format($order->total_amount, 0, ',', '.')); ?> đ</td>
                        <td class="px-4 py-3 no-print">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($order->status):
                                case ('pending'): ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Đã trình</span>
                                    <?php break; ?>
                                <?php case ('confirmed'): ?>
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Đã duyệt</span>
                                    <?php break; ?>
                                <?php case ('received'): ?>
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Đã nhận</span>
                                    <?php break; ?>
                                <?php case ('cancelled'): ?>
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Đã hủy</span>
                                    <?php break; ?>
                            <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right flex gap-1 justify-end no-print">
                            <button wire:click="printSingle(<?php echo e($order->id); ?>)" class="text-slate-400 hover:text-amber-600 p-1 transition-all hover:scale-110" title="In phiếu">🖨️</button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->status === 'pending'): ?>
                                <button wire:click="confirmOrder(<?php echo e($order->id); ?>)" class="bg-emerald-500 hover:bg-emerald-600 text-white px-2 py-1 rounded text-[10px] font-black transition-all hover:scale-105 shadow-sm" title="Xác nhận">DUYỆT</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button wire:click="openModal(<?php echo e($order->id); ?>)" class="text-indigo-500 hover:text-indigo-700 p-1 transition-all hover:scale-110" title="Sửa">📝</button>
                            <button wire:confirm="Xác nhận xoá đơn hàng <?php echo e($order->po_number); ?>?" wire:click="delete(<?php echo e($order->id); ?>)" class="text-rose-400 hover:text-rose-600 p-1 transition-all hover:scale-110" title="Xoá">🗑️</button>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">Chưa có phiếu đề xuất nào.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            <?php echo e($orders->links()); ?>

        </div>
    </div>

    <!-- Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4"><?php echo e($isEdit ? 'Chỉnh sửa phiếu đề xuất' : 'Tạo phiếu đề xuất mới'); ?></h3>
                        
                        <div class="space-y-4">
                            <!-- Basic Info -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số phiếu (PO)</label>
                                    <input type="text" wire:model="po_number" <?php echo e(!$isEdit ? 'readOnly' : ''); ?> class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 <?php echo e(!$isEdit ? 'bg-gray-100' : ''); ?>" placeholder="PO-2024-001">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isEdit): ?>
                                        <small class="text-gray-500">Tự động sinh (tiếp theo)</small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['po_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nhà cung cấp/Khách hàng <span class="text-red-500">*</span></label>
                                    <select wire:model="supplier_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="">-- Chọn từ danh sách --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['supplier_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ngày đặt hàng</label>
                                    <input type="date" wire:model="order_date" readOnly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-100 cursor-not-allowed">
                                    <small class="text-gray-500">Ngày hôm nay (không thể sửa)</small>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['order_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ngày dự kiến giao</label>
                                    <input type="date" wire:model="expected_delivery_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <small class="text-gray-500">Mặc định +3 ngày từ ngày đặt</small>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['expected_delivery_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Trạng thái phê duyệt</label>
                                    <select wire:model="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="pending">Chờ xác nhận</option>
                                        <option value="confirmed">Đã xác nhận</option>
                                        <option value="received">Đã nhận hàng</option>
                                        <option value="cancelled">Đã hủy</option>
                                    </select>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tổng tiền</label>
                                    <input type="number" step="0.01" wire:model="total_amount" readOnly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-100">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['total_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                                <textarea wire:model="notes" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Ghi chú thêm..."></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Items Section -->
                            <div class="border-t pt-4">
                                <h4 class="font-semibold mb-3 text-gray-800">Mục hàng đặt</h4>
                                
                                <div class="space-y-3 mb-4">
                                    <div class="grid grid-cols-12 gap-2 pb-2">
                                        <div class="col-span-4">
                                            <select wire:model.live="newItemProductId" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                                <option value="">-- Gõ để chọn NVL cảnh báo thiếu hụt --</option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $lowStockProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <option value="<?php echo e($product->id); ?>">
                                                        <?php echo e($product->code); ?> - <?php echo e($product->name); ?> (Hãng: <?php echo e($product->brand ?? 'N/A'); ?> | Tồn: <?php echo e(floatval($product->inventory?->quantity ?? 0)); ?>)
                                                    </option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            </select>
                                            <p class="text-[10px] text-gray-500 mt-1">Chỉ hiển thị các nguyên vật liệu có tồn kho ≤ định mức an toàn.</p>
                                        </div>
                                        <div class="col-span-2">
                                            <input type="text" inputmode="numeric" wire:model.lazy="newItemQuantity" placeholder="SL Mua" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                        </div>
                                        <div class="col-span-2 no-print">
                                            <?php
                                                $selectedProd = $newItemProductId ? $products->firstWhere('id', $newItemProductId) : null;
                                                $invQty = $selectedProd ? ($selectedProd->inventory->quantity ?? 0) : 0;
                                            ?>
                                            <div class="w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2 text-sm text-gray-500 whitespace-nowrap overflow-hidden text-ellipsis shadow-inner" title="Tồn kho hiện tại: <?php echo e(floatval($invQty)); ?>">
                                                Tồn: <?php echo e(floatval($invQty)); ?>

                                            </div>
                                        </div>
                                        <div class="col-span-2">
                                            <button wire:click="addItem" type="button" class="w-full bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm font-semibold transition">Thêm</button>
                                        </div>
                                    </div>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($items)): ?>
                                    <div class="bg-gray-50 rounded border overflow-hidden">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="bg-gray-100 border-b">
                                                    <th class="px-2 py-2 text-left">Mã NVL</th>
                                                    <th class="px-2 py-2 text-left">Tên NVL</th>
                                                    <th class="px-2 py-2 text-left">Hãng SX</th>
                                                    <th class="px-2 py-2 text-center">ĐVT</th>
                                                    <th class="px-2 py-2 text-right">SL Mua</th>
                                                    <th class="px-2 py-2 text-center">Xoá</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <?php
                                                        $product = $products->firstWhere('id', $item['product_id']);
                                                    ?>
                                                    <tr class="hover:bg-gray-100">
                                                        <td class="px-2 py-2 font-mono text-xs"><?php echo e($product?->code ?? 'N/A'); ?></td>
                                                        <td class="px-2 py-2 font-semibold"><?php echo e($product?->name ?? 'N/A'); ?></td>
                                                        <td class="px-2 py-2 text-xs text-gray-600"><?php echo e($product?->brand ?? 'N/A'); ?></td>
                                                        <td class="px-2 py-2 text-center text-xs bg-gray-50"><?php echo e($product?->unit ?? 'N/A'); ?></td>
                                                        <td class="px-2 py-2 text-right font-bold text-blue-700"><?php echo e($item['quantity']); ?></td>
                                                        <td class="px-2 py-2 text-center">
                                                            <button wire:click="removeItem(<?php echo e($index); ?>)" type="button" class="text-red-500 hover:text-red-700">✕</button>
                                                        </td>
                                                    </tr>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" wire:click="save" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none sm:w-auto sm:text-sm">
                            💾 Lưu phiếu
                        </button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isEdit && !empty($items)): ?>
                            <button type="button" wire:click="$toggle('status')" wire:click.prevent="$set('status', 'confirmed'); save()" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:w-auto sm:text-sm">
                                ✓ Lưu & Xác nhận
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <button type="button" wire:click="$set('showModal', false)" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Modal Office Purchase -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showOfficeModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showOfficeModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-blue-50 border-b border-blue-100 px-4 py-3 sm:px-6">
                        <h3 class="text-lg leading-6 font-semibold text-blue-900 flex items-center gap-2">
                            <span>🏢</span> Tạo đề xuất Mua hàng Văn phòng
                        </h3>
                    </div>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="space-y-4">
                            <!-- Basic Info -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số phiếu (PO)</label>
                                    <input type="text" wire:model="po_number" readOnly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nhà cung cấp/Nới báo giá <span class="text-red-500">*</span></label>
                                    <select wire:model="supplier_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="">-- Chọn --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['supplier_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                            <!-- Items Section -->
                            <div class="border-t pt-4">
                                <h4 class="font-semibold mb-3 text-gray-800">Danh sách vật tư văn phòng</h4>
                                <div class="flex gap-2 mb-4 items-end bg-blue-50 p-3 rounded-lg border border-blue-100">
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Tên vật tư/VPP</label>
                                        <input type="text" wire:model="officeItemName" placeholder="Ví dụ: Giấy A4, Bút bi..." class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                    </div>
                                    <div class="w-20">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Số lượng</label>
                                        <input type="number" wire:model="officeItemQuantity" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm text-center">
                                    </div>
                                    <div class="w-28">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Dự kiến giá</label>
                                        <input type="number" wire:model="officeItemPrice" placeholder="Giá" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm text-right">
                                    </div>
                                    <div>
                                        <button wire:click="addOfficeItem" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold transition h-[38px]">Thêm</button>
                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['officeItem'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mb-2"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($officeItems)): ?>
                                    <div class="bg-gray-50 rounded border overflow-hidden">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-100 border-b">
                                                    <th class="px-3 py-2 text-left">Tên vật tư/VPP</th>
                                                    <th class="px-3 py-2 text-center w-16">SL</th>
                                                    <th class="px-3 py-2 text-center w-12">Xoá</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $officeItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <tr>
                                                        <td class="px-3 py-2 font-medium"><?php echo e($item['name']); ?></td>
                                                        <td class="px-3 py-2 text-center font-bold text-blue-700"><?php echo e($item['quantity']); ?></td>
                                                        <td class="px-3 py-2 text-center">
                                                            <button wire:click="removeOfficeItem(<?php echo e($index); ?>)" type="button" class="text-red-500 hover:text-red-700 font-bold">✕</button>
                                                        </td>
                                                    </tr>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" wire:click="saveOfficePurchase" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:w-auto sm:text-sm">
                            💾 Lưu Đề Xuất
                        </button>
                        <button type="button" wire:click="$set('showOfficeModal', false)" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- PHẦN IN PDF BỊ ẨN KHI XEM THƯỜNG -->
    <div class="hidden print:block fixed inset-0 bg-white z-[9999] w-full">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $printItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $printOrder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div style="font-family: 'Times New Roman', serif; padding: 15mm; page-break-after: always; width: 100%;">
            <!-- Header -->
            <div class="mb-4 text-center">
                <h1 class="text-xl font-bold uppercase" style="font-size: 18px; letter-spacing: 1px;">CÔNG TY CPĐT VÀ THI CÔNG HẠ TẦNG VINALPHA</h1>
                <p class="text-[14px]">Địa chỉ: Long An - SĐT: 0708091050</p>
            </div>
            <div style="border-bottom: 2px solid #000; margin-bottom: 20px;"></div>

            <!-- Title -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold uppercase tracking-widest text-black">PHIẾU ĐỀ XUẤT MUA HÀNG</h2>
                <p class="italic text-[13px] mt-1">
                    Ngày <?php echo e(\Carbon\Carbon::parse($printOrder->order_date)->format('d')); ?> 
                    tháng <?php echo e(\Carbon\Carbon::parse($printOrder->order_date)->format('m')); ?> 
                    năm <?php echo e(\Carbon\Carbon::parse($printOrder->order_date)->format('Y')); ?>

                </p>
            </div>

            <!-- Info -->
            <div style="margin-bottom: 20px;" class="text-[14px]">
                <table class="w-full">
                    <tr>
                        <td class="font-bold w-32 pb-1">Số PO:</td>
                        <td class="pb-1 uppercase font-semibold"><?php echo e($printOrder->po_number); ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold pb-1">Tên nhà CC:</td>
                        <td class="pb-1 uppercase font-bold"><?php echo e($printOrder->supplier->name ?? '............................................'); ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold pb-1">SĐT:</td>
                        <td class="pb-1"><?php echo e($printOrder->supplier->phone ?? '............................................'); ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold pb-1">Địa chỉ:</td>
                        <td class="pb-1"><?php echo e($printOrder->supplier->address ?? '............................................'); ?></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($printOrder->notes): ?>
                    <tr>
                        <td class="font-bold pb-1">Ghi chú:</td>
                        <td class="pb-1"><?php echo e($printOrder->notes); ?></td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </table>
            </div>

            <!-- Table -->
            <table class="w-full border-collapse border border-black text-[13px] mb-8">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-2 w-10 text-center font-bold">STT</th>
                        <th class="border border-black px-2 py-2 text-left w-24 font-bold">Mã Vật Tư</th>
                        <th class="border border-black px-2 py-2 text-left font-bold">TÊN VẬT TƯ (Nguyên vật liệu)</th>
                        <th class="border border-black px-2 py-2 text-center w-24 font-bold">S.Lượng</th>
                        <th class="border border-black px-2 py-2 text-center w-20 font-bold">ĐVT</th>
                        <th class="border border-black px-2 py-2 text-left w-24 font-bold">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $printOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="border border-black px-2 py-2 text-center"><?php echo e($idx + 1); ?></td>
                        <td class="border border-black px-2 py-2 font-mono uppercase"><?php echo e($item->product->code ?? ''); ?></td>
                        <td class="border border-black px-2 py-2 font-semibold"><?php echo e($item->product->name ?? ''); ?></td>
                        <td class="border border-black px-2 py-2 text-center font-bold"><?php echo e(floatval($item->quantity)); ?></td>
                        <td class="border border-black px-2 py-2 text-center"><?php echo e($item->product->unit ?? ''); ?></td>
                        <td class="border border-black px-2 py-2 text-center"></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = count($printOrder->items); $i < max(8, count($printOrder->items)); $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="border border-black px-2 py-2 text-center text-transparent">_</td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>

            <!-- Footer / Signatures -->
            <div class="grid grid-cols-2 gap-4 text-center mt-8 mb-8">
                <div>
                    <p class="font-bold text-[14px]">Người đặt hàng</p>
                    <p class="text-[12px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-bold uppercase text-[14px]"><?php echo e($printOrder->user->name ?? '........................'); ?></p>
                </div>
                <div>
                    <p class="font-bold text-[14px]">Người xét duyệt</p>
                    <p class="text-[12px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-bold uppercase text-[14px]">........................</p>
                </div>
            </div>
            
            <div class="text-right mt-12 mb-4 text-[11px] italic text-gray-500">
                In lúc: <?php echo e(date('d/m/Y H:i')); ?>

            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
</div><?php /**PATH D:\Project\resources\views\livewire\warehouse\purchase-order-list.blade.php ENDPATH**/ ?>