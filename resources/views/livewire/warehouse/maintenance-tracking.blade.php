<div class="px-4 pb-10">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-2">
        <div>
            <p class="text-sm text-gray-500">Bảng theo dõi và cảnh báo bảo dưỡng tự động (theo giờ máy)</p>
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
                <span>📊</span> Xuất Excel
            </button>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
                <span>🖨️</span> In Danh Sách
            </button>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="bg-white p-2 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-2 items-center justify-between">
        <div class="w-full md:w-1/3 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm mã máy, tên máy..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
        </div>
        
        <div class="flex gap-2">
            <button wire:click="$set('statusFilter', 'ALL')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $statusFilter === 'ALL' || $statusFilter === '' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">Tất cả</button>
            <button wire:click="$set('statusFilter', 'DEN_HAN')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $statusFilter === 'DEN_HAN' ? 'bg-red-50 text-red-700 border border-red-200' : 'text-gray-600 hover:bg-gray-50' }}">Đến hạn</button>
            <button wire:click="$set('statusFilter', 'SAP_DEN')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $statusFilter === 'SAP_DEN' ? 'bg-yellow-50 text-yellow-700 border border-yellow-200' : 'text-gray-600 hover:bg-gray-50' }}">Sắp đến</button>
            <button wire:click="$set('statusFilter', 'BINH_THUONG')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $statusFilter === 'BINH_THUONG' ? 'bg-green-50 text-green-700 border border-green-200' : 'text-gray-600 hover:bg-gray-50' }}">Bình thường</button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wider">Mã Máy</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wider">Cấp BD</th>
                        <th class="px-2 py-2 text-right text-xs font-bold uppercase tracking-wider">Chu Kỳ (Giờ)</th>
                        <th class="px-2 py-2 text-right text-xs font-bold uppercase tracking-wider">Giờ BD<br>Gần Nhất</th>
                        <th class="px-2 py-2 text-right text-xs font-bold uppercase tracking-wider">Giờ<br>Hiện Tại</th>
                        <th class="px-2 py-2 text-right text-xs font-bold uppercase tracking-wider">Giờ<br>Tới Hạn</th>
                        <th class="px-2 py-2 text-right text-xs font-bold uppercase tracking-wider">Giờ<br>Còn Lại</th>
                        <th class="px-2 py-2 text-center text-xs font-bold uppercase tracking-wider">Trạng Thái</th>
                        <th class="px-2 py-2 text-left text-xs font-bold uppercase tracking-wider">Cảnh Báo</th>
                        <th class="px-2 py-2 text-center text-xs font-bold uppercase tracking-wider">Mức Ưu Tiên</th>
                        <th class="px-2 py-2 text-center text-xs font-bold uppercase tracking-wider">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($trackingList as $item)
                        <tr class="hover:bg-slate-50 transition-colors {{ $item['color'] === 'red' ? 'bg-red-50/30' : ($item['color'] === 'yellow' ? 'bg-yellow-50/30' : '') }}">
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ $item['asset_code'] }}<br>
                                <span class="text-xs font-normal text-gray-500">{{ $item['asset_name'] }}</span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-indigo-600">
                                {{ $item['rule_code'] ?: $item['rule_name'] }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-600 font-bold text-right">{{ number_format($item['cycle']) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($item['last_hours']) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-gray-900 text-right">{{ number_format($item['current_hours']) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-blue-600 text-right">{{ number_format($item['target_hours']) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-right 
                                {{ $item['color'] === 'red' ? 'text-red-600' : ($item['color'] === 'yellow' ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ number_format($item['remaining_hours']) }}
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                @if($item['color'] === 'red')
                                    <span class="px-1.5 py-1 text-[11px] inline-flex text-xs leading-4 font-extrabold rounded-md bg-red-100 text-red-800 border border-red-200 uppercase">{{ $item['status'] }}</span>
                                @elseif($item['color'] === 'yellow')
                                    <span class="px-1.5 py-1 text-[11px] inline-flex text-xs leading-4 font-bold rounded-md bg-yellow-100 text-yellow-800 border border-yellow-200 uppercase">{{ $item['status'] }}</span>
                                @else
                                    <span class="px-1.5 py-1 text-[11px] inline-flex text-xs leading-4 font-bold rounded-md bg-green-100 text-green-800 border border-green-200 uppercase">{{ $item['status'] }}</span>
                                @endif
                            </td>
                            <td class="px-2 py-1.5 text-sm text-gray-700">
                                <span class="{{ $item['color'] === 'red' ? 'font-bold text-red-600' : ($item['color'] === 'yellow' ? 'text-yellow-700' : 'text-gray-500') }}">
                                    {{ $item['warning'] }}
                                </span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                @if($item['priority'] === 'CAO')
                                    <span class="text-xs font-black text-red-600 uppercase">🔥 CAO</span>
                                @elseif($item['priority'] === 'TRUNG BÌNH')
                                    <span class="text-xs font-bold text-yellow-600 uppercase">⚡ TRUNG BÌNH</span>
                                @else
                                    <span class="text-xs font-semibold text-gray-500 uppercase">THẤP</span>
                                @endif
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center text-sm font-medium">
                                @if($item['status'] === 'ĐẾN HẠN' || $item['status'] === 'SẮP ĐẾN')
                                    <a href="{{ route('warehouse.maintenance-plans') }}?create=1&asset={{ $item['asset_id'] }}" class="text-white bg-indigo-600 hover:bg-indigo-700 px-1.5 py-1 text-[11px].5 rounded-md shadow-sm transition text-xs font-bold inline-block">Lên Kế Hoạch</a>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-6 py-12 text-center text-gray-500">
                                <p class="mb-2 text-3xl">📭</p>
                                <p>Không có dữ liệu theo dõi bảo dưỡng.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $trackingList->links() }}
        </div>
    </div>
</div>
