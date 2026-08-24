<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Kho' }} - ERP Warehouse</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Tailwind build sẵn. Trước đây dùng Play CDN (407KB JS, biên dịch CSS
         ngay trong trình duyệt mỗi lần tải trang) — chậm và phụ thuộc máy chủ
         ngoài. Nay là CSS tĩnh ~15KB gzip. Sau khi sửa view phải chạy lại
         `npm run build`. --}}
    @vite('resources/css/app.css')
    @livewireStyles
<style>
    @page { margin: 0; }
    @media print { 
        body { padding: 1.5cm; }
        .no-print { display: none !important; } 
    }

    /* ============================================================
       DESIGN SYSTEM — Form Elements
       Áp dụng toàn app: input, select, textarea, checkbox, radio
    ============================================================ */

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="number"],
    input[type="search"],
    input[type="tel"],
    input[type="url"],
    input[type="date"],
    input[type="time"],
    input[type="datetime-local"],
    select,
    textarea {
        display: block;
        width: 100%;
        padding: 0.45rem 0.75rem;
        font-size: 0.8125rem;
        line-height: 1.5;
        font-weight: 500;
        color: #1e293b;
        background-color: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 0.625rem;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        -webkit-appearance: none;
        appearance: none;
    }

    input::placeholder,
    textarea::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    input[type="number"]:focus,
    input[type="search"]:focus,
    input[type="tel"]:focus,
    input[type="url"]:focus,
    input[type="date"]:focus,
    input[type="time"]:focus,
    input[type="datetime-local"]:focus,
    select:focus,
    textarea:focus {
        border-color: #6366f1;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    input:disabled,
    select:disabled,
    textarea:disabled {
        background-color: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
        border-color: #e2e8f0;
    }

    input[readonly],
    textarea[readonly] {
        background-color: #f8fafc;
        color: #64748b;
        cursor: default;
    }

    select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.6rem center;
        background-size: 1rem;
        padding-right: 2rem;
        cursor: pointer;
    }

    textarea {
        resize: vertical;
        min-height: 60px;
    }

    input[type="checkbox"],
    input[type="radio"] {
        width: 1rem;
        height: 1rem;
        display: inline-block;
        accent-color: #6366f1;
        cursor: pointer;
        flex-shrink: 0;
    }

    input.is-invalid,
    select.is-invalid,
    textarea.is-invalid {
        border-color: #f43f5e;
        background-color: #fff1f2;
    }
    input.is-invalid:focus,
    select.is-invalid:focus,
    textarea.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.12);
    }

    .input-sm {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
        border-radius: 0.5rem;
    }

    .input-lg {
        padding: 0.625rem 1rem;
        font-size: 0.9375rem;
        border-radius: 0.75rem;
    }

    label.form-label {
        display: block;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
        margin-bottom: 0.375rem;
    }

    .date-range-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        background: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 0.75rem;
        padding: 0.35rem 0.75rem;
    }
    .date-range-pill input[type="date"] {
        width: auto;
        border: none;
        background: transparent;
        padding: 0;
        font-size: 0.75rem;
        font-weight: 700;
        color: #334155;
        box-shadow: none;
    }
    .date-range-pill input[type="date"]:focus {
        box-shadow: none;
        border-color: transparent;
        background: transparent;
    }
    .date-range-pill:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .search-input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: 0.65rem center;
        background-size: 0.9rem;
        padding-left: 2rem;
    }

    /* ============================================================
       Thanh bộ lọc — dàn các ô lọc thành lưới đều nhau
       Dùng:  .filter-bar > .filter-grid > .filter-field
       Các ô luôn thẳng hàng và tự xuống dòng gọn, không còn cảnh
       mỗi ô một chiều rộng rồi so le nhau như khi dùng flex-wrap.
    ============================================================ */
    .filter-bar {
        background: #ffffff;
        border: 1px solid #e2e8f0;      /* slate-200 */
        border-radius: 1rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        padding: 0.875rem 1rem;
        margin-bottom: 1rem;
    }

    .filter-grid {
        display: grid;
        /* Mỗi ô lọc tối đa 250px — xếp được bao nhiêu cột thì xếp, phần thừa
           để trống bên phải chứ không kéo giãn ô ra cho đầy hàng. */
        grid-template-columns: repeat(auto-fill, minmax(10rem, 250px));
        gap: 0.625rem 0.75rem;
        align-items: end;
        /* Neu luoi nam trong mot flex container thi phai gian ra chiem het cho
           trong. Thieu dong nay, grid co lai vua noi dung -> tinh ra it cot,
           xuong nhieu hang, va de ho mot khoang trong lon ben canh cum nut.
           Khi khong phai flex item thi 2 thuoc tinh nay bi bo qua, vo hai. */
        flex: 1 1 auto;
        min-width: 0;
    }

    /* min-width:0 để ô co theo cột, không phình làm vỡ lưới */
    .filter-field {
        min-width: 0;
        max-width: 250px;
    }

    /* Ô cần rộng hơn — chiếm 2 cột, dùng khi thật sự cần (vd: khoảng ngày) */
    .filter-grid > .filter-wide {
        grid-column: span 2;
        max-width: none;
    }

    /* Cụm nút hành động: chiếm trọn hàng cuối, dồn về phải */
    .filter-actions {
        grid-column: 1 / -1;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    /* Dải trạng thái chọn (vd: "10 đã chọn") nằm bên trái cụm nút */
    .filter-actions > .filter-actions-note { margin-right: auto; }

    @media (max-width: 640px) {
        .filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .filter-grid > .filter-wide { grid-column: 1 / -1; }
        .filter-actions > * { flex: 1 1 auto; justify-content: center; }
        .filter-actions > .filter-actions-note { flex-basis: 100%; margin-right: 0; }
    }

    /* Hien thi khi in / khi xem man hinh. Cot Vi tri o Ton kho la o nhap
       lieu tren man hinh nhung khi in phai ra chu thuong. */
    .print-only { display: none; }
    @media print {
        .print-only { display: inline !important; }
    }

    /* ============================================================
       Toast — thông báo nổi góc dưới phải
       Dùng: showToast('Nội dung', '✅')  hoặc từ Livewire:
             $this->dispatch('toast', message: '...', icon: '✅')
    ============================================================ */
    #toast-container {
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        pointer-events: none;
    }
    .custom-toast {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.25rem;
        border-radius: 1rem;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        max-width: 26rem;
        pointer-events: auto;
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.25);
        backdrop-filter: blur(10px);
        animation: toastIn 0.3s ease-out forwards;
    }
    .custom-toast.toast-success { background: rgba(5, 122, 85, 0.96); }
    .custom-toast.toast-error   { background: rgba(159, 18, 57, 0.96); }
    .custom-toast.toast-info    { background: rgba(30, 41, 59, 0.96); }
    .custom-toast .toast-icon   { font-size: 1rem; flex-shrink: 0; }
    .custom-toast .toast-text   { text-transform: none; letter-spacing: 0; font-weight: 700; }
    .custom-toast.hide { animation: toastOut 0.3s ease-in forwards; }
    @keyframes toastIn  { from { transform: translateY(1rem); opacity: 0 } to { transform: none; opacity: 1 } }
    @keyframes toastOut { from { opacity: 1 } to { opacity: 0; transform: translateY(0.5rem) } }
    @media print { #toast-container { display: none !important; } }
</style>
<script>
    let originalTitle = document.title;
    window.addEventListener("beforeprint", function() {
        originalTitle = document.title;
        document.title = "";
    });
    window.addEventListener("afterprint", function() {
        document.title = originalTitle;
    });
</script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-sky-100 text-sky-950 border-b border-sky-200 shadow-md sticky top-0 z-50 print:hidden">
        <div class="w-full px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2 md:gap-4 shrink-0">
                <a href="{{ route('warehouse.inventory') }}" class="flex items-center gap-2 text-xl font-extrabold tracking-tight text-sky-900 hover:text-sky-950 transition-all shrink-0">
                    <span class="bg-sky-600 text-white p-1.5 rounded-lg shadow-sm">📦</span>
                    <span class="whitespace-nowrap">ERP KHO</span>
                </a>

                <div class="hidden md:flex items-center gap-1 shrink-0">
                    @if(session('current_house', 1) == 5)
                        <!-- HR Specific Menu -->
                        <div class="relative group shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('hr.permissions') || request()->routeIs('hr.users') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                1. HR MODULE
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
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
                        <a href="{{ route('warehouse.contacts') }}" class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 hover:bg-sky-200 hover:text-sky-950 {{ request()->routeIs('warehouse.contacts') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                            1. NCC/KH
                        </a>

                        <!-- Module 2: KHO -->
                        <div class="relative group shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('warehouse.stock-*') || request()->routeIs('warehouse.inventory') || request()->routeIs('warehouse.product-*') || request()->routeIs('warehouse.asset-manager') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                2. KHO
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                                <a href="{{ route('warehouse.product-catalog') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 font-bold border-b border-slate-100">DANH MỤC VẬT TƯ</a>
                                <a href="{{ route('warehouse.asset-manager') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 font-bold border-b border-slate-100">DANH MỤC THIẾT BỊ</a>
                                <a href="{{ route('warehouse.stock-in') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Nhập kho</a>
                                <a href="{{ route('warehouse.stock-out') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Xuất kho</a>
                                <a href="{{ route('warehouse.inventory') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Tồn kho</a>
                                <a href="{{ route('warehouse.stock-transfer.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Chuyển kho</a>
                                <a href="{{ route('warehouse.stock-recovery-report') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Thu hồi phế phẩm</a>
                                <a href="{{ route('warehouse.stock-count') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Kiểm kê kho</a>
                                <a href="{{ route('warehouse.settings.warehouses') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Cấu hình kho</a>
                            </div>
                        </div>

                        <!-- Module 3: THEO DÕI BẢO DƯỠNG -->
                        <div class="relative group shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('warehouse.asset-manager') || request()->routeIs('maintenance.*') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                3. THEO DÕI BẢO DƯỠNG
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-72 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left">
                                <a href="{{ route('warehouse.asset-manager') }}" class="block px-4 py-2 text-sm text-gray-700 font-bold bg-sky-50 hover:bg-sky-100 border-b border-sky-100">TRANG CHỦ TỔNG HỢP (7 IN 1)</a>
                                <a href="{{ route('warehouse.asset-manager', ['activeTab' => 'odo-manager']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100">Cập nhật giờ ODO hàng ngày</a>
                                <a href="{{ route('warehouse.asset-manager', ['activeTab' => 'bom-manager']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">Định mức bảo dưỡng (BOM)</a>
                                <a href="{{ route('warehouse.asset-manager', ['activeTab' => 'ticket-list']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 border-t border-slate-50">Phiếu bảo dưỡng & Lịch</a>
                                <a href="{{ route('warehouse.asset-manager', ['activeTab' => 'shift-log']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-50 border-t border-slate-50">Giao ca / Nhật ký</a>
                            </div>
                        </div>

                        <!-- Module 4: KẾ HOẠCH & MUA HÀNG -->
                        <div class="relative group ml-2 shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('purchase-plan*') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                4. KẾ HOẠCH & MUA HÀNG
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-64 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left z-50">
                                <a href="{{ route('purchase-plan') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 font-bold text-indigo-700 hover:bg-indigo-50">1. Quản lý Kế hoạch</a>
                                <a href="{{ route('purchase-plan.history') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 border-t border-slate-50">2. Lịch sử mua hàng</a>
                            </div>
                        </div>

                        <!-- Module 5: BÁO CÁO -->
                        <div class="relative group ml-2 shrink-0">
                            <button class="px-2 py-1.5 rounded-md text-xs whitespace-nowrap shrink-0 font-bold transition duration-150 group-hover:bg-sky-200 group-hover:text-sky-950 flex items-center gap-1 {{ request()->routeIs('warehouse.purchase-*') || request()->routeIs('warehouse.delivery-note') || request()->routeIs('warehouse.reports') || request()->routeIs('purchase-request') ? 'bg-sky-200 text-sky-950 shadow-inner' : 'text-sky-900' }}">
                                5. BÁO CÁO
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>
                            <div class="absolute left-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-left -translate-y-2 group-hover:translate-y-0 text-left z-50">
                                <a href="{{ route('warehouse.reports.transaction-detail') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 {{ request()->routeIs('warehouse.reports.transaction-detail') ? 'bg-slate-100 font-bold' : '' }}">Báo cáo chi tiết giao dịch</a>
                                <a href="{{ route('warehouse.reports.daily') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 {{ request()->routeIs('warehouse.reports.daily') ? 'bg-slate-100 font-bold' : '' }}">Báo Cáo Ngày</a>
                                <a href="{{ route('warehouse.reports.stock') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-slate-100 {{ request()->routeIs('warehouse.reports.stock') ? 'bg-slate-100 font-bold' : '' }}">Báo Cáo Kho Tổng Hợp</a>
                            </div>
                        </div>

                        <!-- Module 6: CHAT KHO -->
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
        <h1 class="text-2xl font-black text-slate-900 mb-1 uppercase tracking-tight print:hidden" style="font-family: 'Times New Roman', Times, serif;">
            {{ mb_strtoupper($title ?? '') }}
        </h1>
        {{ $slot }}
    </main>

    @livewireScripts
    @stack('scripts')
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
    {{-- Toast dùng chung cho mọi màn. Gọi bằng showToast('...', '✅') ở JS,
         hoặc từ component Livewire: $this->dispatch('toast', message: '...') --}}
    <div id="toast-container" class="no-print"></div>
    <script>
        window.showToast = function (message, icon, type, duration) {
            icon = icon || '✅';
            type = type || 'success';
            duration = duration || 3000;

            var container = document.getElementById('toast-container');
            if (!container) return;

            var toast = document.createElement('div');
            toast.className = 'custom-toast toast-' + type;

            var i = document.createElement('span');
            i.className = 'toast-icon';
            i.textContent = icon;

            // textContent chứ không phải innerHTML: nội dung có thể là tên vật tư
            // do người dùng nhập, nhét thẳng vào HTML là mở đường cho XSS.
            var t = document.createElement('span');
            t.className = 'toast-text';
            t.textContent = message;

            toast.appendChild(i);
            toast.appendChild(t);
            container.appendChild(toast);

            setTimeout(function () {
                toast.classList.add('hide');
                setTimeout(function () { toast.remove(); }, 300);
            }, duration);
        };

        // Cho phép mọi component Livewire bắn toast mà không cần tự viết JS
        document.addEventListener('livewire:init', function () {
            Livewire.on('toast', function (e) {
                var d = Array.isArray(e) ? (e[0] || {}) : (e || {});
                showToast(d.message || '', d.icon, d.type, d.duration);
            });
        });
    </script>
</body>
</html>
