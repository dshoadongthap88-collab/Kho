{{--
    Dự án đang chọn trên thanh tiêu đề — bấm vào để chuyển sang dự án khác.

    Dùng chung cho cả hai layout (warehouse-layout và layouts/app) để tên dự án
    và danh sách quyền không bị lệch giữa hai nơi.

    Tên đọc thẳng từ bảng projects, không viết cứng trong giao diện: thêm dự án
    mới là tự hiện, khỏi phải sửa layout.
--}}
@auth
    @php
        $nguoiDung   = Auth::user();
        $duAnHienTai = (int) session('current_house', 1);

        $danhSachDuAn = \Illuminate\Support\Facades\Cache::remember(
            'layout_projects', 300,
            fn() => \App\Models\Project::orderBy('id')->get(['id', 'name'])
        );

        // Chỉ liệt kê dự án người dùng có quyền vào — đúng phép mà
        // TenantController dùng, để menu không gợi ý chỗ bấm vào sẽ bị chặn.
        //
        // allowed_houses lưu JSON nên phần tử có thể là chuỗi ("1") hoặc số —
        // ép về số, nếu không nhân viên có quyền hợp lệ vẫn thấy menu rỗng.
        $duAnChoPhep = $nguoiDung->role === 'admin'
            ? $danhSachDuAn->pluck('id')->all()
            : array_map('intval', is_array($nguoiDung->allowed_houses) ? $nguoiDung->allowed_houses : []);

        $tenDuAn  = optional($danhSachDuAn->firstWhere('id', $duAnHienTai))->name ?? 'Chưa chọn';
        $duAnKhac = $danhSachDuAn->filter(
            fn($p) => in_array($p->id, $duAnChoPhep) && $p->id !== $duAnHienTai
        );
    @endphp

    <div class="relative group shrink-0">
        <button type="button"
                class="flex items-center gap-1.5 px-2 py-1.5 rounded-md bg-sky-600 hover:bg-sky-700 transition duration-150 text-[11px] whitespace-nowrap font-bold text-white border border-sky-700"
                title="Đổi dự án">
            <span>🏢</span>
            <span>{{ $tenDuAn }}</span>
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div class="absolute right-0 mt-0 w-56 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right -translate-y-2 group-hover:translate-y-0 z-50">
            <div class="px-3 py-2 border-b border-gray-100">
                <div class="text-[10px] uppercase tracking-wide text-slate-400 font-bold">Đang làm việc tại</div>
                <div class="text-sm font-bold text-slate-800">{{ $tenDuAn }}</div>
            </div>

            @forelse($duAnKhac as $duAn)
                {{-- Sang màn chọn dự án với ô nhập PIN mở sẵn.
                     Vẫn phải nhập PIN — chỉ bớt một cú bấm. --}}
                <a href="{{ route('tenant.select-house', ['house' => $duAn->id]) }}"
                   class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-800 text-left">
                    <span class="text-slate-400">🔄</span>
                    <span class="font-semibold">{{ $duAn->name }}</span>
                </a>
            @empty
                <div class="px-3 py-2 text-xs text-slate-400 italic">Bạn chỉ được vào dự án này</div>
            @endforelse

            <a href="{{ route('tenant.select-house') }}"
               class="block px-3 py-2 text-xs text-slate-500 hover:bg-slate-100 border-t border-gray-100 text-left">
                📋 Xem tất cả dự án
            </a>
        </div>
    </div>
@endauth
