<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="mb-4 bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isFormOpen): ?>
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 mb-6 animate-in slide-in-from-top-4 duration-300">
            <div class="p-4 border-b border-slate-200 bg-indigo-50 rounded-t-xl">
                <h2 class="text-lg font-black text-indigo-900 uppercase tracking-tight">
                    <?php echo e($isEditing ? 'Sửa thông tin tài sản' : 'Thêm mới tài sản'); ?>

                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Mã tài sản <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="asset_code" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold uppercase" placeholder="Nhập mã...">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['asset_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500 font-semibold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Tên thiết bị / Tài sản <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold" placeholder="Tên thiết bị...">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-xs text-red-500 font-semibold"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Bộ phận sử dụng</label>
                        <input type="text" wire:model="department" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="VD: Xưởng cơ khí...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Loại máy</label>
                        <input type="text" wire:model="machine_type" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="VD: Máy CNC, Máy tiện...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Hãng sản xuất</label>
                        <input type="text" wire:model="manufacturer" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Nhập hãng sản xuất...">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Model</label>
                        <input type="text" wire:model="model" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Mã model...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Số Serial</label>
                        <input type="text" wire:model="serial_number" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono uppercase" placeholder="S/N...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Ngày lắp đặt</label>
                        <input type="date" wire:model="installation_date" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold">
                    </div>
                    
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Biển số</label>
                        <input type="text" wire:model="license_plate" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold uppercase" placeholder="Biển số xe...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">ODO hiện tại</label>
                        <input type="number" wire:model="lifetime_odo" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold" placeholder="Km...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Giờ máy hiện tại</label>
                        <input type="number" wire:model="lifetime_hours" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold" placeholder="Giờ...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Chu kỳ BĐ (ODO)</label>
                        <input type="number" wire:model="maintenance_cycle_odo" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold" placeholder="Km...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Chu kỳ BĐ (Giờ)</label>
                        <input type="number" wire:model="maintenance_cycle_hours" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold" placeholder="Giờ...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Dự án <span class="text-red-500">*</span></label>
                        <select wire:model="house_id" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold">
                            <option value="">-- Chọn dự án --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $houses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $house): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($house->id); ?>"><?php echo e($house->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Kho quản lý <span class="text-red-500">*</span></label>
                        <select wire:model="management_unit" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold">
                            <option value="">-- Chọn kho quản lý --</option>
                            <option value="Vinalpha">Vinalpha</option>
                            <option value="Vincons">Vincons</option>
                            <option value="M&E">M&E</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Trạng thái <span class="text-red-500">*</span></label>
                        <select wire:model="status" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold">
                            <option value="active">🟢 Đang sử dụng</option>
                            <option value="maintenance">🟠 Đang bảo trì</option>
                            <option value="inactive">🔴 Ngưng sử dụng</option>
                        </select>
                    </div>
                </div>

                <!-- Định mức vật tư (BOM) -->
                <div class="mt-6 pt-4 border-t border-slate-200">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-black text-slate-700 uppercase tracking-tight">⚙️ Định mức vật tư bảo dưỡng (BOM)</h4>
                        <button type="button" wire:click="addBomItem" class="text-xs font-bold text-indigo-700 bg-indigo-100 px-3 py-1.5 rounded-lg hover:bg-indigo-200 transition shadow-sm flex items-center gap-1">
                            + Thêm vật tư
                        </button>
                    </div>
                    
                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($bomItems) == 0): ?>
                            <div class="text-sm text-slate-500 italic text-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                                Chưa có vật tư nào. Nhấp "Thêm vật tư" để khai báo các loại dầu/lọc.
                            </div>
                        <?php else: ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $bomItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200 shadow-sm" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'bom-item-'.e($index).''; ?>wire:key="bom-item-<?php echo e($index); ?>">
                                <div class="flex-1 space-y-1">
                                    <input type="text" wire:model.defer="bomItems.<?php echo e($index); ?>.name" placeholder="Tên vật tư (VD: Dầu 15W40...)" class="w-full text-sm px-3 py-2 rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 font-bold" list="common-materials-am">
                                </div>
                                <div class="w-32 space-y-1">
                                    <input type="text" wire:model.defer="bomItems.<?php echo e($index); ?>.quantity" placeholder="Số lượng/Đơn vị" class="w-full text-sm px-3 py-2 rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-center font-bold text-indigo-700">
                                </div>
                                <button type="button" wire:click="removeBomItem(<?php echo e($index); ?>)" class="mt-1 text-rose-500 hover:text-rose-700 p-2 bg-white rounded-lg shadow-sm border border-slate-200 hover:bg-rose-50 transition" title="Xóa">
                                    ✕
                                </button>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <datalist id="common-materials-am">
                        <option value="Dầu động cơ 15W-40">
                        <option value="Dầu động cơ 20W-50">
                        <option value="Dầu cầu hộp số 80W-90">
                        <option value="Dầu cầu hộp số 85W-140">
                        <option value="Dầu thủy lực AW 68">
                        <option value="Dầu thủy lực AW 46">
                        <option value="Mỡ bôi trơn EP2">
                        <option value="Lọc nhớt động cơ">
                        <option value="Lọc nhiên liệu (thô)">
                        <option value="Lọc nhiên liệu (tinh)">
                        <option value="Lọc gió (trong)">
                        <option value="Lọc gió (ngoài)">
                        <option value="Lọc thủy lực">
                        <option value="Lọc nước làm mát">
                    </datalist>
                </div>
            </div>
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 rounded-b-xl">
                <button wire:click="closeForm" class="px-6 py-2 rounded-lg font-bold text-slate-600 hover:bg-slate-200 transition">Hủy bỏ</button>
                <button wire:click="save" class="px-6 py-2 rounded-lg font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    LƯU LẠI
                </button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-wrap justify-between items-center gap-4">
            <h2 class="text-lg font-black text-slate-800 uppercase tracking-tight flex items-center gap-2">
                <span class="p-1.5 bg-indigo-100 text-indigo-700 rounded-lg">⚙️</span>
                DANH MỤC THIẾT BỊ
            </h2>
            <div class="flex items-center gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
                    <div class="flex items-center gap-2 pr-3 border-r border-slate-300 animate-in slide-in-from-right-4">
                        <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded-md">Đã chọn: <?php echo e(count($selectedIds)); ?></span>
                        <button class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-bold hover:bg-black transition shadow-sm">
                            🖨️ In mã QR
                        </button>
                        <button wire:click="deleteSelected" wire:confirm="Xóa <?php echo e(count($selectedIds)); ?> tài sản đã chọn?" class="px-3 py-1.5 bg-rose-500 text-white rounded-lg text-xs font-bold hover:bg-rose-600 transition shadow-sm">
                            🗑️ Xóa
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="relative">
                    <input wire:model.live="search" type="text" placeholder="Tìm mã, tên..." class="pl-9 pr-4 py-2 w-64 text-sm font-bold border-slate-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-inner">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isFormOpen): ?>
                    <button wire:click="openForm" class="bg-indigo-600 text-white px-5 py-2 rounded-xl text-sm font-black hover:bg-indigo-700 transition shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        THÊM MỚI
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-800 text-xs uppercase font-black text-white tracking-widest">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500">
                        </th>
                        <th class="px-4 py-3">MÃ TÀI SẢN</th>
                        <th class="px-4 py-3">Tên Thiết Bị / Máy</th>
                        <th class="px-4 py-3">Bộ phận</th>
                        <th class="px-4 py-3">Model/Serial</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3">Dự án</th>
                        <th class="px-4 py-3 text-center">Kho QL</th>
                        <th class="px-4 py-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="hover:bg-indigo-50/30 transition group <?php echo e(in_array($asset->id, $selectedIds) ? 'bg-indigo-50' : ''); ?>">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model.live="selectedIds" value="<?php echo e($asset->id); ?>" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3 font-black text-indigo-700"><?php echo e($asset->asset_code); ?></td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-800"><?php echo e($asset->name); ?></div>
                            <div class="text-[10px] uppercase text-slate-400 font-bold"><?php echo e($asset->machine_type); ?> <?php echo e($asset->manufacturer ? '('.$asset->manufacturer.')' : ''); ?></div>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-700"><?php echo e($asset->department ?: '-'); ?></td>
                        <td class="px-4 py-3 text-xs">
                            <div class="font-bold"><?php echo e($asset->model ?: '-'); ?></div>
                            <div class="font-mono text-slate-400"><?php echo e($asset->serial_number); ?></div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asset->status == 'active'): ?>
                                <span class="px-2.5 py-1 text-[10px] uppercase font-black rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">Đang sử dụng</span>
                            <?php elseif($asset->status == 'maintenance'): ?>
                                <span class="px-2.5 py-1 text-[10px] uppercase font-black rounded-lg bg-amber-50 text-amber-700 border border-amber-100">Bảo trì</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 text-[10px] uppercase font-black rounded-lg bg-rose-50 text-rose-700 border border-rose-100">Ngưng SD</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-700"><?php echo e(optional($asset->house)->name ?: '-'); ?></td>
                        <td class="px-4 py-3 text-center font-bold text-slate-700"><?php echo e($asset->management_unit ?: '-'); ?></td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="edit(<?php echo e($asset->id); ?>)" class="p-1.5 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Sửa">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button wire:click="delete(<?php echo e($asset->id); ?>)" wire:confirm="Xóa thiết bị này?" class="p-1.5 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Xóa">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            <div class="text-4xl mb-2">🏭</div>
                            <div class="font-bold">Chưa có thiết bị nào trong danh mục</div>
                        </td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-slate-50 border-t border-slate-100">
            <?php echo e($assets->links()); ?>

        </div>
    </div>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/asset/asset-manager.blade.php ENDPATH**/ ?>