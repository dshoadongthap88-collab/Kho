<div style="font-family: 'Times New Roman', Times, serif;">
    <div x-data="{ showLightbox: false, lightboxUrl: '' }">
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
        <!-- Tabs -->
        <div class="flex bg-slate-100 p-1 rounded-lg w-full mb-2">
            <button wire:click="switchTab('materials')" class="flex-1 py-2 text-sm font-bold uppercase rounded-md transition-all <?php echo e($activeTab === 'materials' ? 'bg-white text-indigo-600 shadow' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'); ?>">
                Danh Mục Vật Tư
            </button>
            <button wire:click="switchTab('equipments')" class="flex-1 py-2 text-sm font-bold uppercase rounded-md transition-all <?php echo e($activeTab === 'equipments' ? 'bg-white text-indigo-600 shadow' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'); ?>">
                Danh Mục Thiết Bị
            </button>
        </div>
        
        <div class="flex items-center gap-2 w-full overflow-x-auto pb-1 hide-scrollbar">
            <!-- Date Filter Standard -->
            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200 shadow-inner focus-within:ring-2 focus-within:ring-indigo-100 transition-all h-[38px] shrink-0">
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Từ ngày</label>
                    <input type="date" wire:model.live="dateFrom" class="text-[12px] border-none bg-transparent focus:ring-0 p-0 font-bold text-slate-700">
                </div>
                <div class="w-px h-3 bg-slate-300 mx-1"></div>
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-tighter">Đến ngày</label>
                    <input type="date" wire:model.live="dateTo" class="text-[12px] border-none bg-transparent focus:ring-0 p-0 font-bold text-slate-700">
                </div>
            </div>

            <!-- Search Standard -->
            <div class="relative w-64 h-[38px] shrink-0">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm tên, mã, hãng..." class="w-full h-full pl-9 pr-3 py-2 text-[12px] font-bold rounded-lg border border-slate-200 focus:ring-indigo-500 shadow-sm transition-all bg-slate-50 focus:bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div class="w-px h-6 bg-slate-200 mx-1 shrink-0"></div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
                <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'bulk-actions-container'; ?>wire:key="bulk-actions-container" class="flex items-center gap-1.5 pr-2 border-r border-slate-300 mr-1 h-[38px] shrink-0">
                    <span class="text-[10px] font-black text-indigo-700 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 flex items-center h-full">CHỌN: <?php echo e(count($selectedIds)); ?></span>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) === 1): ?>
                        <button <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'btn-edit-selected'; ?>wire:key="btn-edit-selected" type="button" 
                                wire:click="openModal(<?php echo e($selectedIds[0]); ?>)" 
                                class="flex items-center justify-center gap-1 px-3 h-full bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[11px] font-black transition-all hover:scale-105 active:scale-95 shadow-sm cursor-pointer">
                            <span>✏️</span> SỬA
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <button <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'btn-delete-selected'; ?>wire:key="btn-delete-selected" type="button" 
                            onclick="confirm('Xác nhận xóa các vật tư đã chọn?') || event.stopImmediatePropagation()"
                            wire:click="deleteSelected" 
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center gap-1 px-3 h-full bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-[11px] font-black transition-all hover:scale-105 active:scale-95 shadow-sm cursor-pointer">
                        <span wire:loading.remove wire:target="deleteSelected">🗑️</span>
                        <span wire:loading wire:target="deleteSelected" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        XÓA
                    </button>

                    <button <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'btn-print-selected'; ?>wire:key="btn-print-selected" type="button" 
                            wire:click="printLabels" 
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center gap-1 px-3 h-full bg-slate-800 hover:bg-black text-white rounded-lg text-[11px] font-black transition-all hover:scale-105 active:scale-95 shadow-sm cursor-pointer">
                        <span wire:loading.remove wire:target="printLabels">📄</span>
                        <span wire:loading wire:target="printLabels" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        IN
                    </button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'materials'): ?>
            <div class="flex gap-1 bg-slate-100 p-1 rounded-lg border border-slate-300 h-[38px] shrink-0">
                <button type="button"
                        wire:click="setFilterMode('all')"
                        class="px-3 h-full text-[11px] font-black uppercase rounded <?php echo e($filterMode === 'all' ? 'bg-blue-600 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-50'); ?>">
                    Tất cả
                </button>
                <button type="button"
                        wire:click="setFilterMode('low_stock')"
                        class="px-3 h-full text-[11px] font-black uppercase rounded <?php echo e($filterMode === 'low_stock' ? 'bg-orange-600 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-50'); ?>">
                    Sắp hết tồn
                </button>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button type="button" wire:click="openModal" class="h-[38px] shrink-0 bg-gradient-to-r from-blue-600 to-blue-700 font-black hover:from-blue-700 hover:to-blue-800 text-white px-3 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md active:scale-95 uppercase">
                <span>➕</span> THÊM MỚI
            </button>
            <button type="button" wire:click="$set('showImportModal', true)" wire:loading.attr="disabled" class="h-[38px] shrink-0 bg-gradient-to-r from-emerald-600 to-emerald-700 font-black hover:from-emerald-700 hover:to-emerald-800 text-white px-3 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md active:scale-95 uppercase">
                <span>📥</span> IMPORT EXCEL
            </button>
            <button type="button" wire:click="printAll" wire:loading.attr="disabled" class="h-[38px] shrink-0 bg-gradient-to-r from-slate-800 to-slate-900 font-black hover:from-indigo-600 hover:to-indigo-700 text-white px-3 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md active:scale-95 uppercase">
                <span>🖨️</span> IN FILE PDF
            </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'materials'): ?>
            <button type="button" wire:click="saveMinStocks" wire:loading.attr="disabled" 
                    x-data="{ saved: false }" 
                    @min-stocks-saved.window="saved = true; setTimeout(() => saved = false, 3000)"
                    class="h-[38px] shrink-0 bg-gradient-to-r from-amber-500 to-amber-600 font-black hover:from-amber-600 hover:to-amber-700 text-white px-3 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md active:scale-95 uppercase">
                <span x-show="!saved">💾</span> 
                <span x-show="!saved">LƯU TỒN TỐI THIỂU</span>
                <span x-cloak x-show="saved" class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    ĐÃ LƯU
                </span>
            </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('warning')): ?>
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.500ms
                 class="bg-amber-50 text-amber-600 px-6 py-3 rounded-xl shadow-lg border border-amber-200 font-black text-[13px] flex items-center gap-2 pointer-events-auto">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?php echo e(session('warning')); ?>

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

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'materials'): ?>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-black tracking-widest">
                    <th class="px-2 py-2 w-10 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll([<?php echo e(implode(',', $allProductIdsOnPage)); ?>])" 
                               <?php echo e(count(array_intersect(array_map('strval', $allProductIdsOnPage), $selectedIds)) === count($allProductIdsOnPage) && count($allProductIdsOnPage) > 0 ? 'checked' : ''); ?>

                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-3 py-4 text-[11px] font-black uppercase tracking-tighter text-slate-500">Mã vật tư</th>
                    <th class="px-2 py-2 w-16 text-center">Hình ảnh</th>
                    <th class="px-2 py-2">TÊN VẬT TƯ</th>
                    <th class="px-2 py-2">Phân loại</th>
                    <th class="px-2 py-2">Hãng sản xuất</th>
                    <th class="px-2 py-2">Mã Code NCC</th>
                    <th class="px-2 py-2">Hạn dùng</th>
                    <th class="px-2 py-2 text-center">Số lượng</th>
                    <th class="px-2 py-2">Vị trí</th>
                    <th class="px-2 py-2">Tình trạng</th>
                    <th class="px-2 py-2">Tồn tối thiểu</th>
                    <th class="px-2 py-2">Ghi chú</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'product-'.e($product->id).''; ?>wire:key="product-<?php echo e($product->id); ?>" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors <?php echo e(in_array((string)$product->id, $selectedIds) ? 'bg-indigo-50/50' : ''); ?>">
                        <td class="px-2 py-1.5 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedIds" value="<?php echo e($product->id); ?>" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </td>
                        <td class="px-2 py-1.5 font-mono text-sm text-blue-600"><?php echo e($product->code); ?></td>
                        <td class="px-2 py-1.5 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->image): ?>
                                <div class="relative group inline-block">
                                    <img src="<?php echo e(asset('storage/' . $product->image)); ?>" 
                                         alt="Img" 
                                         class="w-12 h-12 object-cover rounded shadow-sm border cursor-zoom-in transition-transform hover:scale-110" 
                                         @click="lightboxUrl = '<?php echo e(asset('storage/' . $product->image)); ?>'; showLightbox = true">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center rounded transition-opacity pointer-events-none">
                                        <span class="text-white text-[10px]">🔍</span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <button wire:click="openModal(<?php echo e($product->id); ?>)" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase underline decoration-2 underline-offset-4">
                                    Tải ảnh
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-2 py-1.5 text-[11px] font-black text-gray-800 uppercase tracking-tight">
                            <?php echo e($product->name); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->category): ?>
                                <div class="text-[9px] text-gray-500 mt-1 uppercase"><?php echo e($product->category->name); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-2 py-1.5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->type === 'product_produced'): ?>
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs">SX</span>
                            <?php elseif($product->type === 'product_purchased'): ?>
                                <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-xs">Mua</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-2 py-1.5 text-gray-600"><?php echo e($product->brand); ?></td>
                        <td class="px-2 py-1.5 text-sm font-semibold text-purple-700"><?php echo e($product->batch_number); ?></td>
                        <td class="px-2 py-1.5 text-sm <?php echo e($product->is_expiring_soon ? 'text-red-600 font-bold' : 'text-gray-600'); ?>">
                            <?php echo e($product->expiry_date ? $product->expiry_date->format('d/m/Y') : '-'); ?>

                        </td>
                        <td class="px-2 py-1.5 text-center">
                            <span class="px-1.5 py-1 text-[11px] rounded-full text-xs font-bold 
                                <?php echo e($product->is_low_stock ? 'bg-orange-600 text-white' : (($product->inventory?->quantity ?? 0) > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500')); ?>">
                                <?php echo e(number_format($product->inventory?->quantity ?? 0)); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->is_low_stock): ?> ⚠️ <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </td>
                        <td class="px-2 py-1.5 text-gray-600 font-bold"><?php echo e($product->inventory?->warehouse_location ?? $product->location); ?></td>
                        <td class="px-2 py-1.5">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->status === 'active'): ?>
                                <span class="bg-green-100 text-green-700 px-1.5 py-1 text-[11px] rounded text-xs">Đang kinh doanh</span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-700 px-1.5 py-1 text-[11px] rounded text-xs">Ngừng kinh doanh</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <input type="number" 
                                   wire:model.defer="minStocks.<?php echo e($product->id); ?>"
                                   class="w-20 text-xs font-black text-slate-800 bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded px-1.5 py-1 text-[11px] transition-all text-center shadow-inner placeholder-slate-400"
                                   placeholder="0">
                        </td>
                        <td class="px-2 py-1.5 text-xs text-gray-600 truncate max-w-[150px]" title="<?php echo e($product->description); ?>"><?php echo e($product->description); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                     <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">Không tìm thấy vật tư nào.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            <?php echo e($products->links()); ?>

        </div>
        <?php else: ?>
        <!-- Bảng thiết bị -->
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-black tracking-widest">
                    <th class="px-2 py-2 w-10 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll([<?php echo e(implode(',', $allProductIdsOnPage)); ?>])" 
                               <?php echo e(count(array_intersect(array_map('strval', $allProductIdsOnPage), $selectedIds)) === count($allProductIdsOnPage) && count($allProductIdsOnPage) > 0 ? 'checked' : ''); ?>

                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-2 py-2">MÃ THIẾT BỊ</th>
                    <th class="px-2 py-2">TÊN THIẾT BỊ</th>
                    <th class="px-2 py-2">LOẠI THIẾT BỊ</th>
                    <th class="px-2 py-2">MÃ TÀI SẢN</th>
                    <th class="px-2 py-2">NGƯỜI QUẢN LÝ</th>
                    <th class="px-2 py-2">TÌNH TRẠNG</th>
                    <th class="px-2 py-2 text-center w-24">THAO TÁC</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $equipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $equipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors <?php echo e(in_array($equipment->id, $selectedIds) ? 'bg-indigo-50/30' : ''); ?>">
                        <td class="px-2 py-1.5 text-center no-print">
                            <input type="checkbox" value="<?php echo e($equipment->id); ?>" wire:model="selectedIds" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </td>
                        <td class="px-2 py-1.5 text-sm font-mono text-indigo-600"><?php echo e($equipment->equipment_code ?: '-'); ?></td>
                        <td class="px-2 py-1.5 text-sm font-black text-gray-800 uppercase"><?php echo e($equipment->name); ?></td>
                        <td class="px-2 py-1.5 text-sm font-bold text-gray-700 uppercase"><?php echo e($equipment->machine_type ?: '-'); ?></td>
                        <td class="px-2 py-1.5 text-sm font-mono text-indigo-600"><?php echo e($equipment->asset_code ?: '-'); ?></td>
                        <td class="px-2 py-1.5 text-sm text-gray-600 font-bold uppercase"><?php echo e($equipment->manager ?: '-'); ?></td>
                        <td class="px-2 py-1.5 text-sm text-gray-600 font-bold uppercase">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($equipment->warranty_status === 'Còn bảo hành'): ?>
                                <span class="bg-green-100 text-green-800 px-1.5 py-1 text-[11px] rounded text-[10px]"><?php echo e($equipment->warranty_status); ?></span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-800 px-1.5 py-1 text-[11px] rounded text-[10px]"><?php echo e($equipment->warranty_status ?: 'Hết bảo hành'); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-2 py-1.5 text-center">
                            <button wire:click="openModal(<?php echo e($equipment->id); ?>)" class="text-indigo-600 hover:text-indigo-800 font-black uppercase text-[10px] bg-indigo-50 px-1.5 py-1 text-[11px].5 rounded hover:bg-indigo-100 transition-colors">
                                Sửa
                            </button>
                            <button wire:click="deleteEquipment(<?php echo e($equipment->id); ?>)" wire:confirm="Bạn có chắc chắn muốn xóa thiết bị này?" class="text-rose-600 hover:text-rose-800 font-black uppercase text-[10px] bg-rose-50 px-1.5 py-1 text-[11px].5 rounded hover:bg-rose-100 transition-colors ml-1 mt-1 sm:mt-0">
                                Xóa
                            </button>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Không tìm thấy thiết bị nào.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            <?php echo e($equipments->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-y-auto max-h-[85vh] shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4"><?php echo e($activeTab === 'materials' ? ($isEdit ? 'Chỉnh sửa vật tư' : 'Thêm vật tư mới') : ($isEdit ? 'Chỉnh sửa thiết bị' : 'Thêm thiết bị mới')); ?></h3>
                        <div class="grid grid-cols-2 gap-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'materials'): ?>
                            <div class="col-span-2 bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 mb-2">
                                <label class="block text-[11px] font-black text-indigo-900 uppercase tracking-widest mb-3">📸 Hình ảnh vật tư</label>
                                <div class="flex items-center gap-6">
                                    <div class="relative group">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image): ?>
                                            <img src="<?php echo e($image->temporaryUrl()); ?>" class="h-24 w-24 object-cover rounded-xl border-2 border-white shadow-md ring-2 ring-indigo-200">
                                            <button wire:click="$set('image', null)" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full p-1 shadow-lg hover:bg-rose-600 transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        <?php elseif($productId && ($currentProduct = \App\Models\Product::find($productId)) && $currentProduct->image): ?>
                                            <img src="<?php echo e(asset('storage/' . $currentProduct->image)); ?>" class="h-24 w-24 object-cover rounded-xl border-2 border-white shadow-md ring-2 ring-slate-200">
                                        <?php else: ?>
                                            <div class="h-24 w-24 bg-white border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center text-slate-400 group-hover:border-indigo-400 group-hover:text-indigo-400 transition-all">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <span class="text-[9px] font-black mt-1 uppercase">Chưa có ảnh</span>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <input type="file" wire:model="image" id="image-upload" class="hidden">
                                            <label for="image-upload" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-indigo-200 rounded-lg shadow-sm text-xs font-black text-indigo-700 hover:bg-indigo-50 cursor-pointer transition-all active:scale-95">
                                                📂 CHỌN ẢNH TỪ MÁY
                                            </label>
                                        </div>
                                        <p class="mt-2 text-[10px] text-slate-500 font-bold leading-tight uppercase">
                                            Hỗ trợ: JPG, PNG, WEBP<br>Dung lượng tối đa: 2MB
                                        </p>
                                        <div wire:loading wire:target="image" class="mt-2 text-[10px] font-black text-indigo-600 animate-pulse">
                                            ĐANG TẢI...
                                        </div>
                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-rose-500 text-[10px] font-bold mt-1 block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã vật tư</label>
                                <input type="text" wire:model="code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tên vật tư</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Danh mục</label>
                                <select wire:model="category_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-2 flex gap-4 my-2">
                                <label class="block text-sm font-medium text-gray-700 items-center flex">Phân loại gốc:</label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="type" value="product_produced" class="text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Thành phẩm (SX)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="type" value="product_purchased" class="text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Thành phẩm (Mua)</span>
                                </label>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hãng sản xuất</label>
                                <input type="text" wire:model="brand" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['brand'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Số lượng</label>
                                <input type="text" inputmode="numeric" wire:model.lazy="quantity" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Vị trí</label>
                                <input type="text" wire:model="location" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['location'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã Code NCC (Không bắt buộc)</label>
                                <input type="text" wire:model="batch_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['batch_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hạn sử dụng</label>
                                <input type="date" wire:model="expiry_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['expiry_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                                <textarea wire:model="description" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" rows="2" placeholder="Thêm ghi chú..."></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tồn tối thiểu</label>
                                <input type="text" inputmode="numeric" wire:model.lazy="min_stock" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['min_stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <?php else: ?>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã tài sản</label>
                                <input type="text" wire:model="asset_code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['asset_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tên thiết bị</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã thiết bị</label>
                                <input type="text" wire:model="equipment_code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['equipment_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Loại thiết bị</label>
                                <input type="text" wire:model="machine_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['machine_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Người quản lý</label>
                                <input type="text" wire:model="manager" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['manager'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tình trạng</label>
                                <select wire:model="warranty_status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                    <option value="Còn bảo hành">Còn bảo hành</option>
                                    <option value="Hết bảo hành">Hết bảo hành</option>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['warranty_status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                                <div class="col-span-2 bg-rose-50 p-3 rounded-lg border border-rose-200 mt-2">
                                    <ul class="list-disc list-inside">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <li class="text-rose-600 text-[11px] font-black uppercase"><?php echo e($error); ?></li>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-2">
                        <button type="button" wire:click="save" wire:loading.attr="disabled" class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-sm font-black text-white hover:bg-indigo-700 focus:outline-none transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">💾 LƯU LẠI</span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                ĐANG LƯU...
                            </span>
                        </button>
                        <button type="button" wire:click="$set('showModal', false)" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-sm font-black text-gray-700 hover:bg-gray-50 focus:outline-none transition-all">
                            HỦY BỎ
                        </button>
                        
                        <div wire:loading wire:target="save" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center z-50 rounded-lg">
                            <div class="bg-white p-4 rounded-2xl shadow-xl border border-indigo-100 flex flex-col items-center gap-3">
                                <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-sm font-black text-indigo-900 uppercase">Đang xử lý dữ liệu...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Import Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showImportModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showImportModal', false)"></div>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Nhập dữ liệu từ Excel</h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-2">Tải tệp Excel (.xlsx, .xls) hoặc CSV để nhập hàng loạt vật tư.</p>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium underline">Tải tệp mẫu (.xlsx) tại đây</a>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chọn tệp của bạn</label>
                            <input type="file" wire:model="excelFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['excelFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div wire:loading wire:target="excelFile" class="mt-2 text-sm text-blue-600">
                            Đang tải tệp lên...
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="importExcel" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Bắt đầu nhập
                        </button>
                        <button type="button" wire:click="$set('showImportModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <style>
        @media print {
            .no-print, header, nav, aside, .sidebar, .sidebar-toolbar,
            .bg-white.p-6.rounded-2xl, .bg-white.rounded-xl.shadow-sm,
            .px-4.py-3.bg-gray-50.border-t, button, input, select {
                display: none !important;
            }
            body {
                background: white !important;
                font-family: 'Times New Roman', Times, serif !important;
                padding: 0 !important;
                margin: 0 !important;
                color: black !important;
            }
            main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .print-only { display: block !important; }
            .print-container { width: 100%; padding: 15mm; }
            table.print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px;
            }
            table.print-table th, table.print-table td {
                border: 1.5px solid black !important;
                padding: 6px 8px !important;
                font-size: 13px !important;
                color: black !important;
            }
            table.print-table th {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 12px !important;
            }
            @page { size: A4 portrait; margin: 10mm; }
        }
        .print-only { display: none; }
    </style>

    <!-- PHIẾU IN DANH MỤC VẬT TƯ -->
    <div class="print-only print-container" style="font-family: 'Times New Roman', Times, serif;">
        <!-- Header công ty -->
        <div style="margin-bottom: 20px;">
            <h1 style="font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 0;">CTY TNHH ABC</h1>
            <p style="font-size: 14px; margin: 4px 0;">Quản lý kho: HÓC MÔN</p>
            <p style="font-size: 14px; margin: 4px 0;">SĐT: 0708091050</p>
        </div>

        <div style="border-bottom: 2px solid black; margin-bottom: 20px;"></div>

        <!-- Tiêu đề phiếu -->
        <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 3px; margin: 0;"><?php echo e($activeTab === 'materials' ? 'BẢNG DANH MỤC VẬT TƯ' : 'BẢNG DANH MỤC THIẾT BỊ'); ?></h2>
            <p style="font-size: 13px; font-style: italic; margin-top: 6px;">
                Ngày <?php echo e(now()->format('d')); ?> tháng <?php echo e(now()->format('m')); ?> năm <?php echo e(now()->format('Y')); ?>

            </p>
        </div>

        <!-- Bảng sản phẩm / thiết bị -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">STT</th>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'materials'): ?>
                    <th style="width: 110px;">Mã Vật Tư</th>
                    <th>TÊN VẬT TƯ</th>
                    <th style="width: 130px;">Hãng SX</th>
                    <th style="width: 80px; text-align: center;">Số lượng</th>
                    <th style="width: 100px; text-align: center;">Vị trí</th>
                    <?php else: ?>
                    <th>TÊN THIẾT BỊ</th>
                    <th style="width: 110px;">Mã Tài Sản</th>
                    <th style="width: 100px;">Biển Số Xe</th>
                    <th style="width: 130px;">Tên Tài Xế</th>
                    <th style="width: 100px;">SĐT</th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $printItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td style="text-align: center;"><?php echo e($index + 1); ?></td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'materials'): ?>
                    <td style="font-family: monospace; text-transform: uppercase;"><?php echo e($item->code); ?></td>
                    <td style="font-weight: bold;"><?php echo e($item->name); ?></td>
                    <td><?php echo e($item->brand ?? '-'); ?></td>
                    <td style="text-align: center; font-weight: bold;"><?php echo e(number_format($item->inventory?->quantity ?? 0)); ?></td>
                    <td style="text-align: center;"><?php echo e($item->location ?? '-'); ?></td>
                    <?php else: ?>
                    <td style="font-weight: bold; text-transform: uppercase;"><?php echo e($item->name); ?></td>
                    <td style="font-family: monospace; text-transform: uppercase;"><?php echo e($item->asset_code); ?></td>
                    <td style="text-transform: uppercase;"><?php echo e($item->license_plate ?? '-'); ?></td>
                    <td style="text-transform: uppercase;"><?php echo e($item->driver_name ?? '-'); ?></td>
                    <td style="font-family: monospace;"><?php echo e($item->phone_number ?? '-'); ?></td>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = count($printItems); $i < max(8, count($printItems)); $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td style="text-align: center; color: transparent;">_</td>
                    <td></td><td></td><td></td><td></td>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'materials'): ?><td></td><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>

        <!-- Chữ ký -->
        <div style="display: flex; justify-content: space-between; margin-top: 50px; text-align: center; font-size: 14px;">
            <div style="width: 45%;">
                <p style="font-weight: bold; text-transform: uppercase;">Người lập phiếu</p>
                <p style="font-size: 11px; font-style: italic;">(Ký, ghi rõ họ tên)</p>
                <div style="height: 80px;"></div>
                <p style="font-weight: bold; text-transform: uppercase;"><?php echo e(auth()->user()->name ?? '........................'); ?></p>
            </div>
            <div style="width: 45%;">
                <p style="font-weight: bold; text-transform: uppercase;">Quản lý kho</p>
                <p style="font-size: 11px; font-style: italic;">(Ký, ghi rõ họ tên)</p>
                <div style="height: 80px;"></div>
                <p style="font-weight: bold;">.................................</p>
            </div>
        </div>

        <div style="text-align: right; margin-top: 30px; font-size: 10px; font-style: italic; color: #666;">
            In lúc: <?php echo e(now()->format('d/m/Y H:i')); ?>

        </div>
    </div>

        <?php
        $__scriptKey = '2485275746-0';
        ob_start();
    ?>
    <script>
        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/product-catalog.blade.php ENDPATH**/ ?>