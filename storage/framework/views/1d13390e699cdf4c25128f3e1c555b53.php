<div class="p-2 bg-white rounded shadow-md mt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Danh Sách Phiếu Bảo Dưỡng</h2>
        <div class="flex gap-2 items-center">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                <span class="text-sm text-red-600 bg-red-50 px-3 py-1 rounded"><?php echo e(session('error')); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <button wire:click="printSelected" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded shadow-sm transition flex items-center gap-2">
                <span>🖨️</span> In Phiếu
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedTickets) > 0): ?>
                    <span class="bg-white text-sky-700 px-1.5 py-0.5 rounded text-xs ml-1"><?php echo e(count($selectedTickets)); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-2 border-b text-center w-12">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                    </th>
                    <th class="px-2 py-2 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày bảo dưỡng</th>
                    <th class="px-2 py-2 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên thiết bị bảo dưỡng</th>
                    <th class="px-2 py-2 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã tài sản bảo dưỡng</th>
                    <th class="px-2 py-2 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mức bảo dưỡng</th>
                    <th class="px-2 py-2 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên tài xế</th>
                    <th class="px-2 py-2 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-slate-50 <?php echo e(in_array($ticket->id, $selectedTickets) ? 'bg-sky-50' : ''); ?>">
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <input type="checkbox" value="<?php echo e($ticket->id); ?>" wire:model.live="selectedTickets" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo e($ticket->maintenance_date ? \Carbon\Carbon::parse($ticket->maintenance_date)->format('d/m/Y') : $ticket->created_at->format('d/m/Y')); ?></td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-gray-900"><?php echo e($ticket->asset->name ?? 'N/A'); ?></td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-500 font-mono"><?php echo e($ticket->asset->asset_code ?? 'N/A'); ?></td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm font-semibold text-gray-700"><?php echo e($ticket->maintenance_rule_id ?? 'N/A'); ?></td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-600"><?php echo e($ticket->staff_name ?? 'N/A'); ?></td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->status == 'completed'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Hoàn thành</span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ xử lý</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                            Chưa có phiếu bảo dưỡng nào.
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php /**PATH D:\Project\resources\views/components/maintenance/ticket-list.blade.php ENDPATH**/ ?>