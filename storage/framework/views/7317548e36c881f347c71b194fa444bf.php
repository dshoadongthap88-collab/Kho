<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-4 p-2 bg-green-100 text-green-800 rounded-lg"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="bg-white rounded-xl shadow p-2">
        <h2 class="text-xl font-bold mb-4">🔧 Quản lý BOM - Định mức mã tài sản</h2>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Chọn sản phẩm thành phẩm / Mã tài sản</label>
            <select wire:model.live="selectedProductId" class="w-full rounded-lg border-gray-300 shadow-sm">
                <option value="">-- Chọn sản phẩm / Mã tài sản --</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($product->id); ?>"><?php echo e($product->code); ?> - <?php echo e($product->name); ?></option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedProductId): ?>
        <div class="mb-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold">Danh sách định mức vật tư</h3>
                <div class="flex gap-2 no-print">
                    <button type="button" onclick="window.print()" class="bg-slate-800 hover:bg-black text-white px-4 py-2 rounded-lg flex items-center gap-2 transition text-sm font-bold shadow-md cursor-pointer">
                        🖨️ In BOM Định mức mã tài sản
                    </button>
                    <button type="button" wire:click="saveBom" wire:loading.attr="disabled" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition text-sm font-bold shadow-md cursor-pointer">
                        <span wire:loading.remove wire:target="saveBom">💾</span>
                        <span wire:loading wire:target="saveBom" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        Lưu cấu hình
                    </button>
                </div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($bomItems) > 0): ?>
            <table class="w-full mb-4">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left text-sm">Định mức Vật tư</th>
                        <th class="px-3 py-2 text-center text-sm">Định mức / 1 Mã tài sản</th>
                        <th class="px-3 py-2 text-center text-sm">ĐVT</th>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($availability): ?>
                        <th class="px-3 py-2 text-center text-sm">Tồn kho</th>
                        <th class="px-3 py-2 text-center text-sm">Trạng thái</th>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <th class="px-3 py-2 text-center text-sm w-20"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $bomItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="border-b">
                        <td class="px-3 py-2 text-sm"><?php echo e($item['material_name']); ?></td>
                        <td class="px-3 py-2 text-center text-sm"><?php echo e(floatval($item['quantity'])); ?></td>
                        <td class="px-3 py-2 text-center text-sm text-gray-500"><?php echo e($item['unit']); ?></td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($availability && isset($availability['details'][$index])): ?>
                        <td class="px-3 py-2 text-center text-sm"><?php echo e(number_format($availability['details'][$index]['available'])); ?></td>
                        <td class="px-3 py-2 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($availability['details'][$index]['is_sufficient']): ?>
                                <span class="text-green-600 text-xs">🟢 Đủ</span>
                            <?php else: ?>
                                <span class="text-red-600 text-xs">🔴 Thiếu</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <td class="px-3 py-2 text-center">
                            <button wire:confirm="Xác nhận xóa vật tư <?php echo e($item['material_name']); ?> khỏi định mức mã tài sản?" wire:click="removeMaterial(<?php echo e($item['id']); ?>)" class="text-rose-500 hover:text-rose-700 text-xs font-bold transition-all hover:scale-110">Xóa</button>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="text-gray-400 text-sm mb-4">Chưa có vật tư định mức nào được khai báo cho sản phẩm/mã tài sản này.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="bg-gray-50 rounded-lg p-2">
                <h4 class="text-sm font-semibold mb-3">Thêm định mức vật tư</h4>
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Vật tư định mức</label>
                        <select wire:model="newMaterialId" class="w-full rounded border-gray-300 shadow-sm text-sm">
                            <option value="">-- Chọn vật tư --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($mat->id); ?>"><?php echo e($mat->code); ?> - <?php echo e($mat->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="w-28">
                        <label class="block text-xs text-gray-500 mb-1">Số lượng</label>
                        <input type="text" inputmode="numeric" wire:model.lazy="newQuantity" class="w-full rounded border-gray-300 shadow-sm text-sm" placeholder="0">
                    </div>
                    <div class="w-24">
                        <label class="block text-xs text-gray-500 mb-1">ĐVT</label>
                        <input type="text" wire:model="newUnit" placeholder="tự động" class="w-full rounded border-gray-300 shadow-sm text-sm">
                    </div>
                    <button wire:click="addMaterial" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">Thêm</button>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <style>
        @media print {
            .no-print, header, nav, aside.sidebar, .mb-6:first-child, .bg-gray-50.p-2 { display: none !important; }
            .bg-white { box-shadow: none !important; }
            body { font-size: 12pt; }
        }
    </style>
</div>
<?php /**PATH D:\Project\resources\views\livewire\warehouse\bom-manager.blade.php ENDPATH**/ ?>