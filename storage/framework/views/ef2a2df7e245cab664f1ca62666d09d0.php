<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title ?? 'Kho'); ?> - ERP Warehouse</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-sky-100 text-sky-950 border-b border-sky-200 shadow-md sticky top-0 z-50 no-print">
        <div class="w-full px-8 py-3 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="<?php echo e(route('warehouse.inventory')); ?>" class="flex items-center gap-2 text-xl font-extrabold tracking-tight text-sky-900 hover:text-sky-950 transition-all">
                    <span class="bg-sky-600 text-white p-1.5 rounded-lg shadow-sm">📦</span>
                    <span>ERP KHO</span>
                </a>

                <div class="hidden md:flex items-center gap-1">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('current_house') != 5): ?>
                    <!-- Module 1: NCC/KH -->
                    <a href="<?php echo e(route('warehouse.contacts')); ?>" class="px-3 py-2 rounded-md text-sm font-bold transition duration-150 hover:bg-sky-200 hover:text-sky-950 <?php echo e(request()->routeIs('warehouse.contacts') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900'); ?>">
                        1. NCC/KH
                    </a>

                    <!-- Module 2: KHO -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 <?php echo e(request()->routeIs('warehouse.stock-*') || request()->routeIs('warehouse.inventory') || request()->routeIs('warehouse.product-*') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900'); ?>">
                            2. KHO
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left z-[100]">
                            <a href="<?php echo e(route('warehouse.product-catalog', ['activeTab' => 'materials'])); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 font-bold border-b border-slate-100">DANH MỤC VẬT TƯ</a>
                            <a href="<?php echo e(route('warehouse.product-catalog', ['activeTab' => 'equipments'])); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 font-bold border-b border-slate-100">DANH MỤC THIẾT BỊ</a>
                            <a href="<?php echo e(route('warehouse.stock-in')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Nhập kho</a>
                            <a href="<?php echo e(route('warehouse.stock-out')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Xuất kho</a>
                            <a href="<?php echo e(route('warehouse.inventory')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Tồn kho</a>
                            <a href="<?php echo e(route('warehouse.stock-transfer.index')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Chuyển kho</a>
                            <a href="<?php echo e(route('warehouse.stock-recovery-report')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Thu hồi phế phẩm</a>
                            <a href="<?php echo e(route('warehouse.stock-count')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Kiểm kê kho</a>
                        </div>
                    </div>


                    <!-- Module 3: THEO DÕI BẢO DƯỠNG -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 <?php echo e(request()->routeIs('warehouse.asset-manager') || request()->routeIs('maintenance.*') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900'); ?>">
                            3. THEO DÕI BẢO DƯỠNG
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-72 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                            <a href="<?php echo e(route('warehouse.asset-manager')); ?>" class="block px-4 py-2 text-sm text-gray-700 font-bold bg-sky-50 hover:bg-sky-100 border-b border-sky-100">TRANG CHỦ TỔNG HỢP (7 IN 1)</a>
                            <a href="<?php echo e(route('warehouse.asset-manager', ['activeTab' => 'odo-manager'])); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Cập nhật giờ ODO hàng ngày</a>
                            <a href="<?php echo e(route('warehouse.asset-manager', ['activeTab' => 'bom-manager'])); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Định mức bảo dưỡng (BOM)</a>
                            <a href="<?php echo e(route('warehouse.asset-manager', ['activeTab' => 'ticket-list'])); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50">Phiếu bảo dưỡng & Lịch</a>
                            <a href="<?php echo e(route('warehouse.asset-manager', ['activeTab' => 'shift-log'])); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50">Giao ca / Nhật ký</a>
                        </div>
                    </div>

                    <!-- Module 4: KẾ HOẠCH & MUA HÀNG -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 <?php echo e(request()->routeIs('warehouse.purchase-*') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900'); ?>">
                            4. KẾ HOẠCH & MUA HÀNG
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-64 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                            <a href="<?php echo e(route('purchase-plan')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Kế hoạch mua hàng</a>
                            <a href="<?php echo e(route('purchase-request')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Đề xuất mua vật tư</a>
                            <a href="<?php echo e(route('purchase-plan.history')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Lịch sử kế hoạch</a>
                        </div>
                    </div>

                    <!-- Module 5: BÁO CÁO -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 <?php echo e(request()->routeIs('warehouse.delivery-note') || request()->routeIs('warehouse.reports.*') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900'); ?>">
                            5. BÁO CÁO
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                            <a href="<?php echo e(route('warehouse.reports.transaction-detail')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 <?php echo e(request()->routeIs('warehouse.reports.transaction-detail') ? 'bg-slate-100 font-bold' : ''); ?>">Báo cáo chi tiết giao dịch</a>
                            <a href="<?php echo e(route('warehouse.reports.daily')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 <?php echo e(request()->routeIs('warehouse.reports.daily') ? 'bg-slate-100 font-bold' : ''); ?>">Báo Cáo Ngày</a>
                            <a href="<?php echo e(route('warehouse.reports.stock')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 <?php echo e(request()->routeIs('warehouse.reports.stock') ? 'bg-slate-100 font-bold' : ''); ?>">Báo Cáo Kho Tổng Hợp</a>
                        </div>
                    </div>
                    
                    <!-- Module 6: CHAT KHO -->
                    <div class="relative group">
                        <a href="<?php echo e(route('warehouse.chat')); ?>" class="px-3 py-2 rounded-md text-sm font-bold transition duration-150 hover:bg-sky-200 hover:text-sky-950 <?php echo e(request()->routeIs('warehouse.chat') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900'); ?>">
                            6. CHAT KHO
                        </a>
                    </div>
                    <?php else: ?>
                    <!-- Module HR -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::check() && Auth::user()->role === 'admin'): ?>
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-bold transition duration-150 group-hover:bg-purple-200 group-hover:text-purple-950 flex items-center gap-1 <?php echo e(request()->routeIs('hr.*') ? 'bg-purple-200 text-purple-950 shadow-inner' : 'text-purple-900'); ?>">
                            ⭐ NGÔI NHÀ HR (TRUNG TÂM ADMIN)
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-64 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                            <a href="<?php echo e(route('hr.dashboard')); ?>" class="block px-4 py-2 text-sm text-purple-700 font-bold hover:bg-purple-50">🎛️ Bảng Điều Khiển</a>
                            <a href="<?php echo e(route('hr.projects')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Quản lý Dự án (Ngôi nhà)</a>
                            <a href="<?php echo e(route('hr.modules')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Cấu hình Module</a>
                            <a href="<?php echo e(route('hr.permissions')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Phân quyền Hệ thống</a>
                            <a href="<?php echo e(route('hr.notifications')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Quản lý Thông báo</a>
                            <a href="<?php echo e(route('hr.global-report')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Báo cáo Tổng hợp</a>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- User Menu -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <div class="relative group">
                        <button class="flex items-center gap-2 px-3 py-2 rounded-md bg-sky-200 hover:bg-sky-300 transition duration-150 text-sm font-bold text-sky-950">
                            <span class="bg-sky-600 px-2 py-0.5 rounded text-xs text-white border border-sky-700"><?php echo e(\App\Models\Project::find(session('current_house', 1))?->name ?? 'Dự án'); ?></span>
                            <span>👤</span>
                            <span><?php echo e(Auth::user()->role === 'admin' ? 'Admin' : 'Nhân viên'); ?> - <?php echo e(Auth::user()->name); ?></span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute right-0 mt-0 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right -translate-y-2 group-hover:translate-y-0 z-50">
                            <div class="px-4 py-3 border-b border-gray-100 text-sm text-gray-600 text-left">
                                <div class="font-semibold text-gray-800"><?php echo e(Auth::user()->name); ?></div>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->role === 'admin' && session('current_house', 1) == 5): ?>
                                <a href="<?php echo e(route('hr.users.index')); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 text-left">👥 Quản lý nhân viên</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <form method="POST" action="<?php echo e(route('logout')); ?>" class="border-t border-gray-100 mt-1 text-left">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-slate-100 font-medium">🚪 Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="px-3 py-2 rounded-md bg-sky-200 hover:bg-sky-300 transition duration-150 text-sm font-bold text-sky-950">
                        Đăng nhập
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="text-xs text-sky-600 font-bold">v1.1</span>
            </div>
        </div>
    </nav>

    <main class="w-full px-2 py-2">
        <h1 class="text-2xl font-black text-slate-900 mb-1 uppercase tracking-tight no-print" style="font-family: 'Times New Roman', Times, serif;">
            <?php echo e(mb_strtoupper($title ?? '')); ?>

        </h1>
        <?php echo e($slot ?? ''); ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                // Không chuyển ô nếu đang ở trong textarea (để xuống dòng) hoặc button (để thực hiện lệnh)
                if (e.target.tagName === 'TEXTAREA' || e.target.tagName === 'BUTTON') return;

                const focusables = Array.from(document.querySelectorAll('input:not([type="hidden"]), select, button:not([disabled])'))
                    .filter(el => {
                        const style = window.getComputedStyle(el);
                        return style.display !== 'none' && style.visibility !== 'hidden' && el.offsetParent !== null;
                    });

                const index = focusables.indexOf(e.target);
                if (index > -1 && index < focusables.length - 1) {
                    e.preventDefault();
                    focusables[index + 1].focus();
                }
            }
        });
    </script>
</body>
</html>
<?php /**PATH D:\Project\resources\views/layouts/app.blade.php ENDPATH**/ ?>