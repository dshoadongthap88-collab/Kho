<div>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-4">
        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm mã hoặc tên vật tư..." class="w-full md:w-56 py-1.5 px-3 text-sm rounded-lg border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
            <select wire:model.live="statusFilter" class="w-full md:w-40 py-1.5 px-3 text-sm rounded-lg border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pending">Đề xuất (Chờ duyệt)</option>
                <option value="ordered">Đã đặt hàng</option>
                <option value="unreceived">Chưa giao</option>
                <option value="partial">Giao thiếu</option>
                <option value="completed">Đủ hàng</option>
            </select>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <button wire:click="printSelected" class="flex items-center justify-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white px-3 py-1.5 rounded-lg text-sm font-medium shadow transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                In phiếu
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selected) > 0): ?>
                    <span class="bg-white text-slate-800 text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-1"><?php echo e(count($selected)); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
            <button wire:click="openAddModal" class="flex items-center justify-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium shadow transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Thêm đề xuất
            </button>
            <div class="flex items-center bg-indigo-50 border border-indigo-200 rounded-lg overflow-hidden shadow">
                <div class="px-2 py-1.5 text-indigo-700 font-medium text-xs whitespace-nowrap">Dự trù</div>
                <input type="number" wire:model="reserveDays" class="w-12 border-0 text-center font-bold text-indigo-700 text-sm bg-white focus:ring-0 p-1.5" title="Số ngày dự trù">
                <div class="px-2 py-1.5 text-indigo-700 font-medium text-xs whitespace-nowrap border-r border-indigo-200">ngày</div>
                <button wire:click="autoSuggest" wire:loading.attr="disabled" class="flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Tự động phân tích
                </button>
            </div>
            <button wire:click="closeDay" wire:confirm="Bạn có chắc chắn muốn chốt sổ và dọn dẹp bảng? Dữ liệu sẽ được lưu vào file Lịch sử." wire:loading.attr="disabled" class="flex items-center justify-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium shadow transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                Chốt sổ
            </button>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="mb-4 bg-emerald-50 text-emerald-700 p-4 rounded-lg border border-emerald-200 font-medium">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-2 py-2 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                        </th>
                        <th class="px-2 py-2">Ngày ĐX</th>
                        <th class="px-2 py-2">Mã & Tên Vật Tư</th>
                        <th class="px-2 py-2 text-right">Tồn Kho</th>
                        <th class="px-2 py-2 text-right">SL Đề Xuất</th>
                        <th class="px-2 py-2 text-right">Đã Giao</th>
                        <th class="px-2 py-2 text-right text-rose-600">Còn Thiếu</th>
                        <th class="px-2 py-2 text-center">Trạng Thái</th>
                        <th class="px-2 py-2 text-center">Tình Trạng</th>
                        <th class="px-2 py-2 text-center">Ngày Nhận</th>
                        <th class="px-2 py-2">Ghi Chú</th>
                        <th class="px-2 py-2 text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $missing = $plan->proposed_quantity - $plan->delivered_quantity;
                            $missing = $missing > 0 ? $missing : 0;
                        ?>
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                            <td class="px-2 py-1.5 text-center">
                                <input type="checkbox" wire:model.live="selected" value="<?php echo e($plan->id); ?>" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-xs"><?php echo e($plan->created_at->format('d/m/Y')); ?></td>
                            <td class="px-2 py-1.5 text-sm font-medium text-slate-900 leading-tight">
                                <div class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded text-[10px] font-bold inline-block mb-0.5"><?php echo e($plan->product?->code ?? 'N/A'); ?></div><br>
                                <span class="line-clamp-2 max-w-[180px]" title="<?php echo e($plan->product?->name ?? 'Vật tư đã bị xóa'); ?>"><?php echo e($plan->product?->name ?? 'Vật tư đã bị xóa'); ?></span>
                            </td>
                            <td class="px-2 py-1.5 text-right font-bold text-slate-600 text-sm whitespace-nowrap">
                                <?php echo e(number_format($plan->product?->inventory?->quantity ?? 0, 0)); ?>

                            </td>
                            <td class="px-2 py-1.5 text-right whitespace-nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->status !== 'completed'): ?>
                                    <input type="number" 
                                           value="<?php echo e($plan->proposed_quantity); ?>" 
                                           wire:change="updateProposedQuantity(<?php echo e($plan->id); ?>, $event.target.value)"
                                           class="w-16 text-right p-1 text-sm border-slate-300 rounded font-bold text-slate-700 focus:ring-sky-500 focus:border-sky-500 h-8">
                                <?php else: ?>
                                    <span class="font-bold text-slate-700 text-sm"><?php echo e(number_format($plan->proposed_quantity, 0)); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-2 py-1.5 text-right font-bold text-emerald-600 text-sm whitespace-nowrap"><?php echo e(number_format($plan->delivered_quantity, 0)); ?></td>
                            <td class="px-2 py-1.5 text-right font-bold text-rose-600 text-sm whitespace-nowrap"><?php echo e(number_format($missing, 0)); ?></td>
                            <td class="px-2 py-1.5 text-center whitespace-nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->status === 'pending'): ?>
                                    <span class="px-1.5 py-0.5 bg-slate-100 text-slate-600 rounded text-[11px] font-bold inline-block">Đề xuất</span>
                                <?php elseif($plan->status === 'ordered'): ?>
                                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-600 rounded text-[11px] font-bold inline-block">Đã đặt</span>
                                <?php elseif($plan->status === 'unreceived'): ?>
                                    <span class="px-1.5 py-0.5 bg-rose-100 text-rose-600 rounded text-[11px] font-bold inline-block">Chưa giao</span>
                                <?php elseif($plan->status === 'partial'): ?>
                                    <span class="px-1.5 py-0.5 bg-amber-100 text-amber-600 rounded text-[11px] font-bold inline-block">Giao thiếu</span>
                                <?php else: ?>
                                    <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-600 rounded text-[11px] font-bold inline-block">Đủ hàng</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-2 py-1.5 text-center whitespace-nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->status !== 'completed'): ?>
                                    <select wire:change="updateUrgency(<?php echo e($plan->id); ?>, $event.target.value)" class="p-1 text-[11px] h-7 border-slate-300 rounded focus:ring-sky-500 focus:border-sky-500 <?php echo e($plan->urgency === 'urgent' ? 'text-rose-600 font-bold bg-rose-50' : 'text-slate-600'); ?>">
                                        <option value="normal" <?php echo e($plan->urgency === 'normal' ? 'selected' : ''); ?>>Bình thường</option>
                                        <option value="urgent" <?php echo e($plan->urgency === 'urgent' ? 'selected' : ''); ?>>Cần gấp</option>
                                    </select>
                                <?php else: ?>
                                    <span class="text-[11px] px-1.5 py-0.5 rounded <?php echo e($plan->urgency === 'urgent' ? 'text-rose-600 font-bold bg-rose-50' : 'text-slate-600'); ?>">
                                        <?php echo e($plan->urgency === 'urgent' ? 'Cần gấp' : 'Bình thường'); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-2 py-1.5 text-center whitespace-nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->status !== 'completed'): ?>
                                    <input type="date" value="<?php echo e($plan->expected_delivery_date ? $plan->expected_delivery_date->format('Y-m-d') : ''); ?>"
                                           wire:change="updateExpectedDate(<?php echo e($plan->id); ?>, $event.target.value)"
                                           class="p-1 text-[11px] h-7 w-24 border-slate-300 rounded focus:ring-sky-500 focus:border-sky-500 text-slate-600">
                                <?php else: ?>
                                    <span class="text-[11px] text-slate-600 font-medium"><?php echo e($plan->expected_delivery_date ? $plan->expected_delivery_date->format('d/m/Y') : ''); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-2 py-1.5">
                                <input type="text" 
                                       value="<?php echo e($plan->notes); ?>" 
                                       wire:change="updateNotes(<?php echo e($plan->id); ?>, $event.target.value)"
                                       class="w-full min-w-[120px] p-1 text-[11px] border-slate-300 rounded focus:ring-sky-500 focus:border-sky-500 text-slate-700 h-7" 
                                       placeholder="Ghi chú...">
                            </td>
                            <td class="px-2 py-1.5 text-right space-x-1 whitespace-nowrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->status === 'pending'): ?>
                                    <button wire:click="placeOrder(<?php echo e($plan->id); ?>)" class="px-1.5 py-1 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded text-[11px] font-bold transition">Đặt hàng</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->status !== 'completed'): ?>
                                    <button wire:click="openUpdateModal(<?php echo e($plan->id); ?>)" class="px-1.5 py-1 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded text-[11px] font-bold transition">Nhận</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <button wire:click="delete(<?php echo e($plan->id); ?>)" wire:confirm="Bạn có chắc chắn muốn xóa?" class="px-1.5 py-1 text-rose-600 hover:bg-rose-50 rounded text-[11px] transition">Xóa</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-500 font-medium">Chưa có kế hoạch mua hàng nào.</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-200">
            <?php echo e($plans->links()); ?>

        </div>
    </div>

    <!-- Modal Cập nhật giao hàng (Sử dụng x-data của AlpineJS cho modal đơn giản) -->
    <div x-data="{ show: false }" 
         @open-modal.window="if ($event.detail[0] === 'update-delivery-modal') show = true"
         @close-modal.window="if ($event.detail[0] === 'update-delivery-modal') show = false"
         x-show="show" 
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-75" aria-hidden="true" @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 mb-4">Cập nhật số lượng giao hàng</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Số lượng đã giao</label>
                            <input type="number" wire:model="delivered_quantity" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['delivered_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-600 text-xs mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Ngày dự kiến nhận (nếu chưa giao đủ)</label>
                            <input type="date" wire:model="expected_delivery_date" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                        </div>
                    </div>
                </div>
                <div class="mt-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="saveDeliveryUpdate" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-bold text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Lưu cập nhật
                    </button>
                    <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Thêm Đề Xuất (Thủ công) -->
    <div x-data="{ show: false }" 
         @open-modal.window="if ($event.detail[0] === 'add-plan-modal') show = true"
         @close-modal.window="if ($event.detail[0] === 'add-plan-modal') show = false"
         x-show="show" 
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-75" aria-hidden="true" @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 mb-4">Thêm đề xuất mua hàng thủ công</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Vật tư / Sản phẩm <span class="text-rose-500">*</span></label>
                            <select wire:model="new_product_id" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                                <option value="">-- Chọn vật tư --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $allProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($product->id); ?>">[<?php echo e($product->code); ?>] <?php echo e($product->name); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_product_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-600 text-xs mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Số lượng <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model="new_quantity" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-600 text-xs mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Ghi chú</label>
                            <textarea wire:model="new_notes" rows="2" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500"></textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="saveNewPlan" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Lưu đề xuất
                    </button>
                    <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/purchase-plan/purchase-plan-manager.blade.php ENDPATH**/ ?>