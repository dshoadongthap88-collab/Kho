<div x-data x-on:trigger-print.window="setTimeout(() => window.print(), 300)">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            .bg-white { box-shadow: none !important; border: none !important; }
        }
    </style>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 no-print shadow-sm">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 no-print shadow-sm">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-8">
        <!-- Khu vực thêm Thành phẩm dự kiến (Bên trái) -->
        <div class="md:col-span-1 border rounded-xl bg-white shadow-sm p-2 no-print">
            <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">🎯 Mục tiêu Sản xuất</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chọn loại Thành phẩm</label>
                    <select wire:model="newProductId" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Click để chọn --</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hasBomProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($product->id); ?>"><?php echo e($product->code); ?> - <?php echo e($product->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newProductId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng dự kiến</label>
                    <input type="number" wire:model="newQuantity" min="1" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newQuantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button wire:click="addTarget" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-md font-semibold transition shadow-sm">
                    ➕ Thêm vào Kế hoạch
                </button>
            </div>

            <!-- Danh sách Hàng thành phẩm đã thêm -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($targetProducts) > 0): ?>
                <div class="mt-6 pt-4 border-t">
                    <h4 class="font-semibold text-sm text-gray-700 mb-2">Đang lên kế hoạch cho:</h4>
                    <ul class="space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $targetProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $tgt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li class="flex justify-between items-center bg-indigo-50 px-3 py-2 rounded border border-indigo-100 <?php echo e(isset($tgt['is_selected']) && !$tgt['is_selected'] ? 'opacity-50 grayscale' : ''); ?>">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model.live="targetProducts.<?php echo e($index); ?>.is_selected" class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-sm">
                                    <div>
                                        <span class="block text-xs font-bold <?php echo e(isset($tgt['is_selected']) && !$tgt['is_selected'] ? 'text-gray-500 line-through' : 'text-indigo-800'); ?>"><?php echo e($tgt['name']); ?></span>
                                        <span class="text-[10px] text-gray-500">SL: <?php echo e($tgt['quantity']); ?></span>
                                    </div>
                                </div>
                            </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="mt-6 pt-4 border-t text-sm text-gray-400 text-center italic">
                    Chưa có sản phẩm nào được chọn
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Khu vực kết quả Tính toán BOM (Bên phải) -->
        <div class="md:col-span-2">
            <div class="border rounded-xl bg-white shadow-sm p-2 h-full flex flex-col">
                <div class="flex justify-between items-end mb-4 border-b pb-2">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Phân tích Nhu cầu Nguyên vật liệu</h3>
                        <p class="text-xs text-gray-500">Hệ thống phân rã và gộp tự động từ Định mức tiêu hao (BOM)</p>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($materialNeeds)): ?>
                    <div class="flex gap-2 no-print">
                        <button onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 px-3 py-1.5 rounded text-sm font-semibold flex items-center gap-1 shadow-sm transition">
                            🖨️ In Yêu Cầu
                        </button>
                        <button wire:confirm="Xác nhận chuyển dữ liệu hàng hóa ĐANG THIẾU sang trang Phiếu Đề Xuất Mua Hàng tự động?" wire:click="sendToPurchase" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded text-sm font-semibold flex items-center gap-1 shadow-sm transition">
                            📤 Trình mua hàng
                        </button>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($materialNeeds)): ?>
                    <div class="flex-1 overflow-auto print:overflow-visible">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-[10px] font-bold">
                                    <th class="px-3 py-2 border-y">Mã NVL</th>
                                    <th class="px-3 py-2 border-y">Tên Nguyên Vật Liệu</th>
                                    <th class="px-3 py-2 border-y text-center">ĐVT</th>
                                    <th class="px-3 py-2 border-y text-right">Cần dùng</th>
                                    <th class="px-3 py-2 border-y text-right">Tồn kho</th>
                                    <th class="px-3 py-2 border-y text-center">Tình trạng</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $materialNeeds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-3 py-2 font-mono text-xs text-gray-500"><?php echo e($mat['code']); ?></td>
                                        <td class="px-3 py-2 font-semibold text-slate-800"><?php echo e($mat['name']); ?></td>
                                        <td class="px-3 py-2 text-center text-xs bg-slate-50"><?php echo e($mat['unit']); ?></td>
                                        <td class="px-3 py-2 text-right font-bold text-indigo-600"><?php echo e(number_format($mat['required'])); ?></td>
                                        <td class="px-3 py-2 text-right"><?php echo e(number_format($mat['in_stock'])); ?></td>
                                        <td class="px-3 py-2 text-center">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mat['shortage'] > 0): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold leading-4 bg-red-100 text-red-700 print:bg-transparent print:text-red-700">
                                                    THIẾU <?php echo e(number_format($mat['shortage'])); ?>

                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold leading-4 bg-green-100 text-green-700 print:bg-transparent print:text-green-700">
                                                    ĐỦ HÀNG
                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="flex-1 flex flex-col items-center justify-center text-gray-400 italic py-12 print:hidden">
                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <p>Kế hoạch sản xuất đang trống.</p>
                        <p class="text-xs">Vui lòng chọn Thành phẩm ở bên trái để tiến hành tính toán định mức.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="no-print mt-2 text-xs italic text-gray-500">
                Lưu ý: Bấm <b>In Yêu Cầu</b> để tải bản in xác nhận Nhu Cầu NVL. Bấm <b>Trình mua hàng</b> phần mềm sẽ điều hướng những nguyên vật liệu <span class="text-red-500 font-bold">THIẾU</span> sang trang Phiếu Mua Hàng tự động để tiết kiệm thời gian nhập liệu lại.
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\Project\resources\views\livewire\warehouse\material-requirement.blade.php ENDPATH**/ ?>