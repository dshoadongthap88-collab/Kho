<div>
    <!-- Header & Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm mã/tên vật tư hoặc nội dung..." class="w-full md:w-80 rounded-lg border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
            <select wire:model.live="statusFilter" class="w-full md:w-48 rounded-lg border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pending">Đề xuất (Chờ duyệt)</option>
                <option value="ordered">Đã đặt hàng</option>
                <option value="unreceived">Chưa giao</option>
                <option value="partial">Giao thiếu</option>
                <option value="completed">Đủ hàng (Hoàn thành)</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Thời Gian</th>
                        <th class="px-4 py-3">Mã & Tên Vật Tư</th>
                        <th class="px-4 py-3 text-right">SL Đề Xuất</th>
                        <th class="px-4 py-3 text-center">Trạng Thái Mới</th>
                        <th class="px-4 py-3 text-right">SL Đã Giao Mới</th>
                        <th class="px-4 py-3">Ghi Chú & Nguồn Thay Đổi</th>
                        <th class="px-4 py-3">Người Thực Hiện</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($histories as $history)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $history->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                @if($history->purchasePlan)
                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs mr-1">{{ $history->purchasePlan->product->code }}</span>
                                    {{ $history->purchasePlan->product->name }}
                                @else
                                    <span class="text-rose-500 italic">Dữ liệu đã bị xóa</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-sky-600">
                                {{ $history->purchasePlan ? number_format($history->purchasePlan->proposed_quantity, 0) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($history->new_status === 'pending')
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">Đề xuất</span>
                                @elseif($history->new_status === 'ordered')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-bold">Đã đặt</span>
                                @elseif($history->new_status === 'unreceived')
                                    <span class="px-2 py-1 bg-rose-100 text-rose-600 rounded-full text-xs font-bold">Chưa giao</span>
                                @elseif($history->new_status === 'partial')
                                    <span class="px-2 py-1 bg-amber-100 text-amber-600 rounded-full text-xs font-bold">Giao thiếu</span>
                                @elseif($history->new_status === 'completed')
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-600 rounded-full text-xs font-bold">Đủ hàng</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600">
                                {{ number_format($history->new_quantity, 0) }}
                                @if($history->old_quantity !== $history->new_quantity)
                                    <span class="text-xs text-slate-400 font-normal ml-1">(Từ: {{ number_format($history->old_quantity, 0) }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $history->notes }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-700">
                                {{ $history->user ? $history->user->name : 'Hệ thống tự động' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500 font-medium">Chưa có lịch sử thay đổi nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-200">
            {{ $histories->links() }}
        </div>
    </div>
</div>
