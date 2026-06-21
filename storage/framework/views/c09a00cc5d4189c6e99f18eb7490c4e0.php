<div class="px-4">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">Cập nhật chỉ số Odo và Giờ máy hàng ngày cho thiết bị</p>
        </div>
        <button wire:click="openModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
            <span>+</span> Cập nhật Odo mới
        </button>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2">
            <span>✅</span> <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Toolbar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-4 items-center justify-between">
        <div class="w-full md:w-1/3 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm mã, tên thiết bị..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Thiết bị</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Odo Cũ</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Odo Mới</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">+Km</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Giờ Cũ</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Giờ Mới</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">+Giờ</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Người cập nhật</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo e($log->reading_date->format('d/m/Y')); ?></td>
                            <td class="px-6 py-4 text-sm text-indigo-600 font-semibold"><?php echo e($log->asset->asset_code ?? ''); ?> - <?php echo e($log->asset->name ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right"><?php echo e(number_format($log->old_odo)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right"><?php echo e(number_format($log->new_odo)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 text-right">+<?php echo e(number_format($log->odo_diff)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right"><?php echo e(number_format($log->old_hours)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right"><?php echo e(number_format($log->new_hours)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600 text-right">+<?php echo e(number_format($log->hours_diff)); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500"><?php echo e($log->updated_by); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button x-on:click="if(confirm('Xóa bản ghi này?')) $wire.delete(<?php echo e($log->id); ?>)" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded" title="Xóa">🗑️</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                <p class="mb-2 text-3xl">📭</p>
                                <p>Chưa có dữ liệu cập nhật Odo.</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            <?php echo e($logs->links()); ?>

        </div>
    </div>

    <!-- Modal Form -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isModalOpen): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4" id="modal-title">
                                Cập nhật Odo / Giờ Máy
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Ngày cập nhật <span class="text-red-500">*</span></label>
                                        <input type="date" wire:model="reading_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['reading_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Chọn Thiết Bị <span class="text-red-500">*</span></label>
                                        <select wire:model.live="asset_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">-- Chọn thiết bị --</option>
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
                                </div>

                                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200">
                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Odo hiện tại</label>
                                            <input type="text" disabled wire:model="old_odo" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md sm:text-sm text-gray-500 font-bold text-right">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-indigo-700">Odo mới <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model.live.debounce.300ms="new_odo" class="mt-1 block w-full border-indigo-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right font-bold text-indigo-900">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_odo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-green-700">Số Km Tăng</label>
                                            <input type="text" disabled wire:model="odo_diff" class="mt-1 block w-full bg-green-50 border-green-200 rounded-md sm:text-sm text-green-700 font-bold text-right">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">Giờ máy hiện tại</label>
                                            <input type="text" disabled wire:model="old_hours" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md sm:text-sm text-gray-500 font-bold text-right">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-indigo-700">Giờ máy mới <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model.live.debounce.300ms="new_hours" class="mt-1 block w-full border-indigo-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right font-bold text-indigo-900">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_hours'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-green-700">Số Giờ Tăng</label>
                                            <input type="text" disabled wire:model="hours_diff" class="mt-1 block w-full bg-green-50 border-green-200 rounded-md sm:text-sm text-green-700 font-bold text-right">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                                    <textarea wire:model="note" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Lưu nhật ký
                            </button>
                            <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Hủy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/asset-odo-log.blade.php ENDPATH**/ ?>