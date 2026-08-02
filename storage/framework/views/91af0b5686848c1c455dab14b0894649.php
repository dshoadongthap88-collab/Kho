<div class="p-6 bg-white rounded-lg shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800"><?php echo e($bomId ? 'Cập nhật BOM bảo dưỡng' : 'Tạo mới BOM bảo dưỡng'); ?></h2>
        <a href="<?php echo e(route('maintenance-boms.index')); ?>" class="text-gray-500 hover:underline">&larr; Quay lại danh sách</a>
    </div>

    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã BOM</label>
                <input type="text" wire:model="bom_code" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" required>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['bom_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã tài sản (Xe)</label>
                <select wire:model.live="asset_id" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" required>
                    <option value="">-- Chọn tài sản --</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($asset->id); ?>"><?php echo e($asset->asset_code); ?> - <?php echo e($asset->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['asset_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cấp bảo dưỡng</label>
                <input type="text" wire:model="maintenance_level" placeholder="VD: 1000 giờ" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" required>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['maintenance_level'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chu kỳ (giờ/km)</label>
                <input type="number" wire:model="cycle" placeholder="VD: 1000" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" required>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asset_id): ?>
        <div class="p-4 bg-gray-50 rounded-lg border mb-8 flex gap-6">
            <div><span class="text-gray-500 text-sm">Tên xe:</span> <span class="font-semibold"><?php echo e($asset_name); ?></span></div>
            <div><span class="text-gray-500 text-sm">Model:</span> <span class="font-semibold"><?php echo e($asset_model ?: 'N/A'); ?></span></div>
            <div><span class="text-gray-500 text-sm">Hãng:</span> <span class="font-semibold"><?php echo e($asset_manufacturer ?: 'N/A'); ?></span></div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Chi tiết vật tư</h3>
                <button type="button" wire:click="addItem" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                    + Thêm vật tư
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="p-2 font-semibold text-gray-600 w-1/4">Mã vật tư</th>
                            <th class="p-2 font-semibold text-gray-600">Tên & Thông số</th>
                            <th class="p-2 font-semibold text-gray-600 w-24">ĐVT</th>
                            <th class="p-2 font-semibold text-gray-600 w-24">Số lượng</th>
                            <th class="p-2 font-semibold text-gray-600 w-28">Dự phòng</th>
                            <th class="p-2 font-semibold text-gray-600">Ghi chú</th>
                            <th class="p-2 font-semibold text-gray-600 w-16">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="border-b" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'item-'.e($index).''; ?>wire:key="item-<?php echo e($index); ?>">
                            <td class="p-2">
                                <select wire:model.live="items.<?php echo e($index); ?>.product_id" class="w-full p-2 border rounded" required>
                                    <option value="">-- Chọn VT --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($product->id); ?>"><?php echo e($product->code); ?> - <?php echo e($product->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </td>
                            <td class="p-2 text-sm text-gray-700">
                                <div><?php echo e($item['product_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($item['product_desc']); ?></div>
                            </td>
                            <td class="p-2 text-sm"><?php echo e($item['product_unit']); ?></td>
                            <td class="p-2">
                                <input type="number" step="0.01" wire:model="items.<?php echo e($index); ?>.quantity" class="w-full p-2 border rounded" required>
                            </td>
                            <td class="p-2">
                                <input type="number" step="0.01" wire:model="items.<?php echo e($index); ?>.backup_quantity" class="w-full p-2 border rounded" required>
                            </td>
                            <td class="p-2">
                                <input type="text" wire:model="items.<?php echo e($index); ?>.note" class="w-full p-2 border rounded" placeholder="Ghi chú...">
                            </td>
                            <td class="p-2 text-center">
                                <button type="button" wire:click="removeItem(<?php echo e($index); ?>)" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($items) === 0): ?>
                        <tr>
                            <td colspan="7" class="p-4 text-center text-gray-500">Chưa có vật tư nào. Bấm "Thêm vật tư" để bắt đầu.</td>
                        </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-4 mt-8">
            <a href="<?php echo e(route('maintenance-boms.index')); ?>" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold shadow">
                <?php echo e($bomId ? 'Cập nhật BOM' : 'Lưu BOM'); ?>

            </button>
        </div>
    </form>
</div>
<?php /**PATH D:\Project\resources\views\components\maintenance\maintenance-bom-form.blade.php ENDPATH**/ ?>