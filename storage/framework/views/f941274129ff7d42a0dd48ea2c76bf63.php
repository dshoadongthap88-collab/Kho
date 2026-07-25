<div class="space-y-4">
    <!-- Header & Actions -->
    <div class="flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Quản lý Thông báo</h2>
            <p class="text-sm text-gray-500">Tạo, sửa, xóa các thông báo gửi đến người dùng.</p>
        </div>
        <div class="flex gap-2">
            <div class="relative">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm kiếm thông báo..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <button wire:click="create" class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tạo Thông Báo
            </button>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline font-medium"><?php echo e(session('message')); ?></span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Người Nhận</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tiêu Đề</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nội Dung</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Thời Gian</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#<?php echo e($notification->id); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900"><?php echo e($notification->user->name ?? 'Không rõ'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($notification->user->email ?? ''); ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium"><?php echo e($notification->title); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <div class="max-w-xs truncate" title="<?php echo e($notification->message); ?>"><?php echo e($notification->message); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notification->is_read): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đã xem</span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Chưa xem</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e($notification->created_at->format('d/m/Y H:i')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit(<?php echo e($notification->id); ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3">Sửa</button>
                                <button onclick="confirm('Bạn có chắc muốn xóa thông báo này?') || event.stopImmediatePropagation()" wire:click="delete(<?php echo e($notification->id); ?>)" class="text-red-600 hover:text-red-900">Xóa</button>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    <p>Không tìm thấy thông báo nào</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notifications->hasPages()): ?>
        <div class="px-6 py-3 border-t border-gray-200">
            <?php echo e($notifications->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Form Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isModalOpen): ?>
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
            <form wire:submit.prevent="save">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">
                        <?php echo e($notification_id ? 'Chỉnh sửa Thông Báo' : 'Tạo Thông Báo Mới'); ?>

                    </h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Người nhận <span class="text-red-500">*</span></label>
                        <select wire:model="user_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-sky-500 focus:border-sky-500 text-sm">
                            <option value="">-- Chọn người nhận --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$notification_id): ?>
                                <option value="all" class="font-bold text-sky-700">Gửi cho tất cả mọi người</option>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> (<?php echo e($user->role); ?>)</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiêu đề <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-sky-500 focus:border-sky-500 text-sm" placeholder="VD: Thông báo lịch nghỉ lễ">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nội dung <span class="text-red-500">*</span></label>
                        <textarea wire:model="message" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-sky-500 focus:border-sky-500 text-sm" placeholder="Nhập nội dung thông báo..."></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        <?php echo e($notification_id ? 'Lưu thay đổi' : 'Gửi Thông Báo'); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH D:\Project\resources\views/livewire/hr/notification-manager.blade.php ENDPATH**/ ?>