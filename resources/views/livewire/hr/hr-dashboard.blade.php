<div>
    <div class="mb-8 p-2 bg-gradient-to-r from-purple-700 to-indigo-800 rounded-2xl shadow-xl text-white relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute top-0 right-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
            <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" /></svg>
        </div>
        
        <div class="relative z-10">
            <h2 class="text-3xl font-extrabold mb-2">Trung tâm Điều khiển HR</h2>
            <p class="text-purple-100 text-lg">Quản lý toàn diện hệ thống, phân quyền và giám sát báo cáo từ các dự án.</p>
        </div>
        
        <div class="relative z-10 mt-6 grid grid-cols-1 md:grid-cols-3 gap-2">
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-2 border border-white/20">
                <div class="text-purple-200 text-sm font-semibold uppercase tracking-wider mb-1">Tổng số Dự án</div>
                <div class="text-3xl font-bold">{{ $stats['total_projects'] }}</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-2 border border-white/20">
                <div class="text-purple-200 text-sm font-semibold uppercase tracking-wider mb-1">Nhân sự Hệ thống</div>
                <div class="text-3xl font-bold">{{ $stats['total_users'] }}</div>
            </div>
            <div class="bg-white/10 backdrop-blur-md rounded-xl p-2 border border-white/20">
                <div class="text-purple-200 text-sm font-semibold uppercase tracking-wider mb-1">Trạng thái</div>
                <div class="text-xl font-bold flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-400 animate-pulse"></span> Hoạt động tốt
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- CÀI ĐẶT CÁC CHỨC NĂNG -->
        <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            <div class="p-2 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="p-2 bg-blue-100 text-blue-600 rounded-lg">⚙️</span>
                        Cài đặt các chức năng
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Quản lý thiết lập và quyền hạn của hệ thống.</p>
                </div>
            </div>
            <div class="p-2 space-y-4">
                <a href="{{ route('hr.projects') }}" class="group flex items-start gap-2 p-2 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50 transition-all duration-200">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 group-hover:scale-110 transition-transform">
                        🏢
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 group-hover:text-blue-700">Quản lý Dự án (Ngôi nhà)</h4>
                        <p class="text-sm text-slate-600 mt-1">Thêm mới, cấu hình và quản lý thông tin các dự án trong hệ thống.</p>
                    </div>
                </a>

                <a href="{{ route('hr.permissions') }}" class="group flex items-start gap-2 p-2 rounded-xl border border-slate-200 hover:border-purple-300 hover:bg-purple-50 transition-all duration-200">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 group-hover:scale-110 transition-transform">
                        🔐
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-800 group-hover:text-purple-700">Phân quyền Hệ thống</h4>
                        <p class="text-sm text-slate-600 mt-1">Cài đặt vai trò, gán quyền truy cập cho nhân viên vào từng dự án cụ thể.</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- NHẬN BÁO CÁO TỪ CÁC DỰ ÁN -->
        <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            <div class="p-2 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">📊</span>
                        Báo cáo từ các dự án
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Nơi tiếp nhận số liệu và phân tích dữ liệu tổng hợp.</p>
                </div>
            </div>
            <div class="p-2">
                <a href="{{ route('hr.global-report') }}" class="block relative overflow-hidden rounded-xl border border-slate-200 hover:border-emerald-300 group transition-all duration-200">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-teal-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="p-2 relative z-10 group-hover:text-white transition-colors duration-300">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xl font-bold text-slate-800 group-hover:text-white flex items-center gap-2">
                                📈 Báo cáo Tổng hợp Hệ thống
                            </h4>
                            <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2 py-1 rounded-full group-hover:bg-white/20 group-hover:text-white">Mới nhất</span>
                        </div>
                        <p class="text-slate-600 group-hover:text-emerald-100">Truy cập giao diện phân tích toàn diện để xem báo cáo nhập xuất tồn, nhu cầu NVL và chi tiết giao dịch từ tất cả các kho dự án.</p>
                        
                        <div class="mt-6 flex items-center text-emerald-600 font-semibold group-hover:text-white">
                            Xem báo cáo chi tiết <svg class="w-5 h-5 ml-2 group-hover:translate-x-2 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </div>
                    </div>
                </a>

                <div class="mt-6 rounded-xl bg-slate-50 p-2 border border-slate-100">
                    <h5 class="text-sm font-bold text-slate-700 mb-3">Hoạt động gần đây</h5>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-slate-600">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            Hệ thống đã sẵn sàng nhận báo cáo mới từ các dự án.
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Dữ liệu tổng hợp được cập nhật theo thời gian thực (Real-time).
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
