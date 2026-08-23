<div>
    <div class="bg-white rounded-xl shadow-sm border p-2 mb-6">
        <div class="filter-grid">
            <div class="filter-field">
                <label class="form-label">Từ ngày</label>
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            <div class="filter-field">
                <label class="form-label">Đến ngày</label>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            <div class="filter-field">
                <label class="form-label">Tìm sản phẩm</label>
                <input type="text" wire:model.live.debounce.300ms="filterProduct" placeholder="Mã hoặc tên SP..."
                       class="w-full rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
            </div>
            
            {{-- Hai o loc nay truoc day bi nhet chung vao cum nut nen khong thang
                 hang voi cac o loc khac. Tach ra thanh o loc rieng. --}}
            <div class="filter-field no-print">
                <label class="form-label" for="txn-type">Loại giao dịch</label>
                <select id="txn-type" wire:model.live="filterType" class="input-sm">
                    <option value="">Tất cả loại</option>
                    <option value="import">Nhập kho</option>
                    <option value="export">Xuất kho</option>
                    <option value="adjust">Điều chỉnh</option>
                    <option value="transfer">Chuyển kho</option>
                </select>
            </div>

            <div class="filter-field no-print">
                <label class="form-label" for="txn-app">Xuất app</label>
                <select id="txn-app" wire:model.live="filterExportedApp" class="input-sm">
                    <option value="">Tất cả</option>
                    <option value="1">Đã xuất app</option>
                    <option value="0">Chưa xuất app</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="button" wire:click="exportExcel" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black transition shadow-sm cursor-pointer">
                    <span wire:loading.remove wire:target="exportExcel" class="text-sm">📊</span>
                    <span wire:loading wire:target="exportExcel" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    Excel
                </button>
                <button type="button" wire:click="printSelected" class="flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-black text-white rounded-lg text-xs font-black transition shadow-sm cursor-pointer">
                    <span class="text-sm">📄</span> IN BÁO CÁO
                </button>
                @if(count($selectedIds) > 0)
                <button type="button" wire:click="deleteSelected" wire:confirm="Bạn có chắc chắn muốn xóa vĩnh viễn các giao dịch đã chọn khỏi hệ thống?" class="flex items-center gap-1.5 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-black transition shadow-sm cursor-pointer">
                    <span class="text-sm">🗑️</span> XÓA ĐÃ CHỌN
                </button>
                @endif
            </div>
        </div>
        
        @if (session()->has('error'))
            <div class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-xs font-bold border border-red-200">
                {{ session('error') }}
            </div>
        @endif
        @if (session()->has('message'))
            <div class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg text-xs font-bold border border-green-200">
                {{ session('message') }}
            </div>
        @endif
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
                    <th class="px-2 py-2 text-center w-10">
                        <input type="checkbox" 
                               wire:click="toggleSelectAll([{{ implode(',', $transactions->pluck('id')->toArray()) }}])"
                               {{ count(array_intersect($transactions->pluck('id')->map(fn($id) => (string)$id)->toArray(), $selectedIds)) === count($transactions) && count($transactions) > 0 ? 'checked' : '' }}
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Thời gian</th>
                    <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Sản phẩm</th>
                    <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Loại</th>
                    <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Số lượng</th>
                    <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Mã tài sản</th>
                    <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Người liên hệ</th>
                    <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Người thực hiện</th>
                    <th class="px-2 py-2 text-left text-[10px] font-bold text-gray-400 uppercase">Ghi chú</th>
                    <th class="px-2 py-2 text-center text-[10px] font-bold text-gray-400 uppercase">Xuất app</th>
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
                        'transfer_in' => 'bg-teal-50 text-teal-700 border-teal-100',
                        'transfer_out' => 'bg-orange-50 text-orange-700 border-orange-100',
                    ];
                    $typeLabels = [
                        'import' => '📥 Nhập',
                        'export' => '📤 Xuất',
                        'adjust' => '⚙️ Đ/chỉnh',
                        'reserve' => '🔒 Giữ',
                        'transfer_in' => '🚚 Nhập chuyển',
                        'transfer_out' => '🚚 Xuất chuyển',
                    ];
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-2 py-1.5 text-center">
                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $tx->id }}" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                    </td>
                    <td class="px-2 py-1.5 text-xs text-gray-400 font-mono">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-2 py-1.5">
                        <div class="text-xs font-bold text-gray-800">
                            <span class="text-indigo-600">[{{ $tx->product->code ?? 'N/A' }}]</span> 
                            {{ $tx->product->name ?? '' }}
                        </div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $typeColors[$tx->type] ?? 'bg-gray-50 text-gray-600' }}">
                            {{ $typeLabels[$tx->type] ?? $tx->type }}
                        </span>
                    </td>
                    <td class="px-2 py-1.5 text-center text-sm font-black {{ $tx->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tx->quantity >= 0 ? '+' : '' }}{{ number_format($tx->quantity) }}
                    </td>
                    <td class="px-2 py-1.5 text-xs font-bold text-slate-700">
                        {{ ($tx->reference && isset($tx->reference->asset_code)) ? $tx->reference->asset_code : '-' }}
                    </td>
                    <td class="px-2 py-1.5 text-xs font-medium text-slate-600">
                        {{ ($tx->reference && isset($tx->reference->receiver_name)) ? $tx->reference->receiver_name : '-' }}
                    </td>
                    <td class="px-2 py-1.5 text-xs font-medium text-gray-600 italic">👤 {{ $tx->creator->name ?? '-' }}</td>
                    <td class="px-2 py-1.5 text-[10px] text-gray-400 leading-tight">{{ \Illuminate\Support\Str::limit($tx->note, 100) }}</td>
                    <td class="px-2 py-1.5 text-center">
                        <button type="button" wire:click="toggleExportedApp({{ $tx->id }})" class="px-2 py-1 rounded text-[10px] font-bold transition-colors cursor-pointer {{ $tx->is_exported_app ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                            {{ $tx->is_exported_app ? 'Đã xuất app' : 'Chưa xuất app' }}
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400 italic">Dữ liệu trống trong khoảng thời gian này...</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
