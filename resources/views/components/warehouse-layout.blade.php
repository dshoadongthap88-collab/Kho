<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Kho' }} - ERP Warehouse</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-sky-100 text-sky-950 border-b border-sky-200 shadow-md sticky top-0 z-50 no-print">
        <div class="w-full px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2 md:gap-4 shrink-0">
                <a href="{{ route('warehouse.inventory') }}" class="flex items-center gap-2 text-xl font-extrabold tracking-tight text-sky-900 hover:text-sky-950 transition-all shrink-0">
                    <span class="bg-sky-600 text-white p-1.5 rounded-lg shadow-sm">📦</span>
                    <span class="whitespace-nowrap">ERP KHO</span>
                </a>

                @php
                    $user = auth()->user();
                    $isAdmin = $user && $user->role === 'admin';
                    $permissions = $user ? ($user->permissions ?? []) : [];
                    
                    $hasContacts = $isAdmin || in_array('warehouse.contacts', $permissions);
                    $hasKho = $isAdmin || array_intersect(['warehouse.product-catalog', 'warehouse.asset-manager', 'warehouse.stock-in', 'warehouse.stock-out', 'warehouse.inventory', 'warehouse.stock-transfer.index', 'warehouse.stock-recovery-report', 'warehouse.stock-count', 'warehouse.settings.warehouses'], $permissions);
                    $hasMaintenance = $isAdmin || array_intersect(['warehouse.asset-manager', 'warehouse.maintenance-dashboard', 'warehouse.maintenance-tracking', 'warehouse.maintenance-rules', 'warehouse.maintenance-plans', 'warehouse.maintenance-tickets'], $permissions);
                    $hasPurchase = $isAdmin || array_intersect(['warehouse.purchase-plan', 'warehouse.purchase-plan.history'], $permissions);
                    $hasReports = $isAdmin || array_intersect(['warehouse.reports.transaction-detail', 'warehouse.reports.daily', 'warehouse.reports.stock'], $permissions);
                    $hasChat = $isAdmin || in_array('warehouse.chat', $permissions);
                @endphp

                <div class="hidden md:flex items-center gap-1 shrink-0">
                    @if(session('current_house', 1) == 5)
                        <!-- HR Specific Menu -->
                        <div class="relative group shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('hr.permissions') || request()->routeIs('hr.users') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                1. HR MODULE
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                                <a href="{{ route('hr.permissions') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-b border-slate-100">Phân quyền</a>
                                <a href="{{ route('hr.users') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Quản lý nhân viên</a>
                            </div>
                        </div>
                        <a href="{{ route('hr.global-report') }}" class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 hover:bg-sky-200 hover:text-sky-950 {{ request()->routeIs('hr.global-report') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                            2. BÁO CÁO
                        </a>
                    @else
                        <!-- Module 1: NCC/KH -->
                        @if($hasContacts)
                        <a href="{{ route('warehouse.contacts') }}" class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 hover:bg-sky-200 hover:text-sky-950 {{ request()->routeIs('warehouse.contacts') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                            1. NCC/KH
                        </a>
                        @endif

                        <!-- Module 2: KHO -->
                        @if($hasKho)
                        <div class="relative group shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('warehouse.stock-*') || request()->routeIs('warehouse.inventory') || request()->routeIs('warehouse.product-*') || request()->routeIs('warehouse.asset-manager') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                2. KHO
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                                @if($isAdmin || in_array('warehouse.product-catalog', $permissions)) <a href="{{ route('warehouse.product-catalog') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 font-bold border-b border-slate-100">DANH MỤC VẬT TƯ</a> @endif
                                @if($isAdmin || in_array('warehouse.asset-manager', $permissions)) <a href="{{ route('warehouse.asset-manager') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 font-bold border-b border-slate-100">DANH MỤC THIẾT BỊ</a> @endif
                                @if($isAdmin || in_array('warehouse.stock-in', $permissions)) <a href="{{ route('warehouse.stock-in') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Nhập kho</a> @endif
                                @if($isAdmin || in_array('warehouse.stock-out', $permissions)) <a href="{{ route('warehouse.stock-out') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Xuất kho</a> @endif
                                @if($isAdmin || in_array('warehouse.inventory', $permissions)) <a href="{{ route('warehouse.inventory') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Tồn kho</a> @endif
                                @if($isAdmin || in_array('warehouse.stock-transfer.index', $permissions)) <a href="{{ route('warehouse.stock-transfer.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Chuyển kho</a> @endif
                                @if($isAdmin || in_array('warehouse.stock-recovery-report', $permissions)) <a href="{{ route('warehouse.stock-recovery-report') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Thu hồi phế phẩm</a> @endif
                                @if($isAdmin || in_array('warehouse.stock-count', $permissions)) <a href="{{ route('warehouse.stock-count') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Kiểm kê kho</a> @endif
                                @if($isAdmin || in_array('warehouse.settings.warehouses', $permissions)) <a href="{{ route('warehouse.settings.warehouses') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Cấu hình kho</a> @endif
                            </div>
                        </div>
                        @endif


                        <!-- Module 3: THEO DÕI BẢO DƯỠNG -->
                        @if($hasMaintenance)
                        <div class="relative group shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('warehouse.asset-manager') || request()->routeIs('maintenance.*') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                3. THEO DÕI BẢO DƯỠNG
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-72 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                                @if($isAdmin || in_array('warehouse.asset-manager', $permissions))
                                <a href="{{ route('warehouse.asset-manager') }}" class="block px-4 py-2 text-sm text-gray-700 font-bold bg-sky-50 hover:bg-sky-100 border-b border-sky-100">TRANG CHỦ TỔNG HỢP (7 IN 1)</a>
                                <a href="{{ route('warehouse.asset-manager', ['activeTab' => 'odo-manager']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Cập nhật giờ ODO hàng ngày</a>
                                <a href="{{ route('warehouse.asset-manager', ['activeTab' => 'bom-manager']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Định mức bảo dưỡng (BOM)</a>
                                <a href="{{ route('warehouse.asset-manager', ['activeTab' => 'ticket-list']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 border-t border-slate-50">Phiếu bảo dưỡng & Lịch</a>
                                <a href="{{ route('warehouse.asset-manager', ['activeTab' => 'shift-log']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 border-t border-slate-50">Giao ca / Nhật ký</a>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Module 4: KẾ HOẠCH & MUA HÀNG -->
                        @if($hasPurchase)
                        <div class="relative group ml-2 shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('purchase-plan*') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                4. KẾ HOẠCH & MUA HÀNG
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-64 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left z-50">
                                @if($isAdmin || in_array('warehouse.purchase-plan', $permissions)) <a href="{{ route('purchase-plan') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 font-bold text-indigo-700 hover:bg-indigo-50">1. Quản lý Kế hoạch</a> @endif
                                @if($isAdmin || in_array('warehouse.purchase-plan.history', $permissions)) <a href="{{ route('purchase-plan.history') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">2. Lịch sử mua hàng</a> @endif
                            </div>
                        </div>
                        @endif

                        <!-- Module 5: BÁO CÁO -->
                        @if($hasReports)
                        <div class="relative group ml-2 shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('warehouse.purchase-*') || request()->routeIs('warehouse.delivery-note') || request()->routeIs('warehouse.reports') || request()->routeIs('purchase-request') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                5. BÁO CÁO
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left z-50">
                                @if($isAdmin || in_array('warehouse.reports.transaction-detail', $permissions)) <a href="{{ route('warehouse.reports.transaction-detail') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 {{ request()->routeIs('warehouse.reports.transaction-detail') ? 'bg-slate-100 font-bold' : '' }}">Báo cáo chi tiết giao dịch</a> @endif
                                @if($isAdmin || in_array('warehouse.reports.daily', $permissions)) <a href="{{ route('warehouse.reports.daily') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 {{ request()->routeIs('warehouse.reports.daily') ? 'bg-slate-100 font-bold' : '' }}">Báo Cáo Ngày</a> @endif
                                @if($isAdmin || in_array('warehouse.reports.stock', $permissions)) <a href="{{ route('warehouse.reports.stock') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 {{ request()->routeIs('warehouse.reports.stock') ? 'bg-slate-100 font-bold' : '' }}">Báo Cáo Kho Tổng Hợp</a> @endif
                            </div>
                        </div>
                        @endif

                        <!-- Module 6: CHAT KHO -->
                        @if($hasChat)
                        @php
                            $lastRead = auth()->user()->last_read_chat_at ?? '2000-01-01 00:00:00';
                            $unreadCount = \App\Models\ChatMessage::where('created_at', '>', $lastRead)
                                ->where('user_id', '!=', auth()->id())
                                ->count();
                        @endphp
                        <a href="{{ route('warehouse.chat') }}" class="ml-2 px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 relative {{ request()->routeIs('warehouse.chat') ? 'bg-sky-200 text-sky-950 shadow-inner' : ($unreadCount > 0 ? 'text-red-600 bg-red-100 hover:bg-red-200 animate-pulse' : 'text-sky-900 hover:bg-sky-200 hover:text-sky-950') }}">
                            6. CHAT KHO
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[8px] font-bold text-white ring-2 ring-sky-100">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </a>
                        @endif
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- User Menu -->
                @auth
                    <div class="relative group shrink-0">
                        <button class="flex items-center gap-2 px-2 py-1.5 rounded-md bg-sky-200 hover:bg-sky-300 transition duration-150 text-xs whitespace-nowrap shrink-0 font-bold text-sky-950">
                            <span class="bg-sky-600 px-1.5 py-0.5 rounded text-[10px] text-white border border-sky-700">Dự án {{ session('current_house', 1) == 2 ? 'Hậu Nghĩa' : (session('current_house', 1) == 3 ? 'Cần Giờ' : (session('current_house', 1) == 4 ? 'Cần Giuộc' : 'Hóc Môn')) }}</span>
                            <span>👤</span>
                            <span>{{ Auth::user()->role === 'admin' ? 'Admin' : 'NV' }} - {{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute right-0 mt-0 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right -translate-y-2 group-hover:translate-y-0 z-50">
                            <div class="px-4 py-3 border-b border-gray-100 text-sm text-gray-600 text-left">
                                <div class="font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                            </div>

                            @if(Auth::user()->role === 'admin' && session('current_house', 1) == 5)
                                <a href="{{ route('hr.users') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 text-left">👥 Quản lý nhân viên</a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 mt-1 text-left">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-slate-100 font-medium">🚪 Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-md bg-sky-200 hover:bg-sky-300 transition duration-150 text-sm font-bold text-sky-950 whitespace-nowrap">
                        Đăng nhập
                    </a>
                @endauth
                <span class="text-xs text-sky-600 font-bold">v1.1</span>
            </div>
        </div>
    </nav>

    <main class="w-full px-2 py-2">
        <h1 class="text-2xl font-black text-slate-900 mb-1 uppercase tracking-tight no-print" style="font-family: 'Times New Roman', Times, serif;">
            {{ mb_strtoupper($title ?? '') }}
        </h1>
        {{ $slot }}
    </main>

    @livewireScripts
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
