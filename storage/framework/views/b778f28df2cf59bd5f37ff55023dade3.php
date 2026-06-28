<div x-data="{ showLightbox: false, lightboxUrl: '' }" style="font-family: 'Times New Roman', Times, serif;">
    <!-- Lightbox Modal -->
    <div x-show="showLightbox"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 no-print"
         style="display: none;"
         @click="showLightbox = false"
         @keydown.escape.window="showLightbox = false">
        <div class="relative max-w-5xl w-full flex flex-col items-center">
            <button @click="showLightbox = false" class="absolute -top-12 right-0 text-white hover:text-gray-300 text-4xl font-black transition-all">✕</button>
            <img :src="lightboxUrl" class="max-h-[85vh] max-w-full rounded-lg shadow-2xl border-4 border-white object-contain bg-white/10">
            <div class="mt-4 text-white font-black text-sm uppercase tracking-widest bg-black/50 px-4 py-2 rounded-full">Bấm bên ngoài hoặc phím ESC để thoát</div>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-4 no-print relative z-10 bg-white p-3 rounded-xl shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="relative w-64">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm mã/tên tài sản..." class="w-full pl-9 pr-3 py-2 text-[12px] font-bold rounded-lg border-slate-200 focus:ring-indigo-500 shadow-sm transition-all bg-slate-50 focus:bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 shadow-inner">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Từ ngày</label>
                <input type="date" wire:model.live="filterDateFrom" class="text-[12px] border-none bg-transparent focus:ring-0 p-0 font-bold text-slate-700">
                <div class="w-px h-3 bg-slate-300 mx-1"></div>
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Đến ngày</label>
                <input type="date" wire:model.live="filterDateTo" class="text-[12px] border-none bg-transparent focus:ring-0 p-0 font-bold text-slate-700">
            </div>

            <!-- Status Filter -->
            <div class="flex gap-1 bg-slate-100 p-1 rounded-lg">
                <button wire:click="$set('filterStatus', '')" class="px-3 py-1 text-[10px] font-black uppercase rounded transition-all <?php echo e($filterStatus === '' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'); ?>">Tất cả</button>
                <button wire:click="$set('filterStatus', 'maintenance_required')" class="px-3 py-1 text-[10px] font-black uppercase rounded transition-all <?php echo e($filterStatus === 'maintenance_required' ? 'bg-red-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'); ?>">Cần bảo dưỡng</button>
                <button wire:click="$set('filterStatus', 'maintenance_done')" class="px-3 py-1 text-[10px] font-black uppercase rounded transition-all <?php echo e($filterStatus === 'maintenance_done' ? 'bg-green-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'); ?>">Đã bảo dưỡng</button>
                <button wire:click="$set('filterStatus', 'normal')" class="px-3 py-1 text-[10px] font-black uppercase rounded transition-all <?php echo e($filterStatus === 'normal' ? 'bg-blue-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'); ?>">Bình thường</button>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
                <div class="flex items-center gap-1.5 pr-2 border-r border-slate-300 mr-1 py-1">
                    <span class="text-[10px] font-black text-indigo-700 bg-indigo-50 px-2 py-1 rounded border border-indigo-100">CHỌN: <?php echo e(count($selectedIds)); ?></span>
                    <button wire:click="deleteSelected" onclick="confirm('Xác nhận xóa các bản ghi đã chọn?') || event.stopImmediatePropagation()" class="flex items-center gap-1 px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-[11px] font-black transition-all hover:scale-105 active:scale-95 shadow-sm cursor-pointer">🗑️ XÓA</button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button wire:click="openModal" class="bg-gradient-to-r from-indigo-600 to-indigo-700 font-black hover:from-indigo-700 hover:to-indigo-800 text-white px-4 py-1.5 rounded-lg text-[11px] flex items-center gap-1 transition-all shadow-sm hover:shadow-md active:scale-95">
                <span>➕</span> NHẬP THỦ CÔNG
            </button>
            <button wire:click="$set('showImportModal', true)" class="bg-gradient-to-r from-emerald-600 to-emerald-700 font-black hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-1.5 rounded-lg text-[11px] flex items-center gap-1 transition-all shadow-sm hover:shadow-md active:scale-95">
                📥 IMPORT EXCEL
            </button>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms
                 class="bg-emerald-50 text-emerald-600 px-6 py-3 rounded-xl shadow-lg border border-emerald-200 font-black text-[13px] flex items-center gap-2 pointer-events-auto">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?php echo e(session('message')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity.duration.500ms
                 class="bg-rose-50 text-rose-600 px-6 py-3 rounded-xl shadow-lg border border-rose-200 font-black text-[13px] flex items-center gap-2 pointer-events-auto">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Modal Nhập Thủ Công -->
    <div x-data="{ openModal: <?php if ((object) ('showModal') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showModal'->value()); ?>')<?php echo e('showModal'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showModal'); ?>')<?php endif; ?> }">
        <div x-show="openModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 no-print"
             style="display: none;"
             @keydown.escape.window="openModal = false"
             x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
                 @click.stop>
                <!-- Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white flex items-center justify-between">
                    <h2 class="text-xl font-black uppercase tracking-tight">
                        <?php echo e($isEdit ? '✏️ Chỉnh sửa bản ghi ODO' : '➕ Nhập thủ công số giờ ODO'); ?>

                    </h2>
                    <button @click="openModal = false" class="text-white/80 hover:text-white transition-all text-2xl font-bold">&times;</button>
                </div>

                <!-- Form Content -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm font-bold">
                            <div class="font-bold mb-1">⚠️ Vui lòng kiểm tra lại:</div>
                            <ul class="list-disc list-inside">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <li><?php echo e($error); ?></li>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </ul>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Mã tài sản -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 mb-1">Mã tài sản <span class="text-red-500">*</span></label>
                            <select wire:model.live="selectedAssetId"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 <?php $__errorArgs = ['selectedAssetId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    :class="{'bg-gray-100 cursor-not-allowed': <?php echo e($isEdit ? 'true' : 'false'); ?>">
                                <option value="">-- Chọn tài sản --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($asset->id); ?>"><?php echo e($asset->code); ?> - <?php echo e($asset->name); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedAssetId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Ngày đọc -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 mb-1">Ngày đọc <span class="text-red-500">*</span></label>
                            <input type="date" wire:model.live="readingDate"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 <?php $__errorArgs = ['readingDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['readingDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Số giờ ODO -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 mb-1">Số giờ ODO <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" wire:model.live="currentHours"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 <?php $__errorArgs = ['currentHours'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   placeholder="Ví dụ: 1250.50">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['currentHours'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Người vận hành -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 mb-1">Người vận hành</label>
                            <input type="text" wire:model.live="operator"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Tên tài xế/người vận hành">
                        </div>

                        <!-- Tình trạng -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 mb-1">Tình trạng <span class="text-red-500">*</span></label>
                            <select wire:model.live="status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="normal">Bình thường</option>
                                <option value="maintenance_required">Cần bảo dưỡng</option>
                                <option value="maintenance_done">Đã bảo dưỡng</option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Ghi chú -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 mb-1">Ghi chú</label>
                            <textarea wire:model.live="notes" rows="2"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="Ghi chú thêm (nếu có)"></textarea>
                        </div>
                    </div>

                    <!-- Thông tin bổ sung -->
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-sm text-slate-600">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-bold">Lưu ý:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li>Số giờ ODO phải lớn hơn hoặc bằng lần đọc trước của cùng tài sản.</li>
                            <li>Mỗi tài sản chỉ được nhập một lần cho mỗi ngày.</li>
                            <li>Nhấn ESC hoặc click bên ngoài để đóng form.</li>
                        </ul>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t flex justify-end gap-3">
                    <button @click="openModal = false"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-bold hover:bg-gray-100 transition-all">
                        Huỷ
                    </button>
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-lg font-bold hover:from-indigo-700 hover:to-indigo-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg wire:loading class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading>Đang lưu...</span>
                        <span wire:loading.remove><?php echo e($isEdit ? 'Cập nhật' : 'Lưu'); ?></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div x-data="{ openImportModal: <?php if ((object) ('showImportModal') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showImportModal'->value()); ?>')<?php echo e('showImportModal'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showImportModal'); ?>')<?php endif; ?> }">
        <div x-show="openImportModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 no-print"
             style="display: none;"
             @keydown.escape.window="openImportModal = false"
             x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col"
                 @click.stop>
                <!-- Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white flex items-center justify-between">
                    <h2 class="text-xl font-black uppercase tracking-tight">
                        📥 Nhập dữ liệu ODO từ Excel
                    </h2>
                    <button @click="openImportModal = false" class="text-white/80 hover:text-white transition-all text-2xl font-bold">&times;</button>
                </div>

                <!-- Form Content -->
                <div class="flex-1 overflow-y-auto p-6 space-y-6">
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 flex items-start gap-3">
                        <svg class="w-6 h-6 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div class="text-sm text-emerald-800">
                            <p class="font-bold mb-1">Hướng dẫn nhập liệu:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs opacity-90">
                                <li>Sử dụng file mẫu để đảm bảo đúng cấu trúc cột.</li>
                                <li>Định dạng file hỗ trợ: .xlsx, .xls, .csv (Tối đa 10MB).</li>
                                <li>Mã tài sản phải tồn tại trong hệ thống.</li>
                                <li>Ngày đọc định dạng: Y-m-d hoặc d/m/Y.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <!-- Nút tải file mẫu -->
                        <div class="flex justify-center">
                            <a href="#" class="flex items-center gap-2 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-bold hover:bg-slate-200 transition-all border border-slate-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Tải File Mẫu (.xlsx)
                            </a>
                        </div>

                        <!-- File Upload Area -->
                        <div class="relative group">
                            <input type="file" wire:model="excelFile"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                   accept=".xlsx, .xls, .csv">
                            <div class="border-2 border-dashed border-slate-300 rounded-2xl p-8 text-center group-hover:border-emerald-500 transition-all bg-slate-50 group-hover:bg-emerald-50/30">
                                <div class="mx-auto w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-4.212a4 4 0 014.45-4.45a4 4 0 014.45 4.45A4 4 0 0111.12 16m-4 0h12m-4 0v4m-4-4v4m4-4v4"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-slate-700">
                                    <?php echo e($excelFile ? 'Đã chọn file: ' . basename($excelFile) : 'Kéo thả file vào đây hoặc Click để chọn file'); ?>

                                </p>
                                <p class="text-xs text-slate-500 mt-1">Hỗ trợ: .xlsx, .xls, .csv (Tối đa 10MB)</p>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['excelFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-xs text-center font-bold"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t flex justify-end gap-3">
                    <button @click="openImportModal = false"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-bold hover:bg-gray-100 transition-all">
                        Huỷ
                    </button>
                    <button wire:click="importExcel"
                            wire:loading.attr="disabled"
                            class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-lg font-bold hover:from-emerald-700 hover:to-emerald-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg wire:loading class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading>Đang nhập liệu...</span>
                        <span wire:loading.remove>Bắt đầu Import</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-black tracking-widest">
                    <th class="px-6 py-4 w-10 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll([<?php echo e(implode(',', $allReadingIdsOnPage)); ?>])"
                               <?php echo e(count(array_intersect(array_map('strval', $allReadingIdsOnPage), $selectedIds)) === count($allReadingIdsOnPage) && count($allReadingIdsOnPage) > 0 ? 'checked' : ''); ?>

                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-4 py-3">Mã tài sản</th>
                    <th class="px-4 py-3">Tên tài sản</th>
                    <th class="px-4 py-3 text-center">Ngày đọc</th>
                    <th class="px-4 py-3 text-center">Số giờ</th>
                    <th class="px-4 py-3">Người vận hành</th>
                    <th class="px-4 py-3 text-center">Tình trạng</th>
                    <th class="px-4 py-3">Ghi chú</th>
                    <th class="px-4 py-3 text-center no-print">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $readings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'reading-'.e($reading->id).''; ?>wire:key="reading-<?php echo e($reading->id); ?>" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors <?php echo e(in_array((string)$reading->id, $selectedIds) ? 'bg-indigo-50/50' : ''); ?>">
                        <td class="px-6 py-4 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedIds" value="<?php echo e($reading->id); ?>" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </td>
                        <td class="px-4 py-3 font-mono text-sm text-blue-600"><?php echo e($reading->product->code); ?></td>
                        <td class="px-4 py-3 text-[11px] font-black text-gray-800 uppercase tracking-tight"><?php echo e($reading->product->name); ?></td>
                        <td class="px-4 py-3 text-center text-sm"><?php echo e($reading->reading_date->format('d/m/Y')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold <?php echo e($reading->status === 'maintenance_required' ? 'bg-red-100 text-red-700' : ($reading->status === 'maintenance_done' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700')); ?>">
                                <?php echo e(number_format($reading->current_hours, 2)); ?> giờ
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm"><?php echo e($reading->operator ?: '-'); ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reading->status === 'maintenance_required'): ?>
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Cần bảo dưỡng</span>
                            <?php elseif($reading->status === 'maintenance_done'): ?>
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Đã bảo dưỡng</span>
                            <?php else: ?>
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold">Bình thường</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm"><?php echo e($reading->notes ?: '-'); ?></td>
                        <td class="px-4 py-3 text-center no-print">
                            <button wire:click="openModal(<?php echo e($reading->id); ?>)" class="text-amber-600 hover:text-amber-800 text-sm font-bold">✏️ Sửa</button>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">Chưa có bản ghi số giờ odo nào.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            <?php echo e($readings->links()); ?>

        </div>
    </div>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/odo-manager.blade.php ENDPATH**/ ?>