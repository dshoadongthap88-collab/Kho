<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">ĐỊNH MỨC BẢO DƯỠNG (BOM)</h2>
    </div>

    <!-- Thông báo lỗi/thành công -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <span class="block sm:inline"><?php echo e(session('message')); ?></span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <span class="block sm:inline"><?php echo e(session('error')); ?></span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-2">
        <!-- CỘT 1: CHỌN THIẾT BỊ (Chiếm 3 cột) -->
        <div class="md:col-span-3 bg-white p-2 rounded-xl shadow-sm border border-slate-200">
            <div class="mb-4 pb-2 border-b space-y-2">
                <h3 class="font-bold text-slate-700 uppercase">1. Chọn Thiết bị</h3>
                <input type="text" wire:model.live.debounce.300ms="searchAsset" placeholder="Tìm tên thiết bị, mã..." class="w-full text-sm border-slate-200 rounded-md focus:ring-sky-500 focus:border-sky-500 px-3 py-1.5 shadow-sm">
            </div>
            <div class="space-y-2 max-h-[600px] overflow-y-auto pr-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button wire:click="selectAsset(<?php echo e($asset->id); ?>)" 
                            class="w-full text-left px-3 py-2 rounded-lg transition-colors border <?php echo e($selectedAssetId == $asset->id ? 'bg-sky-50 border-sky-300 shadow-sm' : 'border-transparent hover:bg-slate-50'); ?>">
                        <div class="font-bold text-slate-800 text-sm uppercase"><?php echo e($asset->name); ?></div>
                        <div class="text-xs text-slate-500 font-mono"><?php echo e($asset->equipment_code ?: 'N/A'); ?></div>
                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="text-sm text-slate-500 italic text-center py-4">Chưa có thiết bị</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <!-- CỘT 2: CÁC MỨC BẢO DƯỠNG (Chiếm 3 cột) -->
        <div class="md:col-span-3 bg-white p-2 rounded-xl shadow-sm border border-slate-200">
            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                <h3 class="font-bold text-slate-700 uppercase">2. Mức bảo dưỡng</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAssetId): ?>
                <button wire:click="openBomModal" class="bg-indigo-600 hover:bg-indigo-700 text-white p-1 rounded transition-colors" title="Thêm mức mới">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$selectedAssetId): ?>
                <div class="text-sm text-slate-500 italic text-center py-4">Vui lòng chọn thiết bị trước</div>
            <?php else: ?>
                <div class="space-y-2 max-h-[600px] overflow-y-auto pr-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $boms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bom): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex items-center gap-2">
                            <button wire:click="selectBom(<?php echo e($bom->id); ?>)" 
                                    class="flex-1 text-left px-3 py-2 rounded-lg transition-colors border <?php echo e($selectedBomId == $bom->id ? 'bg-indigo-50 border-indigo-300 shadow-sm' : 'border-transparent hover:bg-slate-50'); ?>">
                                <div class="font-bold text-slate-800 text-sm"><?php echo e($bom->maintenance_level); ?></div>
                                <div class="text-[10px] text-slate-500 uppercase"><?php echo e($bom->bom_code); ?></div>
                            </button>
                            <!-- Sửa / Xóa BOM -->
                            <div class="flex flex-col gap-1">
                                <button wire:click="openBomModal(<?php echo e($bom->id); ?>)" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1 rounded"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                                <button onclick="if(confirm('Bạn có chắc muốn xóa Mức bảo dưỡng này và toàn bộ vật tư bên trong?')) window.Livewire.find('<?php echo e($_instance->getId()); ?>').deleteBom(<?php echo e($bom->id); ?>)" class="text-red-500 hover:text-red-700 bg-red-50 p-1 rounded"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="text-sm text-slate-500 italic text-center py-4">Chưa có mức bảo dưỡng nào</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- CỘT 3: DANH SÁCH VẬT TƯ BÊN TRONG BOM (Chiếm 6 cột) -->
        <div class="md:col-span-6 bg-white p-2 rounded-xl shadow-sm border border-slate-200">
            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                <h3 class="font-bold text-slate-700 uppercase">3. Chi tiết BOM Vật tư</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedBomId): ?>
                <div class="flex gap-2">
                    <button wire:click="saveBomItemsQuantities" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-xs font-bold rounded flex items-center gap-1 shadow-sm transition-colors uppercase">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Lưu thay đổi
                    </button>
                    <button wire:click="openCopyModal" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 text-xs font-bold rounded flex items-center gap-1 shadow-sm transition-colors uppercase">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                        Sao chép BOM
                    </button>
                    <button wire:click="openProductPicker" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 text-xs font-bold rounded flex items-center gap-1 shadow-sm transition-colors uppercase">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm vật tư
                    </button>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$selectedBomId): ?>
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-sm font-medium">Vui lòng chọn 1 Mức bảo dưỡng để xem danh sách vật tư</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto border border-slate-200 rounded-lg max-h-[600px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-100 text-[11px] uppercase font-bold text-slate-600 sticky top-0 shadow-sm z-10">
                            <tr>
                                <th class="px-3 py-2 border-b border-slate-200">Mã VT</th>
                                <th class="px-3 py-2 border-b border-slate-200">Tên vật tư</th>
                                <th class="px-3 py-2 border-b border-slate-200 text-center">ĐVT</th>
                                <th class="px-3 py-2 border-b border-slate-200 text-right w-32">Định mức</th>
                                <th class="px-3 py-2 border-b border-slate-200 text-left">Ghi chú</th>
                                <th class="px-3 py-2 border-b border-slate-200 text-center w-16">Xóa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $bomItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-3 py-2 text-xs font-mono text-indigo-600"><?php echo e($item->product->code ?? 'N/A'); ?></td>
                                    <td class="px-3 py-2 text-sm font-bold text-slate-800"><?php echo e($item->product->name ?? 'N/A'); ?></td>
                                    <td class="px-3 py-2 text-xs text-center text-slate-600 uppercase"><?php echo e($item->product->box_spec ?? 'Cái'); ?></td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" wire:model="bomItemQuantities.<?php echo e($item->id); ?>" class="w-full border-gray-300 rounded text-right font-bold text-indigo-700 py-1 px-2 text-xs shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" wire:model="bomItemNotes.<?php echo e($item->id); ?>" class="w-full border-gray-300 rounded text-left py-1 px-2 text-xs shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Ghi chú...">
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <button onclick="if(confirm('Xóa vật tư này khỏi BOM?')) window.Livewire.find('<?php echo e($_instance->getId()); ?>').deleteItem(<?php echo e($item->id); ?>)" class="text-red-600 hover:text-red-800 bg-red-50 p-1.5 rounded" title="Xóa"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-sm text-slate-500 italic">BOM hiện chưa có vật tư nào</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- MODAL: THÊM / SỬA MỨC BẢO DƯỠNG (BOM) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showBomModal): ?>
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showBomModal', false)"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4 uppercase"><?php echo e($isEditBom ? 'Cập nhật Mức bảo dưỡng' : 'Thêm Mức bảo dưỡng mới'); ?></h3>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mức bảo dưỡng (vd: 250h, Hàng ngày)</label>
                            <input type="text" wire:model="maintenance_level" class="w-full border-gray-300 rounded-md shadow-sm p-2 border">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['maintenance_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chu kỳ (Số giờ - Dùng để sắp xếp)</label>
                            <input type="number" wire:model="cycle" class="w-full border-gray-300 rounded-md shadow-sm p-2 border" placeholder="250">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['cycle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="saveBom" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Lưu lại</button>
                        <button type="button" wire:click="$set('showBomModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Hủy</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- MODAL: THÊM VẬT TƯ (PICKER LỚN) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showProductPickerModal): ?>
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showProductPickerModal', false)"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-xl leading-6 font-bold text-gray-900 uppercase">Danh mục Vật tư</h3>
                            <button wire:click="$set('showProductPickerModal', false)" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                        </div>
                        
                        <div class="mb-4">
                            <input type="text" wire:model.live.debounce.300ms="searchProduct" placeholder="Gõ tên hoặc mã để tìm kiếm vật tư..." class="w-full border-gray-300 rounded-md shadow-sm p-3 border bg-yellow-50 text-lg">
                        </div>

                        <div class="overflow-y-auto max-h-[50vh] border border-gray-200 rounded-lg">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-slate-100 text-[11px] uppercase font-bold text-slate-600 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 border-b border-slate-200 w-12 text-center">
                                            <input type="checkbox" wire:model.live="selectAllProducts" wire:click="toggleSelectAllProducts(<?php echo e(json_encode($allProductIdsOnPage)); ?>)" class="rounded text-indigo-600 focus:ring-indigo-500">
                                        </th>
                                        <th class="px-3 py-2 border-b border-slate-200">Mã VT</th>
                                        <th class="px-3 py-2 border-b border-slate-200">Tên vật tư</th>
                                        <th class="px-3 py-2 border-b border-slate-200 text-center">ĐVT</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <tr class="hover:bg-indigo-50 transition-colors cursor-pointer" onclick="document.getElementById('chk-<?php echo e($prod->id); ?>').click()">
                                            <td class="px-3 py-2 text-center" onclick="event.stopPropagation()">
                                                <input id="chk-<?php echo e($prod->id); ?>" type="checkbox" value="<?php echo e($prod->id); ?>" wire:model="selectedProductIds" class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                            </td>
                                            <td class="px-3 py-2 text-xs font-mono text-indigo-600"><?php echo e($prod->code); ?></td>
                                            <td class="px-3 py-2 text-sm font-bold text-slate-800"><?php echo e($prod->name); ?></td>
                                            <td class="px-3 py-2 text-xs text-center text-slate-600 uppercase"><?php echo e($prod->box_spec ?? 'Cái'); ?></td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-6 text-sm text-slate-500 italic">Không tìm thấy vật tư nào</td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="button" wire:click="addSelectedProductsToBom" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-bold text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Thêm <?php echo e(count($selectedProductIds)); ?> vật tư vào BOM
                        </button>
                        <button type="button" wire:click="$set('showProductPickerModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- MODAL: SAO CHÉP BOM -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCopyModal): ?>
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showCopyModal', false)"></div>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4 uppercase">Sao chép BOM từ mức khác</h3>
                        <p class="text-sm text-gray-500 mb-4">Thao tác này sẽ sao chép toàn bộ vật tư từ mức bảo dưỡng bạn chọn sang mức hiện tại (chỉ thêm vật tư mới, không ghi đè nếu đã tồn tại).</p>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chọn Mức bảo dưỡng nguồn</label>
                            <select wire:model="copyFromBomId" class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-white">
                                <option value="">-- Chọn Mức bảo dưỡng --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $otherBoms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ob): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($ob->id); ?>"><?php echo e($ob->maintenance_level); ?> (Chu kỳ: <?php echo e($ob->cycle); ?>)</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['copyFromBomId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="copyBom" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-500 text-base font-medium text-white hover:bg-amber-600 sm:ml-3 sm:w-auto sm:text-sm">Sao chép</button>
                        <button type="button" wire:click="$set('showCopyModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Hủy</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Thông báo lưu thành công góc màn hình (3s) -->
    <div x-data="{ show: false }"
         x-on:bom-saved-success.window="show = true; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         style="display: none;"
         class="fixed bottom-10 right-10 z-50 flex items-center gap-2 bg-green-600 text-white px-4 py-3 rounded shadow-lg">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="font-bold">Đã lưu định mức BOM thành công!</span>
    </div>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/maintenance-bom-manager.blade.php ENDPATH**/ ?>