<div class="mb-8">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">Dashboard Cảnh Báo Bảo Dưỡng</h2>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-2 mb-6">
        <div class="bg-white p-2 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 font-bold uppercase">Tổng Thiết Bị</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_assets'] }}</p>
        </div>
        <div class="bg-white p-2 rounded-lg shadow border-l-4 border-indigo-500">
            <p class="text-xs text-gray-500 font-bold uppercase">Tổng Giờ (ODO)</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_odo'], 1) }}</p>
        </div>
        <div class="bg-white p-2 rounded-lg shadow border-l-4 border-yellow-400">
            <p class="text-xs text-gray-500 font-bold uppercase">Đến Hạn 250h</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['due_250h'] }}</p>
        </div>
        <div class="bg-white p-2 rounded-lg shadow border-l-4 border-orange-400">
            <p class="text-xs text-gray-500 font-bold uppercase">Đến Hạn 500h</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['due_500h'] }}</p>
        </div>
        <div class="bg-white p-2 rounded-lg shadow border-l-4 border-red-500">
            <p class="text-xs text-gray-500 font-bold uppercase">Quá Hạn</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</p>
        </div>
        <div class="bg-white p-2 rounded-lg shadow border-l-4 border-green-500 flex flex-col justify-between">
            <div>
                <p class="text-xs text-gray-500 font-bold uppercase">Kế Hoạch</p>
            </div>
            <div class="flex justify-between items-end mt-1">
                <div>
                    <span class="text-sm text-gray-500 block">Chờ</span>
                    <span class="text-lg font-bold text-yellow-600">{{ $stats['pending_tickets'] }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-500 block">Hoàn thành</span>
                    <span class="text-lg font-bold text-green-600">{{ $stats['completed_tickets'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Assets List -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-2 py-2 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Mã TS</th>
                    <th class="px-2 py-2 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Tên Thiết Bị</th>
                    <th class="px-2 py-2 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">ODO Hiện Tại</th>
                    <th class="px-2 py-2 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Chu Kỳ (h)</th>
                    <th class="px-2 py-2 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Giờ Còn Lại</th>
                    <th class="px-2 py-2 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Trạng Thái</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($assets as $asset)
                    <tr class="hover:bg-slate-50">
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-gray-900">{{ $asset['asset_code'] }}</td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm font-medium text-gray-600">{{ $asset['name'] }}</td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-900">{{ number_format($asset['current_odo'], 1) }}</td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-500">{{ $asset['maintenance_cycle'] }}</td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold {{ $asset['is_overdue'] ? 'text-red-600' : ($asset['is_warning'] ? 'text-yellow-600' : 'text-green-600') }}">
                            {{ number_format($asset['hours_remaining'], 1) }}h
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-sm font-semibold">
                            @if($asset['is_overdue'])
                                <span class="px-1.5 py-1 text-[11px] inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800 border border-red-200">
                                    Đến hạn bảo dưỡng
                                </span>
                            @elseif($asset['is_warning'])
                                <span class="px-1.5 py-1 text-[11px] inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    Sắp bảo dưỡng
                                </span>
                            @else
                                <span class="px-1.5 py-1 text-[11px] inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Bình thường
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
