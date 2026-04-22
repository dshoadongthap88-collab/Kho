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
    <nav class="bg-indigo-900 text-white shadow-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('warehouse.inventory') }}" class="flex items-center gap-2 text-xl font-extrabold tracking-tight">
                    <span class="bg-white text-indigo-900 p-1 rounded-lg">📦</span>
                    <span>ERP KHO</span>
                </a>
                
                <div class="hidden md:flex items-center gap-1">
                    <!-- Module 1: Thông tin NCC/KH -->
                    <a href="{{ route('warehouse.contacts') }}" class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 hover:bg-indigo-800 {{ request()->routeIs('warehouse.contacts') ? 'bg-indigo-800 text-white shadow-inner' : 'text-indigo-100' }}">
                        1. Thông tin NCC/KH
                    </a>

                    <!-- Module 2: Kho -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 group-hover:bg-indigo-800 flex items-center gap-1 {{ request()->routeIs('warehouse.stock-*') || request()->routeIs('warehouse.inventory') ? 'bg-indigo-800 text-white shadow-inner' : 'text-indigo-100' }}">
                            2. Kho
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0">
                            <a href="{{ route('warehouse.stock-in') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Nhập kho</a>
                            <a href="{{ route('warehouse.stock-out') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Xuất kho</a>
                            <a href="{{ route('warehouse.inventory') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Tồn kho</a>
                            <a href="{{ route('warehouse.stock-count') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Kiểm kê kho</a>
                        </div>
                    </div>

                    <!-- Module 3: Sản Phẩm, BOM/NVL -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 group-hover:bg-indigo-800 flex items-center gap-1 {{ request()->routeIs('warehouse.product-*') || request()->routeIs('warehouse.bom') || request()->routeIs('warehouse.material-*') ? 'bg-indigo-800 text-white shadow-inner' : 'text-indigo-100' }}">
                            3. Sản phẩm & BOM
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                            <a href="{{ route('warehouse.product-catalog') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Danh mục sản phẩm</a>
                            <a href="{{ route('warehouse.material-names') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Tên NVL</a>
                            <a href="{{ route('warehouse.bom') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">BOM/NVL</a>
                        </div>
                    </div>

                    <!-- Module 4: Tổng hợp -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 group-hover:bg-indigo-800 flex items-center gap-1 {{ request()->routeIs('warehouse.purchase-*') || request()->routeIs('warehouse.delivery-note') || request()->routeIs('warehouse.reports') ? 'bg-indigo-800 text-white shadow-inner' : 'text-indigo-100' }}">
                            4. Tổng hợp
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                            <a href="{{ route('warehouse.purchase-request') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Phiếu đề xuất mua hàng</a>
                            <a href="{{ route('warehouse.delivery-note') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Nhu cầu NVL</a>
                            <a href="{{ route('warehouse.reports') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Báo cáo tổng hợp</a>
                        </div>
                    </div>

                    <!-- Module 5: Giao hàng -->
                    <div class="relative group">
                        <button class="px-3 py-2 rounded-md text-sm font-medium transition duration-150 group-hover:bg-indigo-800 flex items-center gap-1 {{ request()->routeIs('warehouse.customer-*') || request()->routeIs('warehouse.delivery-report') ? 'bg-indigo-800 text-white shadow-inner' : 'text-indigo-100' }}">
                            5. Giao hàng
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute left-0 mt-0 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                            <a href="{{ route('warehouse.customer-debt') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Công nợ khách hàng</a>
                            <a href="{{ route('warehouse.delivery-report') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Báo cáo giao hàng</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <!-- User Menu -->
                @auth
                    <div class="relative group">
                        <button class="flex items-center gap-2 px-3 py-2 rounded-md bg-indigo-800 hover:bg-indigo-700 transition duration-150 text-sm font-medium text-white">
                            <span>👤</span>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div class="absolute right-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right -translate-y-2 group-hover:translate-y-0 z-50">
                            <div class="px-4 py-3 border-b border-gray-100 text-sm text-gray-600">
                                <div class="font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-gray-500">
                                    @if(Auth::user()->phone)
                                        {{ Auth::user()->phone }}
                                    @endif
                                    @if(Auth::user()->email)
                                        <br>{{ Auth::user()->email }}
                                    @endif
                                </div>
                                <div class="text-xs text-indigo-600 font-semibold mt-1">
                                    @switch(Auth::user()->role)
                                        @case('admin')
                                            👨‍💼 Quản trị viên
                                            @break
                                        @case('staff')
                                            👨‍💼 Nhân viên
                                            @break
                                        @default
                                            {{ Auth::user()->role }}
                                    @endswitch
                                </div>
                            </div>
                            <a href="{{ route('password.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">🔐 Đổi mật khẩu</a>
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">👥 Quản lý nhân viên</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">🚪 Đăng xuất</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 rounded-md bg-indigo-800 hover:bg-indigo-700 transition duration-150 text-sm font-medium text-white">
                        Đăng nhập
                    </a>
                @endauth
                <span class="text-xs text-indigo-300">v1.1</span>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <h1 class="text-3xl font-black text-slate-900 mb-6 uppercase tracking-tight no-print" style="font-family: 'Times New Roman', Times, serif;">
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
