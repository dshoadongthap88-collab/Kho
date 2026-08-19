<div style="font-family: 'Times New Roman', Times, serif;">
    <div class="bg-white p-2 rounded-2xl shadow-xl border border-slate-200 flex flex-wrap items-center justify-between gap-2 mb-8 no-print">
        <div class="flex flex-wrap items-center gap-2">
            <!-- Date Filter Premium -->
            <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-inner focus-within:ring-4 focus-within:ring-indigo-100 transition-all">
                <div class="flex items-center gap-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Giao từ</label>
                    <input type="date" wire:model.live="dateFrom" class="text-[12px] border-none focus:ring-0 p-0 font-black text-slate-700 bg-transparent">
                </div>
                <div class="w-px h-5 bg-slate-200 mx-2"></div>
                <div class="flex items-center gap-2">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Đến</label>
                    <input type="date" wire:model.live="dateTo" class="text-[12px] border-none focus:ring-0 p-0 font-black text-slate-700 bg-transparent">
                </div>
            </div>

            <!-- Search Premium -->
            <div class="relative w-72">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="TÌM TÊN KHÁCH, SỐ PHIẾU..." class="w-full pl-11 pr-4 py-2.5 text-[12px] font-black rounded-2xl border-slate-200 focus:ring-4 focus:ring-indigo-100 shadow-inner transition-all bg-white placeholder:text-slate-300">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filter Payment -->
            <select wire:model.live="filterPayment" class="border-slate-200 rounded-xl px-4 py-2 text-xs font-bold focus:ring-blue-500 shadow-sm">
                <option value="">Tất cả hóa đơn</option>
                <option value="unpaid_or_debt">🔴 Đang nợ</option>
                <option value="paid">🟢 Đã thanh toán</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
                <div class="flex items-center gap-2 pr-3 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4 duration-300">
                    <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded">Chọn: <?php echo e(count($selectedIds)); ?></span>
                    <button type="button" wire:click="deleteSelected" wire:confirm="Xóa <?php echo e(count($selectedIds)); ?> bản ghi nợ đã chọn?" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-black transition">
                        <span>🗑️</span> XÓA
                    </button>
                    <button type="button" wire:click="printSelected" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-black transition">
                        <span>🖨️</span> IN GHÉP
                    </button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button wire:click="exportExcel" class="bg-emerald-600 font-black hover:bg-emerald-700 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md shadow-emerald-100">
                <span>📊</span> EXCEL
            </button>
            <button onclick="window.print()" class="bg-slate-800 font-black hover:bg-slate-900 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md">
                <span>📄</span> PDF
            </button>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('message')): ?>
        <div class="bg-green-100 border border-green-200 text-green-800 p-3 rounded-lg flex gap-3 shadow-sm items-center text-sm print:hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="hidden print:block print-header" style="margin-bottom: 8px;">
        <div style="font-size: 18px; font-weight: bold; text-transform: uppercase; text-align: center; letter-spacing: 1px;">CÔNG TY CPĐT VÀ THI CÔNG HẠ TẦNG VINALPHA</div>
        <div style="font-size: 11px; color: #666; margin-top: 4px; text-align: center;">Ngày in: <?php echo e(now()->format('d/m/Y H:i')); ?></div>
        <hr style="margin-top: 10px; border-top: 1px solid #333;">
    </div>

    <!-- Bảng dữ liệu hướng ngang -->
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full overflow-x-auto print:shadow-none print:border-none print:rounded-none">
        <table class="w-full text-left whitespace-nowrap table-auto border-collapse print-table">
            <thead class="bg-slate-800 text-white text-[11px] font-black uppercase tracking-widest border-b border-slate-700 print:bg-white">
                <tr>
                    <th class="px-6 py-5 w-10 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll([<?php echo e(implode(',', $debts->pluck('id')->toArray())); ?>])" 
                               <?php echo e(count(array_intersect(array_map('strval', $debts->pluck('id')->toArray()), $selectedIds)) === count($debts->pluck('id')->toArray()) && count($debts) > 0 ? 'checked' : ''); ?>

                               class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-4 py-5">SỐ PHIẾU</th>
                    <th class="px-4 py-5">TÊN KHÁCH HÀNG</th>
                    <th class="px-4 py-5 text-right">SỐ TIỀN NỢ (TỔNG)</th>
                    <th class="px-4 py-5 text-right">ĐÃ THANH TOÁN</th>
                    <th class="px-4 py-5 text-right">SỐ TIỀN CÒN LẠI</th>
                    <th class="px-4 py-5 text-center">HẠN THANH TOÁN</th>
                    <th class="px-4 py-5 text-center print:hidden">TÙY CHỈNH</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $debts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $remaining = $report->total_amount - $report->paid_amount;
                        if ($report->due_date) {
                            $dueDate = \Carbon\Carbon::parse($report->due_date);
                        } elseif ($report->delivered_at) {
                            $dueDate = \Carbon\Carbon::parse($report->delivered_at)->addDays(30);
                        } else {
                            $dueDate = null;
                        }
                        $isOverdue = $remaining > 0 && $dueDate && $dueDate->lt(now());
                        $daysOverdueCount = $isOverdue ? $dueDate->diffInDays(now()) : 0;
                    ?>
                    <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'debt-'.e($report->id).''; ?>wire:key="debt-<?php echo e($report->id); ?>" class="hover:bg-slate-50/80 transition group <?php echo e($isOverdue ? 'bg-red-50/30' : ''); ?> <?php echo e(in_array((string)$report->id, $selectedIds) ? 'bg-blue-50/30 is-selected' : ''); ?> print-row">
                        <td class="px-2 py-1.5 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedIds" value="<?php echo e($report->id); ?>" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        <!-- Số phiếu -->
                        <td class="px-2 py-1.5">
                            <button wire:click="viewStockOutDetails(<?php echo e($report->stock_out_id); ?>)" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline transition-colors text-left print:text-black print:no-underline">
                                <?php echo e($report->stockOut->code ?? 'N/A'); ?>

                            </button>
                        </td>
                        
                        <!-- Tên khách hàng -->
                        <td class="px-2 py-1.5 font-semibold text-slate-800">
                            <?php echo e(explode(' (', $report->customer_name)[0]); ?>

                        </td>
                        
                        <!-- Tổng tiền (Nợ) -->
                        <td class="px-2 py-1.5 text-right font-bold text-slate-800">
                            <?php echo e(number_format($report->total_amount)); ?>

                        </td>
                        
                        <!-- Đã thanh toán -->
                        <td class="px-2 py-1.5 text-right font-semibold text-emerald-600 print:text-black">
                            <?php echo e(number_format($report->paid_amount)); ?>

                        </td>
                        
                        <!-- Còn lại -->
                        <td class="px-2 py-1.5 text-right font-bold <?php echo e($remaining > 0 ? 'text-red-600' : 'text-slate-400'); ?> print:text-black">
                            <?php echo e(number_format($remaining)); ?>

                        </td>
                        
                        <!-- Hạn TT -->
                        <td class="px-2 py-1.5 text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remaining == 0 && $report->total_amount > 0): ?>
                                <span class="bg-emerald-100 text-emerald-700 px-1.5 py-1 text-[11px] rounded text-xs font-bold print:bg-white print:text-black">Hoàn tất</span>
                            <?php else: ?>
                                <div class="<?php echo e($isOverdue ? 'text-red-600 font-bold animate-pulse print:animate-none' : 'text-slate-600'); ?>">
                                    <?php echo e($dueDate ? $dueDate->format('d/m/Y') : 'Chưa đặt hạn'); ?>

                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOverdue): ?>
                                    <div class="text-[10px] text-red-500 font-bold">Quá hạn <?php echo e($daysOverdueCount); ?> ngày</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        
                        <!-- Tùy chỉnh (Action) -->
                        <td class="px-2 py-1.5 text-center print:hidden">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remaining > 0): ?>
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="printSingle(<?php echo e($report->id); ?>)" class="text-slate-400 hover:text-blue-600 p-1" title="In phiếu nợ">🖨️</button>
                                    <button wire:click="openPayModal(<?php echo e($report->id); ?>)" class="bg-blue-500 hover:bg-blue-600 text-white px-1.5 py-1 text-[11px] flex items-center justify-center rounded transition" title="Thu tiền">
                                        💰
                                    </button>
                                    <button wire:confirm="Khách hàng đã trả hết nợ hóa đơn này?" wire:click="markAsFullyPaid(<?php echo e($report->id); ?>)" class="bg-emerald-500 hover:bg-emerald-600 text-white px-1.5 py-1 text-[11px] flex items-center justify-center rounded transition" title="Xong nợ">
                                        ✅
                                    </button>
                                    <button wire:click="openEditModal(<?php echo e($report->id); ?>)" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-1.5 py-1 text-[11px] flex items-center justify-center rounded transition" title="Chỉnh sửa">
                                        📝
                                    </button>
                                    <button wire:confirm="Xác nhận xóa bản ghi công nợ này?" wire:click="delete(<?php echo e($report->id); ?>)" class="bg-rose-100 hover:bg-rose-200 text-rose-600 px-1.5 py-1 text-[11px] flex items-center justify-center rounded transition" title="Xóa">
                                        🗑️
                                    </button>
                                </div>
                            <?php else: ?>
                                <button wire:click="openEditModal(<?php echo e($report->id); ?>)" class="text-slate-400 hover:text-blue-500 transition text-lg" title="Sửa lại nếu nhầm">
                                    📝
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                            Không tìm thấy dữ liệu công nợ nào hợp lệ.
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="hidden print:block" style="margin-top: 40px; page-break-inside: avoid;">
        <table style="width: 100%; text-align: center; font-size: 13px;">
            <tr>
                <td style="width: 33%; padding-top: 10px;">
                    <div style="font-weight: bold;">Người lập</div>
                    <div style="font-size: 11px; color: #888; font-style: italic;">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 70px;"></div>
                </td>
                <td style="width: 33%; padding-top: 10px;">
                    <div style="font-weight: bold;">Quản lý</div>
                    <div style="font-size: 11px; color: #888; font-style: italic;">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 70px;"></div>
                </td>
                <td style="width: 33%; padding-top: 10px;">
                    <div style="font-weight: bold;">Xác nhận khách hàng</div>
                    <div style="font-size: 11px; color: #888; font-style: italic;">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 70px;"></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Pagination -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($debts->hasPages()): ?>
        <div class="px-4 py-3 bg-white border border-slate-200 rounded-xl print:hidden">
            <?php echo e($debts->links()); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Modal Thu Tiền / Sửa -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPayModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto print:hidden" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showPayModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-slate-200">
                    <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex items-center gap-3">
                        <span class="text-2xl"><?php echo e($isEditMode ? '📝' : '💰'); ?></span>
                        <h3 class="text-lg font-bold text-blue-900"><?php echo e($isEditMode ? 'Chỉnh sửa Số Tiền' : 'Thu Thêm Nợ'); ?></h3>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nhập số tiền (VNĐ)</label>
                            <input wire:model="payAmount" type="number" min="0" max="<?php echo e($maxPayAmount); ?>" class="block w-full border border-slate-300 rounded-lg shadow-sm py-2 px-3 text-lg font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['payAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-red-500 text-xs mt-1 block font-medium"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">📅 Hạn thanh toán</label>
                            <input wire:model="editDueDate" type="date" class="block w-full border border-slate-300 rounded-lg shadow-sm py-2 px-3 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-[11px] text-slate-400 mt-1">Để trống nếu lấy mặc định (30 ngày sau giao).</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-500">Tổng phiếu xuất:</span>
                                <span class="font-bold text-slate-800"><?php echo e(number_format($maxPayAmount)); ?> đ</span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isEditMode): ?>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Đã thu trước đó:</span>
                                <span class="font-bold text-emerald-600"><?php echo e(number_format($maxPayAmount - $payAmount)); ?> đ</span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-200">
                        <button type="button" wire:click="$set('showPayModal', false)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Hủy
                        </button>
                        <button type="button" wire:click="receivePayment" class="px-4 py-2 bg-blue-600 rounded-lg text-sm font-bold text-white shadow-md hover:bg-blue-700 active:scale-95 transition-transform">
                            <?php echo e($isEditMode ? 'Cập Nhật Lại' : 'Xác Nhận Thu'); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Modal Chi Tiết Phiếu Xuất -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showStockOutModal && $selectedStockOut): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto print:hidden" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showStockOutModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full border border-slate-200">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📄</span>
                            <h3 class="text-lg font-bold text-indigo-900">Chi Tiết Phiếu Xuất: <?php echo e($selectedStockOut->code); ?></h3>
                        </div>
                        <button wire:click="$set('showStockOutModal', false)" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="px-6 py-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-8 bg-slate-50 p-2 rounded-xl border border-slate-100">
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Khách hàng</p>
                                <p class="font-bold text-slate-800"><?php echo e($selectedStockOut->customer_name); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Ngày tạo phiếu</p>
                                <p class="font-bold text-slate-800"><?php echo e($selectedStockOut->created_at->format('d/m/Y H:i')); ?></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Người lập phiếu</p>
                                <p class="font-bold text-slate-800"><?php echo e($selectedStockOut->creator->name ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="font-bold text-slate-700 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                Danh sách mặt hàng
                            </h4>
                            <div class="overflow-hidden border border-slate-200 rounded-lg">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 text-slate-600 text-xs font-bold uppercase">
                                        <tr>
                                            <th class="px-4 py-2">Sản phẩm</th>
                                            <th class="px-4 py-2 text-center">Số lượng</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 italic text-sm">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $selectedStockOut->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <div class="font-medium text-slate-800"><?php echo e($item->product->name); ?></div>
                                                    <div class="text-[10px] text-slate-500">Mã: <?php echo e($item->product->code); ?></div>
                                                </td>
                                                <td class="px-4 py-2 text-center"><?php echo e(number_format($item->quantity)); ?></td>
                                            </tr>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedStockOut->note): ?>
                        <div class="mt-4">
                            <h4 class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Ghi chú</h4>
                            <p class="text-sm text-slate-600 bg-amber-50 p-3 rounded-lg border border-amber-100 italic">
                                "<?php echo e($selectedStockOut->note); ?>"
                            </p>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    
                    <div class="bg-slate-50 px-6 py-4 flex justify-end border-t border-slate-200">
                        <button type="button" wire:click="$set('showStockOutModal', false)" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition-all active:scale-95">
                            Đóng cửa sổ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


    <!-- PHẦN IN CHI TIẾT CÔNG NỢ (Sổ nợ chi tiết) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($printItems) > 0): ?>
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $printItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="print-page p-8 bg-white" style="font-family: 'Times New Roman', serif; min-height: 297mm; page-break-after: always;">
            <div class="flex justify-between items-start mb-6 border-b-2 border-slate-900 pb-4">
                <div>
                    <h1 class="text-xl font-black uppercase">CÔNG TY CPĐT VÀ THI CÔNG HẠ TẦNG VINALPHA</h1>
                    <p class="text-[11px] font-bold text-slate-500">Long An - SĐT: 0708091050</p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter">BIÊN BẢN ĐỐI SOÁT CÔNG NỢ</h2>
                    <p class="text-xs font-bold text-slate-500 mt-1 italic">Phiếu xuất: <span class="text-indigo-700 NOT-italic"><?php echo e($pItem->stockOut->code ?? 'N/A'); ?></span></p>
                </div>
            </div>

            <div class="bg-slate-50 p-2 rounded-lg border-2 border-slate-900 mb-6 grid grid-cols-2 gap-2">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Khách hàng</p>
                    <p class="font-black text-slate-800 text-lg uppercase"><?php echo e($pItem->customer_name); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Hạn thanh toán</p>
                    <p class="font-black <?php echo e(\Carbon\Carbon::parse($pItem->due_date)->lt(now()) ? 'text-red-700' : 'text-slate-800'); ?>">
                        <?php echo e($pItem->due_date ? \Carbon\Carbon::parse($pItem->due_date)->format('d/m/Y') : 'Chưa xác định'); ?>

                    </p>
                </div>
            </div>

            <p class="font-black text-[11px] uppercase mb-2 text-slate-800 italic">Chi tiết hàng hóa bàn giao & Nợ đọng:</p>
            <table class="w-full border-collapse border-2 border-slate-900 mb-8">
                <thead>
                    <tr class="bg-slate-100 uppercase text-[10px] font-black">
                        <th class="border border-slate-900 px-2 py-2 text-center w-10">STT</th>
                        <th class="border border-slate-900 px-2 py-2 text-left">Tên sản phẩm / Quy cách</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-14">ĐVT</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-20">Lượng</th>
                    </tr>
                </thead>
                <tbody class="text-[12px]">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pItem->stockOut): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pItem->stockOut->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $ii): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td class="border border-slate-900 px-2 py-2 text-center"><?php echo e($idx + 1); ?></td>
                            <td class="border border-slate-900 px-2 py-2 font-bold"><?php echo e($ii->product->name); ?> (<?php echo e($ii->product->code); ?>)</td>
                            <td class="border border-slate-900 px-2 py-2 text-center italic"><?php echo e($ii->product->unit); ?></td>
                            <td class="border border-slate-900 px-2 py-2 text-right font-bold"><?php echo e(number_format($ii->quantity)); ?></td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>

            <div class="grid grid-cols-2 gap-2 text-center mt-12 mb-8">
                <div>
                    <p class="font-bold text-sm uppercase">Đại diện khách hàng</p>
                    <p class="text-[10px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-black uppercase tracking-tighter"><?php echo e($pItem->customer_name); ?></p>
                </div>
                <div>
                    <p class="font-bold text-sm uppercase">Kế toán công ty</p>
                    <p class="text-[10px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-black">............................................</p>
                </div>
            </div>

            <div class="text-right mt-12 text-[9px] text-slate-400 italic">
                Hệ thống tự động xuất lúc: <?php echo e(now()->format('d/m/Y H:i:s')); ?>

            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
        $__scriptKey = '3510229823-2';
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
    <style>
    @media print {
        @page { size: A4 portrait; margin: 0; }
        nav, .no-print, [wire\\:loading], button, select, input { display: none !important; }
        body { background: white !important; margin: 0 !important; padding: 0 !important; }
    }
    </style>
</div>
<?php /**PATH D:\Project\resources\views\livewire\warehouse\customer-debt-list.blade.php ENDPATH**/ ?>