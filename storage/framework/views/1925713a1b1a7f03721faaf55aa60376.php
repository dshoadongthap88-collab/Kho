<div>
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Quản lý Danh mục Vật tư</h1>
            <p class="text-sm text-slate-500">Thêm, sửa, xóa các danh mục để phân loại vật tư</p>
        </div>
        <button wire:click="openModal" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium flex items-center gap-2">
            <span>+</span> Thêm Danh mục
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has("message")): ?>
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200">
            <?php echo e(session("message")); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has("error")): ?>
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            <?php echo e(session("error")); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm danh mục..." class="w-full md:w-1/3 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-sm border-b border-slate-200">
                        <th class="p-4 font-semibold">Tên Danh mục</th>
                        <th class="p-4 font-semibold">Mô tả</th>
                        <th class="p-4 font-semibold text-center">Số Vật tư</th>
                        <th class="p-4 font-semibold">Trạng thái</th>
                        <th class="p-4 font-semibold text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-medium text-slate-800"><?php echo e($category->name); ?></td>
                        <td class="p-4 text-slate-600"><?php echo e($category->description ?? "-"); ?></td>
                        <td class="p-4 font-medium text-indigo-600 text-center"><?php echo e($category->products_count); ?></td>
                        <td class="p-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->status === "active"): ?>
                                <span class="px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 rounded-full">Đang dùng</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-semibold text-slate-700 bg-slate-100 rounded-full">Ngừng dùng</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <button wire:click="openModal(<?php echo e($category->id); ?>)" class="text-indigo-600 hover:text-indigo-900 font-medium">Sửa</button>
                            <button wire:click="delete(<?php echo e($category->id); ?>)" onclick="confirm('Bạn có chắc muốn xóa danh mục này?') || event.stopImmediatePropagation()" class="text-red-600 hover:text-red-900 font-medium">Xóa</button>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">Không tìm thấy danh mục nào.</td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categories->hasPages()): ?>
        <div class="p-4 border-t border-slate-200">
            <?php echo e($categories->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Modal Form -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                <h3 class="text-lg font-bold text-slate-800"><?php echo e($isEdit ? "Cập nhật Danh mục" : "Thêm Danh mục Mới"); ?></h3>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tên Danh mục *</label>
                    <input type="text" wire:model="name" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["name"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Mô tả</label>
                    <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["description"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Trạng thái</label>
                    <select wire:model="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active">Đang dùng</option>
                        <option value="inactive">Ngừng dùng</option>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["status"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 font-medium">Hủy</button>
                <button wire:click="save" class="px-4 py-2 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 font-medium">Lưu lại</button>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH D:\Project\resources\views/livewire/warehouse/category-manager.blade.php ENDPATH**/ ?>