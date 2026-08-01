<div style="font-family: 'Times New Roman', Times, serif;">
    <style>
        @media print {
            body { font-family: 'Times New Roman', Times, serif; }
            /* Căn giữa A5/A4, tối ưu lề */
            @page {
                size: A4 landscape;
                margin: 5mm; 
            }
            body, html {
                margin: 0;
                padding: 0;
                background-color: white !important;
            }
            .print-table { page-break-inside: auto; }
            .print-table tr { page-break-inside: avoid; page-break-after: auto; }
            .signatures-section { page-break-inside: avoid; }
            nav, .sidebar-toolbar, button, a, .no-print {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
            }
            body {
                background: white !important;
                font-size: 12pt;
                -webkit-print-color-adjust: exact;
            }
            .print-only {
                display: block !important;
            }
        }
        .custom-toast {
            animation: slideIn 0.3s ease-out forwards;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        @keyframes slideIn {
            0% { transform: translateY(1rem); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .custom-toast.hide {
            animation: fadeOut 0.3s ease-in forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>

    <div class="relative w-full">
        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed bottom-5 right-5 z-50 pointer-events-none flex flex-col gap-2 no-print"></div>
        <!-- Main Content -->
        <div class="w-full main-content">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'form'): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg shadow-sm border border-green-200 no-print">
                    <span class="flex items-center gap-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <?php echo e(session('success')); ?>

                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('print_notice')): ?>
                        <p class="mt-1 text-sm font-medium"><?php echo e(session('print_notice')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg shadow-sm border border-red-200 no-print">
                    <span class="flex items-center gap-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <?php echo e(session('error')); ?>

                    </span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden printable-area no-print">
                <!-- Header visible only on screen -->
                <div class="bg-slate-50 border-b border-slate-200 px-6 py-3 flex items-center justify-between no-print">
                    <h2 class="text-[16px] font-black text-slate-900 flex items-center gap-3 uppercase tracking-tight">
                        <span class="p-2.5 bg-indigo-600 text-white rounded-2xl shadow-lg shadow-indigo-100">📤</span>
                        PHIẾU XUẤT KHO MỚI
                    </h2>
                    <div class="flex items-center gap-2">
                        <!-- Thêm mới -->
                        <button type="button" onclick="handleResetForm('add')" class="flex items-center gap-1.5 px-3.5 py-2 bg-sky-500 hover:bg-sky-600 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                            <span>➕</span> Thêm Mới
                        </button>
                        <!-- Sửa phiếu -->
                        <button type="button" onclick="showToast('Bạn đang soạn thảo trực tiếp. Chọn vật tư ở bảng dưới để sửa đổi.', '✏️')" class="flex items-center gap-1.5 px-3.5 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                            <span>✏️</span> Sửa Phiếu
                        </button>
                        <!-- Xóa phiếu -->
                        <button type="button" onclick="handleResetForm('delete')" class="flex items-center gap-1.5 px-3.5 py-2 bg-rose-500 hover:bg-rose-600 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150" title="Xóa sạch dữ liệu đang điền">
                            <span>🗑️</span> Xóa Phiếu
                        </button>
                        <!-- In phiếu -->
                        <button type="button" onclick="handlePrint()" class="flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                            <span>🖨️</span> In Phiếu
                        </button>
                        <!-- Danh sách phiếu -->
                        <button type="button" onclick="handleSwitchTab('list')" class="flex items-center gap-1.5 px-3.5 py-2 bg-slate-600 hover:bg-slate-700 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                            <span>📋</span> Danh Sách Phiếu
                        </button>
                        <!-- Lưu phiếu -->
                        <button type="button" onclick="handleSave()" class="flex items-center gap-1.5 px-5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-[12px] font-extrabold rounded-xl shadow transition duration-150">
                            <span>💾</span> Lưu Phiếu
                        </button>
                        <!-- Thoát -->
                        <a href="<?php echo e(route('warehouse.inventory')); ?>" onclick="return handleExit(event)" class="flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[12px] font-extrabold rounded-xl shadow border border-slate-200 transition duration-150">
                            <span>⬅️</span> Thoát
                        </a>
                    </div>
                </div>

                <!-- Header visible only when printing -->
                <div class="hidden print:flex items-center justify-between px-6 py-3 border-b border-black">
                    <h2 class="text-[16px] font-black text-black uppercase tracking-tight">
                        PHIẾU XUẤT KHO MỚI
                    </h2>
                    <div class="text-right">
                        <p class="text-xs font-bold text-black uppercase">Số phiếu: SO-<?php echo e(date('Ymd')); ?>-XXXX</p>
                        <p class="text-[10px] text-black font-bold">Ngày in: <?php echo e(date('d/m/Y H:i')); ?></p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-2">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Khách hàng / Bộ phận nhận</label>
                            <select wire:model.live="customer_name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-black text-slate-800 uppercase appearance-none">
                                <option value="">-- Chọn khách hàng / bộ phận --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($customer->name); ?>">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customer->type === 'internal'): ?> [NỘI BỘ] 
                                        <?php elseif($customer->type === 'supplier'): ?> [NCC]
                                        <?php elseif($customer->type === 'customer'): ?> [KH]
                                        <?php else: ?> [ĐỐI TÁC]
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> 
                                        <?php echo e($customer->name); ?>

                                    </option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Người liên hệ</label>
                            <input type="text" wire:model="receiver_name" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên người liên hệ...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Người nhận</label>
                            <input type="text" wire:model="receiver_contact" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên người nhận...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Mã tài sản</label>
                            <input type="text" wire:model="asset_code" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-bold text-slate-800 uppercase" placeholder="Nhập mã tài sản...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Loại hình xuất kho</label>
                            <select wire:model="type" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 px-3 text-[12px] font-black text-slate-800 appearance-none">
                                <option value="repair">🛠️ XUẤT CHO TỔ ĐỘI SỬA CHỮA</option>
                                <option value="delivery">🚚 XUẤT GIAO KHÁCH HÀNG</option>
                                <option value="disposal">🗑️ XUẤT HỦY</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Lý do xuất kho</label>
                            <div class="relative">
                                <select wire:model.live="note" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2 pl-3 pr-8 text-[12px] font-black text-slate-800 appearance-none">
                                    <option value="">-- Chọn lý do --</option>
                                    <option value="BẢO DƯỠNG ĐỊNH KỲ">BẢO DƯỠNG ĐỊNH KỲ</option>
                                    <option value="SỬA CHỮA">SỬA CHỮA</option>
                                    <option value="CÔNG CỤ SỬA CHỮA">CÔNG CỤ SỬA CHỮA</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'repair'): ?>
                    <div class="grid grid-cols-1 md:grid-cols-7 gap-3 mb-4 p-4 bg-slate-50 rounded-2xl border border-slate-200 shadow-sm no-print">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Số Phiếu ĐNSC/BD</label>
                            <input type="text" wire:model="document_number" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Số phiếu...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Dự án (D.A)</label>
                            <input type="text" wire:model="project_name" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Mã dự án...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">BP sử dụng</label>
                            <div class="relative">
                                <select wire:model="department" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 pl-3 pr-8 text-[12px] font-bold text-slate-800 appearance-none">
                                    <option value="">-- Chọn BP --</option>
                                    <option value="BCH VINALPHA">BCH VINALPHA</option>
                                    <option value="TỔ ĐỘI KTSC VINALPHA">TỔ ĐỘI KTSC VINALPHA</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Biển số</label>
                            <input type="text" wire:model="license_plate" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800 uppercase" placeholder="Biển kiểm soát...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Số KM</label>
                            <input type="text" wire:model="km_number" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Đồng hồ KM...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Số giờ HĐ</label>
                            <input type="text" wire:model="operating_hours" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Số giờ hoạt động...">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên thiết bị</label>
                            <input type="text" wire:model="device_name" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-1.5 px-3 text-[12px] font-bold text-slate-800" placeholder="Tên thiết bị bảo dưỡng...">
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'production'): ?>
                    <!-- Production BOM Selection Area -->
                    <div class="mb-4 p-4 bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-100 rounded-2xl shadow-inner no-print">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="block text-sm font-black text-indigo-900 uppercase tracking-tight">Thành phẩm cần sản xuất</label>
                                <div class="relative">
                                    <select wire:model.live="production_product_id" class="w-full rounded-xl border-indigo-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition-all py-3 pl-4 pr-10 appearance-none bg-white font-bold text-slate-800">
                                        <option value="">-- Chọn thành phẩm từ định mức (BOM) --</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $productionProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option value="<?php echo e($prod->id); ?>"><?php echo e($prod->code); ?> - <?php echo e($prod->name); ?></option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-indigo-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                </div>
                                <p class="text-[10px] text-indigo-400 font-bold px-1 italic">Hệ thống sẽ tự động điền danh sách nguyên vật liệu theo định mức đã cài đặt</p>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-black text-indigo-900 uppercase tracking-tight">Số lượng sản xuất</label>
                                <div class="flex items-center gap-3 bg-white p-1 rounded-xl border border-indigo-200 shadow-sm focus-within:ring-4 focus-within:ring-indigo-100 focus-within:border-indigo-500 transition-all">
                                    <input type="number" wire:model.live="production_quantity" step="0.01" min="0.01" class="flex-1 rounded-lg border-none focus:ring-0 shadow-none font-black text-slate-800 text-lg py-1.5" placeholder="0.00">
                                    <span class="px-4 py-2 bg-indigo-50 text-indigo-700 font-black rounded-lg text-xs uppercase border border-indigo-100">Cơ số</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="overflow-hidden border border-slate-200 rounded-2xl shadow-inner bg-slate-50/30 mb-3">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-emerald-600">
                                    <th class="px-2 py-3 text-center text-[10px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-10 no-print">IN</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 min-w-[350px]">TÊN VẬT TƯ / MÃ VẬT TƯ</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">Đề nghị</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">Thực xuất</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">Thu hồi</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-20">Tồn kho</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">Hãng SX</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-14">ĐVT</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-44">Code / Hạn dùng</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Vị trí</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Ghi chú</th>
                                    <th class="px-2 py-3 border-b border-slate-700 w-10 no-print"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 no-print">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'item-'.e($index).''; ?>wire:key="item-<?php echo e($index); ?>" class="hover:bg-slate-50/50 transition duration-150 <?php echo e(!$item['is_printed'] ? 'no-print' : ''); ?>">
                                    <td class="px-3 py-1.5 text-center no-print">
                                        <input type="checkbox" wire:model.live="items.<?php echo e($index); ?>.is_printed" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" wire:model.live.debounce.250ms="items.<?php echo e($index); ?>.product_search" list="product_list_<?php echo e($index); ?>" 
                                               class="w-full rounded-lg border-slate-300 text-xs font-bold focus:ring-indigo-500 focus:border-indigo-500 transition placeholder:font-normal uppercase <?php echo e($type === 'production' ? 'bg-slate-100 cursor-not-allowed' : ''); ?>"
                                               placeholder="Mã hoặc tên vật tư..." <?php echo e($type === 'production' ? 'readonly' : ''); ?>>
                                        <datalist id="product_list_<?php echo e($index); ?>">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($product->code); ?> - <?php echo e($product->name); ?>"></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </datalist>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["items.{$index}.product_id"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-[10px] mt-1 no-print"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.<?php echo e($index); ?>.requested_quantity" 
                                               class="w-full text-center text-xs font-bold rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                               placeholder="1">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.<?php echo e($index); ?>.quantity" <?php echo e($type === 'production' ? 'readonly' : ''); ?>

                                               class="w-full text-center text-xs font-black rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0 <?php echo e($type === 'production' ? 'bg-slate-100 cursor-not-allowed' : ''); ?>"
                                               placeholder="0">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["items.{$index}.quantity"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-red-500 text-[10px] mt-1 no-print"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.<?php echo e($index); ?>.recovered_quantity" 
                                               class="w-full text-center text-xs font-bold rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                               placeholder="0">
                                    </td>
                                    <td class="px-2 py-1.5 text-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($items[$index]['available_qty'])): ?>
                                            <div class="text-xs no-print whitespace-nowrap">
                                                <span class="font-black text-slate-800"><?php echo e(number_format(floatval($items[$index]['available_qty']), 0)); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(floatval($items[$index]['available_qty']) >= floatval($items[$index]['quantity'] ?? 0)): ?>
                                                    <span class="text-green-600 font-bold block text-[9px] mt-0.5">🟢 Đủ</span>
                                                <?php else: ?>
                                                    <span class="text-red-500 font-bold block text-[9px] mt-0.5">🔴 Thiếu</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="px-2 py-4 text-center">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase"><?php echo e($items[$index]['brand'] ?? '-'); ?></span>
                                    </td>
                                    <td class="px-1 py-1.5 text-center">
                                        <span class="inline-block px-1.5 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-600 border border-slate-200 min-w-[30px]">
                                            <?php echo e($items[$index]['unit'] ?: '-'); ?>

                                        </span>
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" wire:model.live="items.<?php echo e($index); ?>.batch_number" 
                                               class="w-full rounded-lg text-[10px] border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition mb-1" placeholder="Số lô...">
                                        <input type="date" wire:model="items.<?php echo e($index); ?>.expiry_date" 
                                               class="w-full rounded-lg border-slate-300 text-[9px] focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <input type="text" wire:model="items.<?php echo e($index); ?>.warehouse_location"
                                               class="w-full text-[10px] rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition print:border-none print:p-0" placeholder="Vị trí...">
                                    </td>
                                    <td class="px-2 py-1.5 w-24">
                                        <input type="text" wire:model="items.<?php echo e($index); ?>.item_note"
                                               class="w-24 text-[10px] rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Ghi chú...">
                                    </td>
                                    <td class="px-2 py-1.5 text-center no-print">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($items) > 1 || $type === 'manual'): ?>
                                            <button wire:click="removeItem(<?php echo e($index); ?>)" class="text-slate-400 hover:text-red-500 transition p-1 rounded-full hover:bg-red-50" title="Xóa dòng">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between mb-8 no-print">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canAddItem() && $type !== 'production'): ?>
                            <button wire:click="addItem" class="text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg font-semibold text-sm flex items-center gap-2 transition border border-indigo-200 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Thêm dòng vật tư
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="mt-8 mb-4 p-5 bg-slate-50 rounded-2xl border border-slate-200 shadow-sm no-print">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên THỦ KHO</label>
                                <input type="text" wire:model.live="warehouse_keeper" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên thủ kho...">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên Tổ trưởng/ trưởng ca QLTB / vận hành</label>
                                <input type="text" wire:model.live="supervisor_qltb" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên tổ trưởng QLTB...">
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Tên Tổ trưởng / trưởng ca</label>
                                <input type="text" wire:model.live="supervisor_ca" class="w-full rounded-xl border-slate-200 bg-white focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 shadow-sm transition py-2 px-3 text-[12px] font-bold text-slate-800" placeholder="Họ tên tổ trưởng / trưởng ca...">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end items-center gap-4 no-print mt-2">
                        <a href="<?php echo e(route('warehouse.inventory')); ?>" class="px-6 py-2 border border-slate-300 rounded-xl text-slate-600 text-sm font-semibold hover:bg-slate-50 transition duration-150">
                            Hủy bỏ
                        </a>
                        <button wire:click="save" class="bg-indigo-600 text-white px-8 py-2 rounded-xl text-sm font-black hover:bg-indigo-700 transition duration-150 shadow-md flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Xác nhận xuất kho
                        </button>
                    </div>

                </div>
            </div>

            <!-- PHẦN IN PDF BỊ ẨN KHI XEM THƯỜNG (KIỂU V-ALPHA ĐỀ NGHỊ CẤP VẬT TƯ SỬA CHỮA) -->
            <div class="hidden print-only print-container inset-0 bg-white w-full text-black" style="font-family: 'Times New Roman', serif; padding: 5mm; line-height: 1.3;">
                <!-- Logo & Title Section -->
                <div class="grid grid-cols-12 items-center mb-3">
                    <!-- Logo & Company Name (Inline side-by-side) -->
                    <div class="col-span-5 flex items-center gap-3">
                        <!-- Image Logo -->
                        <div class="flex flex-col items-center min-w-[65px]">
                            <img src="data:image/png;base64,<?php echo e(base64_encode(file_get_contents(public_path('images/v-alpha-logo.png')))); ?>" alt="V-ALPHA Logo" class="w-12 h-auto object-contain">
                        </div>
                        <!-- Company Name & Department -->
                        <div class="text-left font-bold text-slate-800 leading-tight">
                            <p class="text-[9.5px] uppercase tracking-wide">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ THI CÔNG HẠ TẦNG V- ALPHA</p>
                            <p class="text-[8px] uppercase tracking-tight text-slate-600 mt-1">PHÒNG KỸ THUẬT SỬA CHỮA</p>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="col-span-5 text-center">
                        <h1 class="text-sm font-bold uppercase tracking-tight leading-normal" style="font-size: 13px;">
                            ĐỀ NGHỊ CẤP VẬT TƯ SỬA CHỮA & BẢO DƯỠNG<br>
                            KIÊM PHIẾU XUẤT KHO
                        </h1>
                        <p class="text-[10px] mt-1 italic">
                            Ngày <?php echo e(date('d')); ?> tháng <?php echo e(date('m')); ?> năm <?php echo e(date('Y')); ?>

                        </p>
                        <p class="text-[10px] font-bold mt-1">
                            Số Phiếu ĐNSC/BD: <span class="font-bold underline"><?php echo e($document_number ?: '..................................................'); ?></span>
                        </p>
                    </div>

                    <!-- Form Code & Project -->
                    <div class="col-span-2 text-right text-[10px] self-start space-y-1">
                        <p class="font-bold text-[9px] text-slate-500 tracking-wider">BM01-ĐNCVT</p>
                        <p class="font-bold mt-2">D.A: <span class="font-black underline"><?php echo e($project_name ?: '_'); ?></span></p>
                    </div>
                </div>

                <!-- Metadata Details Table Grid (Matching Row 1 & Row 2 with solid black borders) -->
                <table class="w-full border-collapse border border-black text-[11px] mb-3 font-bold text-black" style="line-height: 1.4;">
                    <tbody>
                        <tr>
                            <!-- Họ và tên người nhận hàng -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" colspan="2" style="width: 35%;">
                                Họ và tên người nhận hàng: <span class="font-normal"><?php echo e($receiver_contact ?: '........................................'); ?></span>
                            </td>
                            <!-- BP sử dụng -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 25%;">
                                BP sử dụng : <span class="font-normal"><?php echo e($department ?: '................................'); ?></span>
                            </td>
                            <!-- Mã tài sản -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 20%;">
                                Mã tài sản: <span class="font-normal font-mono uppercase"><?php echo e($asset_code ?: '................................'); ?></span>
                            </td>
                            <!-- Biển số -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 20%;">
                                Biển số : <span class="font-normal uppercase"><?php echo e($license_plate ?: '................................'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <!-- Số KM -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 17.5%;">
                                Số KM : <span class="font-normal"><?php echo e($km_number ?: '................................'); ?></span>
                            </td>
                            <!-- Số giờ HĐ -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 17.5%;">
                                Số giờ HĐ : <span class="font-normal"><?php echo e($operating_hours ?: '................................'); ?></span>
                            </td>
                            <!-- Tên thiết bị -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 25%;">
                                Tên thiết bị: <span class="font-normal"><?php echo e($device_name ?: '................................'); ?></span>
                            </td>
                            <!-- Lý do xuất kho (spans columns 3 & 4) -->
                            <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" colspan="2" style="width: 40%;">
                                Lý do xuất kho: <span class="font-normal"><?php echo e($note ?: '................................'); ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Items Table -->
                <table class="print-table w-full border-collapse border-2 border-black text-[11px] mb-3">
                    <thead>
                        <tr class="bg-gray-100 text-center font-bold">
                            <th class="border border-black px-1 py-1 w-8">STT</th>
                            <th class="border border-black px-2 py-1 text-left">TÊN VẬT TƯ SỬA CHỮA</th>
                            <th class="border border-black px-2 py-1 w-24">MÃ VẬT TƯ</th>
                            <th class="border border-black px-1 py-1 w-12">ĐVT</th>
                            <th class="border border-black px-1 py-1 w-16">ĐỀ NGHỊ</th>
                            <th class="border border-black px-1 py-1 w-16">THỰC XUẤT</th>
                            <th class="border border-black px-1 py-1 w-16">THU HỒI</th>
                            <th class="border border-black px-2 py-1 w-32">GHI CHÚ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $validCount = 0; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['product_id'] && $item['is_printed']): ?>
                                <?php $validCount++; ?>
                                <tr>
                                    <td class="border border-black px-1 py-1.5 text-center"><?php echo e($validCount); ?></td>
                                    <td class="border border-black px-2 py-1.5 font-bold uppercase">
                                        <?php echo e(str_contains($item['product_search'], ' - ') ? explode(' - ', $item['product_search'], 2)[1] : $item['product_search']); ?>

                                    </td>
                                    <td class="border border-black px-2 py-1.5 text-center font-mono uppercase">
                                        <?php echo e(str_contains($item['product_search'], ' - ') ? explode(' - ', $item['product_search'], 2)[0] : ''); ?>

                                    </td>
                                    <td class="border border-black px-1 py-1.5 text-center"><?php echo e($item['unit'] ?: '-'); ?></td>
                                    <td class="border border-black px-1 py-1.5 text-center font-bold"><?php echo e((float)($item['requested_quantity'] ?? $item['quantity'])); ?></td>
                                    <td class="border border-black px-1 py-1.5 text-center font-bold"><?php echo e((float)$item['quantity']); ?></td>
                                    <td class="border border-black px-1 py-1.5 text-center"><?php echo e((float)($item['recovered_quantity'] ?? 0)); ?></td>
                                    <td class="border border-black px-2 py-1.5"><?php echo e($item['item_note'] ?: ''); ?></td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php 
                            $minRows = max(8, ceil($validCount / 8) * 8); 
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = $validCount; $i < $minRows; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="border border-black px-1 py-2 text-center font-bold"><?php echo e($i + 1); ?></td>
                                <td class="border border-black px-2 py-2"></td>
                                <td class="border border-black px-2 py-2"></td>
                                <td class="border border-black px-1 py-2"></td>
                                <td class="border border-black px-1 py-2"></td>
                                <td class="border border-black px-1 py-2"></td>
                                <td class="border border-black px-1 py-2"></td>
                                <td class="border border-black px-2 py-2"></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>

                <!-- Footer Signatures Section (5 Signatures from left to right) -->
                <div class="signatures-section grid grid-cols-5 gap-2 text-center text-[10px] mt-6 font-bold leading-normal">
                    <div>
                        <p>Nv vận hành</p>
                        <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                        <div style="height: 50px;"></div>
                        <p class="font-normal text-slate-300">........................</p>
                    </div>
                    <div>
                        <p>Tổ trưởng/ trưởng ca QLTB / vận hành</p>
                        <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                        <div style="height: 50px;"></div>
                        <p class="font-bold text-slate-800 text-[11px] mt-1"><?php echo e($supervisor_qltb ?: '........................'); ?></p>
                    </div>
                    <div>
                        <p>Thủ kho</p>
                        <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                        <div style="height: 50px;"></div>
                        <p class="font-bold text-slate-800 text-[11px] mt-1"><?php echo e($warehouse_keeper ?: '........................'); ?></p>
                    </div>
                    <div>
                        <p>Nv sửa chữa</p>
                        <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                        <div style="height: 50px;"></div>
                        <p class="font-normal text-slate-300">........................</p>
                    </div>
                    <div>
                        <p>Tổ trưởng / trưởng ca</p>
                        <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                        <div style="height: 50px;"></div>
                        <p class="font-bold text-slate-800 text-[11px] mt-1"><?php echo e($supervisor_ca ?: '........................'); ?></p>
                    </div>
                </div>
            </div>
            <?php elseif($activeTab === 'list'): ?>
                <!-- Stock Out List Section -->
                <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden min-h-[600px] main-content">
                    <!-- Print Title (Only visible when printing) -->
                    <div class="hidden print:block text-center mb-8">
                        <h1 class="text-2xl font-black uppercase underline decoration-double">DANH SÁCH PHIẾU XUẤT KHO</h1>
                        <p class="text-[13px] font-bold mt-1">TỪ NGÀY: <?php echo e(\Carbon\Carbon::parse($listDateFrom)->format('d/m/Y')); ?> - ĐẾN NGÀY: <?php echo e(\Carbon\Carbon::parse($listDateTo)->format('d/m/Y')); ?></p>
                    </div>

                    <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 no-print">
                        <h2 class="text-[15px] font-black text-slate-900 flex items-center gap-2 uppercase tracking-tight">
                            <span class="p-2 bg-indigo-600 text-white rounded-xl shadow-lg">📋</span>
                            LỊCH SỬ PHIẾU XUẤT KHO
                        </h2>
                        
                        <div class="flex flex-wrap items-center gap-3 no-print">
                            <!-- Date Range -->
                            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-inner focus-within:ring-4 focus-within:ring-indigo-100 transition-all">
                                <div class="flex items-center gap-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Từ ngày</label>
                                    <input type="date" wire:model.live="listDateFrom" class="text-[12px] border-none focus:ring-0 p-0 font-black text-slate-700 bg-transparent">
                                </div>
                                <div class="w-px h-4 bg-slate-200 mx-2"></div>
                                <div class="flex items-center gap-2">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Đến ngày</label>
                                    <input type="date" wire:model.live="listDateTo" class="text-[12px] border-none focus:ring-0 p-0 font-black text-slate-700 bg-transparent">
                                </div>
                            </div>

                            <!-- Search -->
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="listSearch" placeholder="TÌM MÃ, KHÁCH HÀNG..." class="pl-11 pr-4 py-2.5 w-64 text-[12px] font-black rounded-2xl border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all bg-white placeholder:text-slate-300">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 ml-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
                                    <div class="flex items-center gap-2 pr-3 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4 duration-300">
                                        <span class="text-[11px] font-black text-indigo-700 bg-indigo-50 px-2.5 py-1.5 rounded-lg border border-indigo-100">CHỌN: <?php echo e(count($selectedIds)); ?></span>
                                        <button wire:click="deleteSelected" wire:confirm="Xác nhận xóa <?php echo e(count($selectedIds)); ?> phiếu xuất?" class="flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-rose-500 to-rose-600 text-white rounded-xl text-[12px] font-black transition-all hover:scale-105 shadow-md">
                                            <span>🗑️</span> XÓA
                                        </button>
                                        <button wire:click="printSelected" class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-indigo-600 text-indigo-700 hover:bg-indigo-50 rounded-xl text-[12px] font-black transition-all shadow-sm">
                                            <span>🖨️</span> IN GHÉP
                                        </button>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button wire:click="exportExcel" class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 rounded-xl text-[12px] font-black transition-all shadow-lg shadow-emerald-100">
                                    <span class="text-sm">📊</span> EXCEL
                                </button>
                                <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-800 text-white hover:bg-black rounded-xl text-[12px] font-black transition-all shadow-lg">
                                    <span class="text-sm">📄</span> IN PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-[11px] font-black text-white uppercase tracking-widest bg-slate-800 border-b border-slate-700">
                                <?php
                                    $idsOnPage = $stockOuts->pluck('id')->toArray();
                                ?>
                                <tr>
                                    <th class="px-6 py-4 w-10 no-print text-center">
                                        <input type="checkbox" wire:click="toggleSelectAll([<?php echo e(implode(',', $idsOnPage)); ?>])" <?php echo e(count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : ''); ?> class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500">
                                    </th>
                                    <th class="px-2 py-4">MÃ PHIẾU</th>
                                    <th class="px-6 py-4">NGÀY TẠO</th>
                                    <th class="px-6 py-4">KHÁCH HÀNG / BỘ PHẬN</th>
                                    <th class="px-6 py-4">NGƯỜI LIÊN HỆ / MÃ TS</th>
                                    <th class="px-6 py-4">LOẠI XUẤT</th>
                                    <th class="px-6 py-4 text-right">TỔNG TIỀN</th>
                                    <th class="px-6 py-4">GHI CHÚ</th>
                                    <th class="px-6 py-4 text-center no-print">THAO TÁC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stockOuts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $so): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr class="hover:bg-indigo-50/30 transition-all group <?php echo e(in_array($so->id, $selectedIds) ? 'bg-indigo-50' : ''); ?>">
                                        <td class="px-6 py-4 no-print text-center">
                                            <input type="checkbox" wire:model.live="selectedIds" value="<?php echo e($so->id); ?>" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                        <td class="px-2 py-4 font-black text-indigo-700 tracking-tight"><?php echo e($so->code); ?></td>
                                        <td class="px-6 py-4 text-slate-500 text-[12px] font-bold"><?php echo e($so->created_at->format('d/m/Y H:i')); ?></td>
                                        <td class="px-6 py-4 font-black text-slate-800 text-[13px] uppercase tracking-tighter"><?php echo e($so->customer_name ?: '-'); ?></td>
                                        <td class="px-6 py-4">
                                            <div class="text-[12px] font-bold text-slate-700 uppercase"><?php echo e($so->receiver_name ?: '-'); ?></div>
                                            <div class="text-[10px] font-black text-indigo-600"><?php echo e($so->asset_code); ?></div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($so->type):
                                                case ('repair'): ?> <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg text-[10px] font-black uppercase border border-blue-100">🛠️ SỬA CHỮA</span> <?php break; ?>
                                                <?php case ('delivery'): ?> <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-black uppercase border border-emerald-100">🚚 GIAO HÀNG</span> <?php break; ?>
                                                <?php case ('disposal'): ?> <span class="px-2.5 py-1 bg-red-50 text-red-700 rounded-lg text-[10px] font-black uppercase border border-red-100">🗑️ HỦY</span> <?php break; ?>
                                                <?php default: ?> <span class="px-2.5 py-1 bg-slate-50 text-slate-600 rounded-lg text-[10px] font-black uppercase border border-slate-100">KHÁC</span>
                                            <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right font-black text-slate-900 text-[14px]">
                                            <?php echo e(number_format($so->items->sum('total_amount'))); ?> đ
                                        </td>
                                        <td class="px-6 py-4 text-slate-400 text-[11px] font-bold italic truncate max-w-[150px]" title="<?php echo e($so->note); ?>"><?php echo e($so->note ?: '-'); ?></td>
                                        <td class="px-6 py-4 text-center no-print">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="printSingle(<?php echo e($so->id); ?>)" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="In phiếu này">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                </button>
                                                <button wire:confirm="Xác nhận xóa phiếu xuất <?php echo e($so->code); ?>? Tồn kho sẽ được hoàn trả tự động." wire:click="delete(<?php echo e($so->id); ?>)" class="p-2 text-rose-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Xóa phiếu">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center gap-2">
                                                <span class="text-4xl text-slate-200">🔍</span>
                                                <p class="text-sm font-bold">Không tìm thấy phiếu xuất nào trong khoảng thời gian này</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-50 no-print">
                        <?php echo e($stockOuts->links()); ?>

                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- PHẦN IN CHI TIẾT HÀNG LOẠT (Nhanh/Ghép) - THEO MẪU ĐỀ NGHỊ BẢO DƯỠNG V-ALPHA -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($printItems) > 0): ?>
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $printItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="print-page p-8 bg-white" style="font-family: 'Times New Roman', serif; min-height: 200mm; page-break-after: always; line-height: 1.3;">
            <!-- Logo & Title Section -->
            <div class="grid grid-cols-12 items-center mb-3">
                <!-- Logo & Company Name (Inline side-by-side) -->
                <div class="col-span-5 flex items-center gap-3">
                    <!-- Image Logo -->
                    <div class="flex flex-col items-center min-w-[65px]">
                        <img src="data:image/png;base64,<?php echo e(base64_encode(file_get_contents(public_path('images/v-alpha-logo.png')))); ?>" alt="V-ALPHA Logo" class="w-12 h-auto object-contain">
                    </div>
                    <!-- Company Name & Department -->
                    <div class="text-left font-bold text-slate-800 leading-tight">
                        <p class="text-[9.5px] uppercase tracking-wide">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ THI CÔNG HẠ TẦNG V- ALPHA</p>
                        <p class="text-[8px] uppercase tracking-tight text-slate-600 mt-1">PHÒNG KỸ THUẬT SỬA CHỮA</p>
                    </div>
                </div>

                <!-- Title -->
                <div class="col-span-5 text-center">
                    <h1 class="text-sm font-bold uppercase tracking-tight leading-normal" style="font-size: 13px;">
                        ĐỀ NGHỊ CẤP VẬT TƯ SỬA CHỮA & BẢO DƯỠNG<br>
                        KIÊM PHIẾU XUẤT KHO
                    </h1>
                    <p class="text-[10px] mt-1 italic">
                        Ngày <?php echo e($pItem->created_at ? $pItem->created_at->format('d') : '.....'); ?> 
                        tháng <?php echo e($pItem->created_at ? $pItem->created_at->format('m') : '.....'); ?> 
                        năm <?php echo e($pItem->created_at ? $pItem->created_at->format('Y') : '2026'); ?>

                    </p>
                    <p class="text-[10px] font-bold mt-1">
                        Số Phiếu ĐNSC/BD: <span class="font-bold underline"><?php echo e($pItem->document_number ?: '..................................................'); ?></span>
                    </p>
                </div>

                <!-- Form Code & Project -->
                <div class="col-span-2 text-right text-[10px] self-start space-y-1">
                    <p class="font-bold text-[9px] text-slate-500 tracking-wider">BM01-ĐNCVT</p>
                    <p class="font-bold mt-2">D.A: <span class="font-black underline"><?php echo e($pItem->project_name ?: '_'); ?></span></p>
                </div>
            </div>

            <!-- Metadata Details Table Grid (Matching Row 1 & Row 2 with solid black borders) -->
            <table class="w-full border-collapse border border-black text-[11px] mb-3 font-bold text-black" style="line-height: 1.4;">
                <tbody>
                    <tr>
                        <!-- Họ và tên người nhận hàng -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" colspan="2" style="width: 35%;">
                            Họ và tên người nhận hàng: <span class="font-normal"><?php echo e($pItem->receiver_contact ?: '........................................'); ?></span>
                        </td>
                        <!-- BP sử dụng -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 25%;">
                            BP sử dụng : <span class="font-normal"><?php echo e($pItem->department ?: '................................'); ?></span>
                        </td>
                        <!-- Mã tài sản -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 20%;">
                            Mã tài sản: <span class="font-normal font-mono uppercase"><?php echo e($pItem->asset_code ?: '................................'); ?></span>
                        </td>
                        <!-- Biển số -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 20%;">
                            Biển số : <span class="font-normal uppercase"><?php echo e($pItem->license_plate ?: '................................'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <!-- Số KM -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 17.5%;">
                            Số KM : <span class="font-normal"><?php echo e($pItem->km_number ?: '................................'); ?></span>
                        </td>
                        <!-- Số giờ HĐ -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 17.5%;">
                            Số giờ HĐ : <span class="font-normal"><?php echo e($pItem->operating_hours ?: '................................'); ?></span>
                        </td>
                        <!-- Tên thiết bị -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" style="width: 25%;">
                            Tên thiết bị: <span class="font-normal"><?php echo e($pItem->device_name ?: '................................'); ?></span>
                        </td>
                        <!-- Lý do xuất kho (spans columns 3 & 4) -->
                        <td class="border border-black px-2 py-1.5 align-middle whitespace-nowrap" colspan="2" style="width: 40%;">
                            Lý do xuất kho: <span class="font-normal"><?php echo e($pItem->note ?: '................................'); ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Items Table -->
            <table class="print-table w-full border-collapse border-2 border-black text-[11px] mb-3">
                <thead>
                    <tr class="bg-gray-100 text-center font-bold">
                        <th class="border border-black px-1 py-1 w-8">STT</th>
                        <th class="border border-black px-2 py-1 text-left">TÊN VẬT TƯ SỬA CHỮA</th>
                        <th class="border border-black px-2 py-1 w-24">MÃ VẬT TƯ</th>
                        <th class="border border-black px-1 py-1 w-12">ĐVT</th>
                        <th class="border border-black px-1 py-1 w-16">ĐỀ NGHỊ</th>
                        <th class="border border-black px-1 py-1 w-16">THỰC XUẤT</th>
                        <th class="border border-black px-1 py-1 w-16">THU HỒI</th>
                        <th class="border border-black px-2 py-1 w-32">GHI CHÚ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pItem->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $ii): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="border border-black px-1 py-1.5 text-center"><?php echo e($idx + 1); ?></td>
                        <td class="border border-black px-2 py-1.5 font-bold uppercase">
                            <?php echo e($ii->product->name); ?>

                        </td>
                        <td class="border border-black px-2 py-1.5 text-center font-mono uppercase">
                            <?php echo e($ii->product->code); ?>

                        </td>
                        <td class="border border-black px-1 py-1.5 text-center"><?php echo e($ii->product->unit ?: '-'); ?></td>
                        <td class="border border-black px-1 py-1.5 text-center font-bold"><?php echo e((float)($ii->requested_quantity ?: $ii->quantity)); ?></td>
                        <td class="border border-black px-1 py-1.5 text-center font-bold"><?php echo e((float)$ii->quantity); ?></td>
                        <td class="border border-black px-1 py-1.5 text-center"><?php echo e((float)($ii->recovered_quantity ?: 0)); ?></td>
                        <td class="border border-black px-2 py-1.5"><?php echo e($ii->item_note ?: ''); ?></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php
                        $validCount = count($pItem->items);
                        $minRows = max(8, ceil($validCount / 8) * 8);
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($i = $validCount; $i < $minRows; $i++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="border border-black px-1 py-2 text-center font-bold"><?php echo e($i + 1); ?></td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-1 py-2"></td>
                        <td class="border border-black px-1 py-2"></td>
                        <td class="border border-black px-1 py-2"></td>
                        <td class="border border-black px-1 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </tbody>
            </table>

            <!-- Footer Signatures Section (5 Signatures) -->
            <div class="signatures-section grid grid-cols-5 gap-2 text-center text-[10px] mt-6 font-bold leading-normal">
                <div>
                    <p>Nv vận hành</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-normal text-slate-300">........................</p>
                </div>
                <div>
                    <p>Tổ trưởng/ trưởng ca QLTB / vận hành</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-bold text-slate-800 text-[11px] mt-1"><?php echo e($pItem->supervisor_qltb ?: '........................'); ?></p>
                </div>
                <div>
                    <p>Thủ kho</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-bold text-slate-800 text-[11px] mt-1"><?php echo e($pItem->warehouse_keeper ?: '........................'); ?></p>
                </div>
                <div>
                    <p>Nv sửa chữa</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-normal text-slate-300">........................</p>
                </div>
                <div>
                    <p>Tổ trưởng / trưởng ca</p>
                    <p class="text-[8px] italic font-normal">(Ký, ghi rõ họ và tên)</p>
                    <div style="height: 50px;"></div>
                    <p class="font-bold text-slate-800 text-[11px] mt-1"><?php echo e($pItem->supervisor_ca ?: '........................'); ?></p>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
        $__scriptKey = '1711333737-0';
        ob_start();
    ?>
    <script>
        let isDirty = false;

        // Reset dirty flag when form is loaded or saved
        window.addEventListener('livewire:initialized', () => {
            // Listen for form inputs
            document.addEventListener('input', (e) => {
                isDirty = true;
            });
            
            document.addEventListener('change', (e) => {
                isDirty = true;
            });
        });

        // 3s Toast Notification
        window.showToast = function(message, icon = '✅', duration = 3000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = "custom-toast flex items-center gap-3 text-white px-5 py-3.5 rounded-2xl shadow-2xl pointer-events-auto transform transition-all duration-300 text-xs font-black uppercase tracking-wider";
            toast.innerHTML = `<span class="text-base">${icon}</span> <span>${message}</span>`;
            
            container.appendChild(toast);

            // Auto dismiss
            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, duration);
        };

        // Listeners for Livewire events
        $wire.on('stock-out-saved', () => {
            isDirty = false;
            showToast("Lưu phiếu xuất kho thành công!", "✅", 3000);
        });

        $wire.on('stock-out-deleted', (event) => {
            const count = event.count || 1;
            showToast(`Đã xóa thành công ${count} phiếu xuất kho!`, "🗑️", 3000);
        });

        $wire.on('stock-out-printing', () => {
            showToast("Đang tải dữ liệu và chuẩn bị in phiếu...", "🖨️", 3000);
        });

        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 500);
        });

        // Handlers
        window.handleResetForm = function(actionType) {
            if (isDirty) {
                if (!confirm("Dữ liệu phiếu xuất kho hiện tại đang có thay đổi. Bạn có chắc chắn muốn bỏ qua các thay đổi này không?")) {
                    return;
                }
            }
            isDirty = false;
            $wire.resetForm();
            if (actionType === 'add') {
                showToast("Đã khởi tạo phiếu xuất kho mới thành công!", "➕", 3000);
            } else {
                showToast("Đã xóa sạch dữ liệu trên phiếu!", "🗑️", 3000);
            }
        };

        window.handlePrint = function() {
            showToast("Đang chuẩn bị bản in phiếu xuất kho...", "🖨️", 3000);
            setTimeout(() => {
                window.print();
            }, 300);
        };

        window.handleSwitchTab = function(tab) {
            if (isDirty) {
                if (!confirm("Dữ liệu phiếu xuất kho đang có thay đổi chưa lưu. Bạn có chắc chắn muốn chuyển sang danh sách phiếu không?")) {
                    return;
                }
            }
            isDirty = false;
            $wire.switchTab(tab);
        };

        window.handleSave = function() {
            showToast("Đang tiến hành lưu phiếu xuất kho...", "💾", 3000);
            $wire.save();
        };

        window.handleExit = function(e) {
            if (isDirty) {
                if (!confirm("Dữ liệu phiếu xuất kho đang có thay đổi chưa lưu. Bạn có chắc chắn muốn thoát không?")) {
                    e.preventDefault();
                    return false;
                }
            }
            return true;
        };
    </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/stock-out-form.blade.php ENDPATH**/ ?>