<div class="px-4 pb-10">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">Quản lý các phiếu thực hiện bảo dưỡng sửa chữa</p>
        </div>
        <button wire:click="openModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
            <span>+</span> Lập Phiếu Bảo Dưỡng
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
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm số phiếu, thiết bị..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Số Phiếu</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày bảo dưỡng</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên thiết bị bảo dưỡng</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã tài sản bảo dưỡng</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mức bảo dưỡng</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên tài xế</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600"><?php echo e($ticket->ticket_code); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo e($ticket->maintenance_date ? $ticket->maintenance_date->format('d/m/Y') : '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900"><?php echo e($ticket->asset->name ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono"><?php echo e($ticket->asset->asset_code ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700"><?php echo e($ticket->maintenance_rule_id ?? 'N/A'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo e($ticket->staff_name); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit(<?php echo e($ticket->id); ?>)" class="text-indigo-600 hover:text-indigo-900 mx-2 bg-indigo-50 hover:bg-indigo-100 p-1.5 rounded" title="Chi tiết / Sửa">✏️</button>
                                <button x-on:click="if(confirm('Xóa phiếu bảo dưỡng này?')) $wire.delete(<?php echo e($ticket->id); ?>)" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded" title="Xóa">🗑️</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <p class="mb-2 text-3xl">📭</p>
                                <p>Chưa có phiếu bảo dưỡng nào.</p>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            <?php echo e($tickets->links()); ?>

        </div>
    </div>

    <!-- Modal Form -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isModalOpen): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-xl text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full max-h-[90vh] flex flex-col">
                    <form wire:submit.prevent="save" class="flex flex-col h-full">
                        <div class="bg-white px-6 pt-5 pb-4 border-b border-gray-100 flex-shrink-0 flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                <?php echo e($ticketId ? 'Cập nhật phiếu bảo dưỡng' : 'Lập phiếu bảo dưỡng mới'); ?>

                            </h3>
                            <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                                <span class="text-2xl">&times;</span>
                            </button>
                        </div>

                        <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px);">
                            <div class="space-y-6">
                                
                                <!-- Khối Thông Tin Cơ Bản -->
                                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 space-y-4">
                                    <h4 class="font-bold text-indigo-800 border-b border-slate-200 pb-2 mb-4 uppercase text-sm">1. Thông tin chung</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Số Phiếu</label>
                                            <input type="text" wire:model="ticket_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm font-mono font-bold" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Ngày Thực Hiện <span class="text-red-500">*</span></label>
                                            <input type="date" wire:model="maintenance_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['maintenance_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700">Thiết Bị <span class="text-red-500">*</span></label>
                                            <select wire:model.live="asset_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">-- Chọn Thiết Bị --</option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <option value="<?php echo e($asset->id); ?>">[<?php echo e($asset->asset_code); ?>] <?php echo e($asset->name); ?></option>
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

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Cấp Bảo Dưỡng / Hạng Mục</label>
                                            <select wire:model="maintenance_rule_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">-- Chọn Cấp --</option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <option value="<?php echo e($rule->rule_code ?: $rule->category); ?>"><?php echo e($rule->rule_code ?: $rule->category); ?> - <?php echo e($rule->name); ?></option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            </select>
                                            <span class="text-xs text-gray-500">Phải chọn thiết bị trước để hiển thị cấp bảo dưỡng tương ứng.</span>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Giờ Máy Hiện Tại <span class="text-red-500">*</span></label>
                                            <div class="mt-1 flex rounded-md shadow-sm">
                                                <input type="number" step="0.01" wire:model="maintenance_odo" class="flex-1 block w-full border-gray-300 rounded-none rounded-l-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right font-bold text-blue-600">
                                                <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">giờ</span>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['maintenance_odo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Khối Nội Dung Công Việc -->
                                <div class="bg-white p-4 rounded-lg border border-gray-200 space-y-4">
                                    <h4 class="font-bold text-indigo-800 border-b border-gray-200 pb-2 mb-4 uppercase text-sm">2. Nội dung thực hiện</h4>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Chi tiết công việc đã làm</label>
                                        <textarea wire:model="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Mô tả các hạng mục đã kiểm tra, sửa chữa..."></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Vật tư / Phụ tùng thay thế</label>
                                        <textarea wire:model="materials_used" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="VD: 20L nhớt động cơ, 1 lọc dầu, 1 lọc gió..."></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nhân Viên Thực Hiện</label>
                                            <input type="text" wire:model="staff_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Người Kiểm Tra (KCS / Quản Đốc)</label>
                                            <input type="text" wire:model="inspector" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Kết Quả Bàn Giao</label>
                                        <select wire:model="result" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">-- Trạng thái máy sau bảo dưỡng --</option>
                                            <option value="Hoạt động bình thường">Hoạt động bình thường</option>
                                            <option value="Cần theo dõi thêm">Cần theo dõi thêm</option>
                                            <option value="Chưa đạt yêu cầu">Chưa đạt yêu cầu</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Khối Hình Ảnh -->
                                <div class="bg-white p-4 rounded-lg border border-gray-200 space-y-4">
                                    <h4 class="font-bold text-indigo-800 border-b border-gray-200 pb-2 mb-4 uppercase text-sm">3. Hình ảnh đính kèm</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Hình ảnh TRƯỚC bảo dưỡng</label>
                                            <input type="file" wire:model="image_before" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            <div wire:loading wire:target="image_before" class="text-xs text-indigo-500 mt-1">Đang tải lên...</div>
                                            
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image_before): ?>
                                                <div class="mt-2"><img src="<?php echo e($image_before->temporaryUrl()); ?>" class="h-32 object-contain border rounded p-1"></div>
                                            <?php elseif($existing_image_before): ?>
                                                <div class="mt-2"><img src="<?php echo e(Storage::url($existing_image_before)); ?>" class="h-32 object-contain border rounded p-1"></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['image_before'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Hình ảnh SAU bảo dưỡng</label>
                                            <input type="file" wire:model="image_after" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            <div wire:loading wire:target="image_after" class="text-xs text-indigo-500 mt-1">Đang tải lên...</div>
                                            
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image_after): ?>
                                                <div class="mt-2"><img src="<?php echo e($image_after->temporaryUrl()); ?>" class="h-32 object-contain border rounded p-1"></div>
                                            <?php elseif($existing_image_after): ?>
                                                <div class="mt-2"><img src="<?php echo e(Storage::url($existing_image_after)); ?>" class="h-32 object-contain border rounded p-1"></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['image_after'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex-shrink-0 flex justify-end gap-3 rounded-b-xl">
                            <button type="button" wire:click="closeModal" class="px-6 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                                Hủy
                            </button>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 rounded-lg text-sm font-bold text-white hover:bg-indigo-700 transition shadow-sm">
                                Hoàn Thành & Lưu Phiếu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/maintenance-ticket-manager.blade.php ENDPATH**/ ?>