<div class="relative" x-data="{ open: <?php if ((object) ('open') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('open'->value()); ?>')<?php echo e('open'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('open'); ?>')<?php endif; ?> }">
    
    <button @click="toggle" class="relative p-2 rounded-full hover:bg-sky-200 transition-all focus:outline-none">
        <span class="text-xl">🔔</span>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->unread_count > 0): ?>
            <span class="absolute top-0 right-0 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[8px] font-bold text-white ring-2 ring-sky-100">
                <?php echo e($this->unread_count); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>

    
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         class="absolute right-0 mt-2 w-72 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50 py-1">

        <div class="px-4 py-2 text-xs font-bold text-gray-800 border-b border-gray-100 flex justify-between items-center">
            <span>Thông báo mới</span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->unread_count > 0): ?>
                <span class="text-[10px] text-sky-600"><?php echo e($this->unread_count); ?> tin nhắn</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="max-h-64 overflow-y-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="px-4 py-3 text-xs border-b border-gray-50 last:border-none <?php echo e(!$msg->is_read ? 'bg-sky-50' : ''); ?>">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-gray-800"><?php echo e($msg->title ?? 'Hệ thống'); ?></span>
                        <span class="text-[9px] text-gray-400"><?php echo e(\Carbon\Carbon::parse($msg->created_at)->diffForHumans()); ?></span>
                    </div>
                    <p class="text-gray-600 leading-relaxed"><?php echo e($msg->message); ?></p>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="px-4 py-8 text-center text-gray-400">
                    <span class="text-2xl block mb-2">📭</span>
                    <p class="text-xs">Không có thông báo mới</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <a href="<?php echo e(route('warehouse.stock-transfer.index')); ?>" class="block px-4 py-2 text-center text-xs font-bold text-sky-600 hover:bg-sky-50 border-t border-gray-100">
            Xem tất cả lịch sử
        </a>
    </div>
</div>
<?php /**PATH D:\Project\resources\views\livewire\warehouse\warehouse-notification.blade.php ENDPATH**/ ?>