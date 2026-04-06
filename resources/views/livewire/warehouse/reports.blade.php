<div>
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-sm text-green-600 mb-1">Tổng nhập</p>
            <p class="text-2xl font-bold text-green-700">{{ number_format($summary->total_import ?? 0) }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-center">
            <p class="text-sm text-orange-600 mb-1">Tổng xuất</p>
            <p class="text-2xl font-bold text-orange-700">{{ number_format($summary->total_export ?? 0) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <p class="text-sm text-blue-600 mb-1">Điều chỉnh</p>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($summary->total_adjust ?? 0) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Từ ngày</label>
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Đến ngày</label>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border-gray-300 shadow-sm text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Loại</label>
                <select wire:model.live="filterType" class="rounded-lg border-gray-300 shadow-sm text-sm">
                    <option value="">Tất cả</option>
                    <option value="import">Nhập kho</option>
                    <option value="export">Xuất kho</option>
                    <option value="adjust">Điều chỉnh</option>
                    <option value="reserve">Giữ hàng</option>
                    <option value="release">Giải phóng</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sản phẩm</label>
                <input type="text" wire:model.live.debounce.300ms="filterProduct" placeholder="Tìm SP..."
                       class="rounded-lg border-gray-300 shadow-sm text-sm w-48">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thời gian</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sản phẩm</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Loại</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Số lượng</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nguồn</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Người thực hiện</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ghi chú</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transactions as $tx)
                @php
                    $typeColors = [
                        'import' => 'bg-green-100 text-green-800',
                        'export' => 'bg-orange-100 text-orange-800',
                        'adjust' => 'bg-blue-100 text-blue-800',
                        'reserve' => 'bg-purple-100 text-purple-800',
                        'release' => 'bg-gray-100 text-gray-800',
                    ];
                    $typeLabels = [
                        'import' => 'Nhập',
                        'export' => 'Xuất',
                        'adjust' => 'Điều chỉnh',
                        'reserve' => 'Giữ hàng',
                        'release' => 'Giải phóng',
                    ];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2 text-sm">{{ $tx->product->code ?? '' }} - {{ $tx->product->name ?? '' }}</td>
                    <td class="px-4 py-2 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeColors[$tx->type] ?? '' }}">
                            {{ $typeLabels[$tx->type] ?? $tx->type }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-center text-sm font-semibold {{ $tx->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $tx->quantity >= 0 ? '+' : '' }}{{ number_format($tx->quantity) }}
                    </td>
                    <td class="px-4 py-2 text-xs text-gray-500">{{ $tx->reference_type }}</td>
                    <td class="px-4 py-2 text-sm">{{ $tx->creator->name ?? '-' }}</td>
                    <td class="px-4 py-2 text-xs text-gray-400">{{ Str::limit($tx->note, 40) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Không có giao dịch nào</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
