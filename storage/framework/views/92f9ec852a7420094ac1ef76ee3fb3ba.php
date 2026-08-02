<div class="p-4 max-w-5xl mx-auto">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-lg flex items-center gap-2">
            ✅ <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-lg flex items-center gap-2">
            ❌ <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        
        <div class="bg-indigo-700 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-white text-lg font-bold flex items-center gap-2">
                    🚚 Tạo phiếu chuyển kho
                </h2>
                <p class="text-indigo-200 text-sm mt-0.5">
                    Tạo phiếu chuyển và chờ chi nhánh nhận xác nhận
                </p>
            </div>
            <a href="<?php echo e(route('warehouse.stock-transfer.index')); ?>"
               class="text-indigo-200 hover:text-white text-sm transition">
                ← Quay lại danh sách
            </a>
        </div>

        <div class="p-6 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        🏠 Chuyển đến Chi nhánh / Dự án <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="to_project_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $available_projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($project->id); ?>"><?php echo e($project->name); ?> (<?php echo e($project->code); ?>)</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['to_project_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        📝 Ghi chú chung
                    </label>
                    <input wire:model="note" type="text"
                        placeholder="Lý do chuyển kho..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        👤 Người nhận <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="receiver_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">-- Chọn người nhận --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> - <?php echo e($user->phone); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['receiver_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        📞 SĐT Người nhận
                    </label>
                    <input wire:model="receiver_phone" type="text"
                        placeholder="Số điện thoại người nhận"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            
            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="text-sm font-semibold text-gray-700">📦 Danh sách vật tư / sản phẩm</label>
                    <div class="w-1/2 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">🔍</span>
                        <input type="text" wire:model.live.debounce.300ms="searchQuery" 
                               placeholder="Tìm kiếm mã hoặc tên vật tư để thêm vào danh sách..."
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all shadow-sm">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($searchResults)): ?>
                            <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto mt-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $searchResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div wire:click="addSelectedProduct('<?php echo e($res['code']); ?>')"
                                         class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm border-b border-gray-50 last:border-0 flex justify-between items-center transition-colors">
                                        <span><strong class="text-indigo-700"><?php echo e($res['code']); ?></strong> - <?php echo e($res['name']); ?></span>
                                        <div class="text-right">
                                            <span class="text-xs font-semibold <?php echo e($res['stock'] > 0 ? 'text-green-600' : 'text-red-500'); ?> block">
                                                Tồn: <?php echo e($res['stock']); ?> <?php echo e($res['unit']); ?>

                                            </span>
                                        </div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <button wire:click="addItem" type="button"
                        class="flex items-center gap-1 text-sm px-3 py-2 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition shadow-sm border border-gray-200">
                        ➕ Thêm dòng trống
                    </button>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden relative">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-3 py-2 text-left w-10">#</th>
                                <th class="px-3 py-2 text-left">Mã & Tên vật tư</th>
                                <th class="px-3 py-2 text-center w-28">Số lượng tồn</th>
                                <th class="px-3 py-2 text-center w-32">Số lượng xuất</th>
                                <th class="px-3 py-2 text-center w-32">Vị trí</th>
                                <th class="px-3 py-2 text-left w-48">Ghi chú mặt hàng</th>
                                <th class="px-3 py-2 text-center w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'item-'.e($index).''; ?>wire:key="item-<?php echo e($index); ?>">
                                    <td class="px-3 py-2 text-gray-400 text-center"><?php echo e($loop->iteration); ?></td>
                                    <td class="px-3 py-2">
                                        <input list="products-list" type="text"
                                            wire:model.live="items.<?php echo e($index); ?>.product_code"
                                            placeholder="Chọn vật tư..."
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 uppercase">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["items.{$index}.product_code"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-center font-bold text-gray-700">
                                        <?php echo e($item['stock'] ?? 0); ?>

                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number"
                                            wire:model="items.<?php echo e($index); ?>.quantity"
                                            min="0.01" step="0.01"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["items.{$index}.quantity"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text"
                                            wire:model="items.<?php echo e($index); ?>.location"
                                            placeholder="Kệ A..."
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text"
                                            wire:model="items.<?php echo e($index); ?>.note"
                                            placeholder="Ghi chú..."
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($items) > 1): ?>
                                            <button wire:click="removeItem(<?php echo e($index); ?>)" type="button"
                                                class="text-red-400 hover:text-red-600 transition text-lg leading-none">
                                                ×
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <datalist id="products-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($product->code); ?>"><?php echo e($product->code); ?> - <?php echo e($product->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </datalist>
            </div>

            
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="<?php echo e(route('warehouse.stock-transfer.index')); ?>"
                    class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Hủy
                </a>
                <button wire:click="save" wire:loading.attr="disabled"
                    class="px-6 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 disabled:opacity-60">
                    <span wire:loading.remove>🚚 Hoàn tất chuyển kho</span>
                    <span wire:loading>⏳ Đang xử lý...</span>
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\Project\resources\views\livewire\warehouse\stock-transfer-form.blade.php ENDPATH**/ ?>