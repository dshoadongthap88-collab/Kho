<div>
    <div class="mb-4 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex gap-3 items-center">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm theo tên/mã sản phẩm..."
                   class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-72">
            <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 shadow-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="sufficient">🟢 Đủ hàng</option>
                <option value="warning">🟡 Cảnh báo</option>
                <option value="critical">🔴 Thiếu hàng</option>
            </select>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('warehouse.stock-in') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">+ Nhập kho</a>
            <a href="{{ route('warehouse.stock-out') }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">- Xuất kho</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th wire:click="sortBy('products.code')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Mã SP</th>
                    <th wire:click="sortBy('products.name')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Tên sản phẩm</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">ĐVT</th>
                    <th wire:click="sortBy('inventories.quantity')" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Tồn kho</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Đã giữ</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Khả dụng</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tồn tối thiểu</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vị trí</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inventories as $inv)
                    @php
                        $available = $inv->quantity - $inv->reserved_quantity;
                        if ($available < $inv->min_stock) {
                            $statusColor = 'bg-red-100 text-red-800';
                            $statusText = 'Thiếu hàng';
                            $statusIcon = '🔴';
                        } elseif ($available < $inv->min_stock * 1.5) {
                            $statusColor = 'bg-yellow-100 text-yellow-800';
                            $statusText = 'Cảnh báo';
                            $statusIcon = '🟡';
                        } else {
                            $statusColor = 'bg-green-100 text-green-800';
                            $statusText = 'Đủ hàng';
                            $statusIcon = '🟢';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm font-mono">{{ $inv->product_code }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $inv->product_name }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->unit }}</td>
                        <td class="px-4 py-3 text-sm text-center font-semibold">{{ number_format($inv->quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-center text-orange-600">{{ number_format($inv->reserved_quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-center font-bold {{ $available < $inv->min_stock ? 'text-red-600' : 'text-green-600' }}">{{ number_format($available) }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ number_format($inv->min_stock) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $statusIcon }} {{ $statusText }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->warehouse_location ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">Chưa có dữ liệu tồn kho</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $inventories->links() }}</div>
</div>
