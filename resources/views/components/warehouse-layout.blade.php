<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Kho' }} - ERP Warehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-slate-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <span class="text-xl font-bold">📦 ERP Kho</span>
                <a href="{{ route('warehouse.product-catalog') }}" class="hover:text-indigo-300 transition text-sm {{ request()->routeIs('warehouse.product-catalog') ? 'text-indigo-300 font-semibold' : '' }}">Tên sản phẩm</a>
                <a href="{{ route('warehouse.contacts') }}" class="hover:text-amber-300 transition text-sm {{ request()->routeIs('warehouse.contacts') ? 'text-amber-300 font-semibold' : '' }}">Khách hàng/NCC</a>
                <a href="{{ route('warehouse.inventory') }}" class="hover:text-indigo-300 transition text-sm {{ request()->routeIs('warehouse.inventory') ? 'text-indigo-300 font-semibold' : '' }}">Tồn kho</a>
                <a href="{{ route('warehouse.stock-in') }}" class="hover:text-green-300 transition text-sm {{ request()->routeIs('warehouse.stock-in') ? 'text-green-300 font-semibold' : '' }}">Nhập kho</a>
                <a href="{{ route('warehouse.stock-out') }}" class="hover:text-orange-300 transition text-sm {{ request()->routeIs('warehouse.stock-out') ? 'text-orange-300 font-semibold' : '' }}">Xuất kho</a>
                <a href="{{ route('warehouse.stock-count') }}" class="hover:text-yellow-300 transition text-sm {{ request()->routeIs('warehouse.stock-count') ? 'text-yellow-300 font-semibold' : '' }}">Kiểm kê</a>
                <a href="{{ route('warehouse.bom') }}" class="hover:text-purple-300 transition text-sm {{ request()->routeIs('warehouse.bom') ? 'text-purple-300 font-semibold' : '' }}">BOM / NVL</a>
                <a href="{{ route('warehouse.reports') }}" class="hover:text-cyan-300 transition text-sm {{ request()->routeIs('warehouse.reports') ? 'text-cyan-300 font-semibold' : '' }}">Báo cáo</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ $title ?? '' }}</h1>
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
