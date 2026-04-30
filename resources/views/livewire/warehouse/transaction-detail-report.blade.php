<div>
    <div class="bg-white rounded-xl shadow-sm border p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Từ ngày</label>
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Đến ngày</label>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            <div class="flex-1">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Tìm sản phẩm</label>
                <input type="text" wire:model.live.debounce.300ms="filterProduct" placeholder="Mã hoặc tên SP..."
                       class="w-full rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            
            <div class="flex items-end gap-2 mb-0.5">
                <div class="no-print">
                    <select wire:model.live="filterType" class="rounded-lg border-gray-200 shadow-sm text-xs font-bold focus:ring-indigo-500 py-2">
                        <option value="">-- Loại --</option>
                        <option value="import">Nhập kho</option>
                        <option value="export">Xuất kho</option>
                        <option value="adjust">Điều chỉnh</option>
                    </select>
                </div>
                <button type="button" wire:click="exportExcel" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black transition shadow-sm cursor-pointer">
                    <span wire:loading.remove wire:target="exportExcel" class="text-sm">📊</span>
                    <span wire:loading wire:target="exportExcel" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    Excel
                </button>
                <button type="button" onclick="window.print()" class="flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-black text-white rounded-lg text-xs font-black transition shadow-sm cursor-pointer">
                    <span class="text-sm">📄</span> IN BÁO CÁO
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-bold text-gray-700">Chi tiết giao dịch</h3>
                @if(count($selectedIds) > 0)
                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-bold animate-pulse">
                    Đã chọn {{ count($selectedIds) }} mục
                </span>
                @endif
            </div>
            <span class="text-[10px] text-gray-400">Trang {{ $transactions->currentPage() }} / {{ $transactions->lastPage() }}</span>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-4 py-3 text-center w-10">
                        <input type="checkbox" 
                               wire:click="toggleSelectAll([{{ implode(',', $transactions->pluck('id')->toArray()) }}])"
                               {{ count(array_intersect($transactions->pluck('id')->map(fn($id) => (string)$id)->toArray(), $selectedIds)) === count($transactions) && count($transactions) > 0 ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Thời gian</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Sản phẩm</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Loại</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Số lượng</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Mã tài sản</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Người liên hệ</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Người thực hiện</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Ghi chú</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $tx)
                @php
                    $typeColors = [
                        'import' => 'bg-green-50 text-green-700 border-green-100',
                        'export' => 'bg-amber-50 text-amber-700 border-amber-100',
                        'adjust' => 'bg-blue-50 text-blue-700 border-blue-100',
                        'reserve' => 'bg-purple-50 text-purple-700 border-purple-100',
                    ];
                    $typeLabels = [
                        'import' => '📥 Nhập',
                        'export' => '📤 Xuất',
                        'adjust' => '⚙️ Đ/chỉnh',
                        'reserve' => '🔒 Giữ',
                    ];
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-center">
                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $tx->id }}" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-bold text-gray-800">
                            <span class="text-indigo-600">[{{ $tx->product->code ?? 'N/A' }}]</span> 
                            {{ $tx->product->name ?? '' }}
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $typeColors[$tx->type] ?? 'bg-gray-50 text-gray-600' }}">
                            {{ $typeLabels[$tx->type] ?? $tx->type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-sm font-black {{ $tx->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tx->quantity >= 0 ? '+' : '' }}{{ number_format($tx->quantity) }}
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-slate-700">
                        {{ ($tx->reference && isset($tx->reference->asset_code)) ? $tx->reference->asset_code : '-' }}
                    </td>
                    <td class="px-4 py-3 text-xs font-medium text-slate-600">
                        {{ ($tx->reference && isset($tx->reference->receiver_name)) ? $tx->reference->receiver_name : '-' }}
                    </td>
                    <td class="px-4 py-3 text-xs font-medium text-gray-600 italic">👤 {{ $tx->creator->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-[10px] text-gray-400 leading-tight">{{ \Illuminate\Support\Str::limit($tx->note, 100) }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400 italic">Dữ liệu trống trong khoảng thời gian này...</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
