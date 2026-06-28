<div class="px-4 pb-10">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">Dashboard thống kê tình trạng bảo dưỡng thiết bị</p>
        </div>
        <div class="flex gap-2">
            <select wire:model.live="month" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-indigo-500 focus:border-indigo-500 font-bold">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ sprintf('%02d', $i) }}">Tháng {{ $i }}</option>
                @endfor
            </select>
            <select wire:model.live="year" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-indigo-500 focus:border-indigo-500 font-bold">
                @for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                    <option value="{{ $i }}">Năm {{ $i }}</option>
                @endfor
            </select>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
                <span>📊</span> Xuất Báo Cáo
            </button>
        </div>
    </div>

    <!-- 4 Cards Tổng Quan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Tổng Thiết Bị -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 relative overflow-hidden">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase">Tổng Thiết Bị</p>
                    <h3 class="text-3xl font-black text-gray-800 mt-2">{{ number_format($totalAssets) }}</h3>
                </div>
                <div class="p-3 bg-gray-50 rounded-lg">
                    <span class="text-2xl">🚜</span>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-500">Đang hoạt động trong hệ thống</div>
            <div class="absolute -right-4 -bottom-4 opacity-5 text-8xl z-0">🚜</div>
        </div>

        <!-- Bình Thường -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-sm p-6 relative overflow-hidden text-white">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-sm font-semibold text-green-100 uppercase">Bình Thường</p>
                    <h3 class="text-3xl font-black text-white mt-2">{{ number_format($normalCount) }}</h3>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <span class="text-2xl">✅</span>
                </div>
            </div>
            <div class="mt-4 text-sm text-green-100">Chiếm {{ $totalAssets > 0 ? round(($normalCount/$totalAssets)*100, 1) : 0 }}% tổng thiết bị</div>
        </div>

        <!-- Sắp Đến Hạn -->
        <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-xl shadow-sm p-6 relative overflow-hidden text-white">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-sm font-semibold text-yellow-100 uppercase">Sắp Đến Hạn</p>
                    <h3 class="text-3xl font-black text-white mt-2">{{ number_format($warningCount) }}</h3>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <span class="text-2xl">⚠️</span>
                </div>
            </div>
            <div class="mt-4 text-sm text-yellow-100">Cần chuẩn bị vật tư</div>
        </div>

        <!-- Quá Hạn -->
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-sm p-6 relative overflow-hidden text-white">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-sm font-semibold text-red-100 uppercase">Đến Hạn / Quá Hạn</p>
                    <h3 class="text-3xl font-black text-white mt-2">{{ number_format($overdueCount) }}</h3>
                </div>
                <div class="p-3 bg-white/20 rounded-lg">
                    <span class="text-2xl">🚨</span>
                </div>
            </div>
            <div class="mt-4 text-sm text-red-100">Dừng máy để bảo dưỡng ngay</div>
        </div>
    </div>

    <!-- 2 Cột Thống Kê Chi Tiết -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Cột Trái: Danh Sách Ưu Tiên Cao (Overdue) -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm col-span-1">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-red-50/50 rounded-t-xl">
                <h3 class="font-bold text-red-800">🔥 Danh sách thiết bị Cần BD Khẩn</h3>
                <span class="text-xs font-bold text-red-600 bg-red-100 px-2 py-1 rounded">{{ count($highPriorityAssets) }} thiết bị</span>
            </div>
            <div class="p-0">
                <ul class="divide-y divide-gray-100">
                    @forelse($highPriorityAssets as $item)
                        <li class="p-4 hover:bg-slate-50 transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-sm text-gray-900">{{ $item['asset']->name }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $item['asset']->asset_code }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-red-600">Quá {{ number_format(abs($item['remaining'])) }} giờ</p>
                                    <a href="{{ route('warehouse.maintenance-tracking') }}?search={{ $item['asset']->asset_code }}" class="text-xs text-indigo-600 hover:underline mt-1 inline-block">Xem chi tiết &rarr;</a>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="p-8 text-center text-gray-500">
                            <span class="text-3xl mb-2 block">🎉</span>
                            Không có thiết bị nào đang quá hạn.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Cột Phải: Thống Kê Trong Tháng -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm col-span-2">
            <div class="p-5 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                <h3 class="font-bold text-gray-800">Lịch sử bảo dưỡng thực tế tháng {{ $month }}/{{ $year }}</h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100 text-center">
                        <p class="text-xs font-bold text-indigo-800 uppercase mb-1">Số thiết bị đã bảo dưỡng</p>
                        <p class="text-3xl font-black text-indigo-600">{{ number_format($maintainedCount) }}</p>
                    </div>
                    <div class="bg-sky-50 rounded-lg p-4 border border-sky-100 text-center">
                        <p class="text-xs font-bold text-sky-800 uppercase mb-1">Tổng chi phí (nếu có)</p>
                        <p class="text-3xl font-black text-sky-600">{{ number_format($totalCost) }} đ</p>
                    </div>
                </div>

                <h4 class="font-bold text-sm text-gray-700 mb-3 border-b pb-2">Danh sách phiếu hoàn thành trong tháng</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="text-left text-xs font-bold text-gray-500 uppercase py-2">Ngày</th>
                                <th class="text-left text-xs font-bold text-gray-500 uppercase py-2">Thiết Bị</th>
                                <th class="text-left text-xs font-bold text-gray-500 uppercase py-2">Hạng Mục</th>
                                <th class="text-right text-xs font-bold text-gray-500 uppercase py-2">Người TH</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($ticketsThisMonth as $ticket)
                                <tr>
                                    <td class="py-3 text-sm text-gray-600">{{ $ticket->maintenance_date ? $ticket->maintenance_date->format('d/m/Y') : '' }}</td>
                                    <td class="py-3 text-sm font-semibold text-gray-900">{{ $ticket->asset->name ?? '' }}</td>
                                    <td class="py-3 text-sm text-indigo-600 font-medium">{{ $ticket->maintenance_rule_id }}</td>
                                    <td class="py-3 text-sm text-gray-600 text-right">{{ $ticket->staff_name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-sm text-gray-500">Chưa có phiếu bảo dưỡng nào hoàn thành trong tháng này.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
