<div>
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; margin: 0; padding: 0; }
            .bg-white { box-shadow: none !important; border: none !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #ddd !important; padding: 8px !important; }
            .noprint-row { display: none !important; }
            .status-badge { border: 1px solid #ccc !important; }
        }
        .print-only { display: none; }
    </style>

    <div class="mb-4 flex flex-wrap gap-4 items-center justify-between no-print">
        <div class="flex flex-wrap gap-3 items-center">
            <!-- Tìm kiếm -->
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tên/Mã sản phẩm..."
                   class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-64">
            
            <!-- Bộ lọc Hãng SX -->
            <select wire:model.live="filterBrand" class="rounded-lg border-gray-300 shadow-sm">
                <option value="">Tất cả hãng</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand }}">{{ $brand }}</option>
                @endforeach
            </select>

            <!-- Bộ lọc Vị trí -->
            <input type="text" wire:model.live.debounce.300ms="filterLocation" placeholder="Vị trí..."
                   class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-32" list="locations_list">
            <datalist id="locations_list">
                @foreach($locations as $loc)
                    <option value="{{ $loc }}">
                @endforeach
            </datalist>

            <!-- Bộ lọc Trạng thái -->
            <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 shadow-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="sufficient">🟢 Đủ hàng</option>
                <option value="warning">🟡 Cảnh báo</option>
                <option value="critical">🔴 Thiếu hàng</option>
            </select>
        </div>

        <div class="flex gap-2">
            @if(count($selectedItems) > 0)
                <button onclick="window.print()" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    In danh sách ({{ count($selectedItems) }})
                </button>
            @else
                <button disabled title="Vui lòng chọn ít nhất 1 sản phẩm để in" class="bg-gray-300 text-gray-500 cursor-not-allowed px-6 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    In danh sách
                </button>
            @endif
        </div>
    </div>

    <div class="print-only text-center mb-6">
        <h1 class="text-2xl font-bold">DANH SÁCH TỒN KHO CHI TIẾT</h1>
        <p class="text-sm text-gray-600">Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll([{{ $inventories->pluck('id')->implode(',') }}])" 
                               {{ count($selectedItems) > 0 && count($selectedItems) === count($inventories->pluck('id')) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </th>
                    <th wire:click="sortBy('products.code')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Mã SP</th>
                    <th wire:click="sortBy('products.name')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Tên sản phẩm</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hãng SX</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">ĐVT</th>
                    <th wire:click="sortBy('inventories.quantity')" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Tồn kho</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Đã giữ</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Khả dụng</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Nhập tối thiểu</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vị trí</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inventories as $inv)
                    @php
                        $available = $inv->quantity - $inv->reserved_quantity;
                        $isSelected = in_array($inv->id, $selectedItems);
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
                    <tr class="hover:bg-gray-50 transition {{ $isSelected ? 'bg-indigo-50' : 'noprint-row' }}">
                        <td class="px-4 py-3 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedItems" value="{{ $inv->id }}"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3 text-sm font-mono">{{ $inv->product_code }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $inv->product_name }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->brand ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->unit }}</td>
                        <td class="px-4 py-3 text-sm text-center font-semibold">{{ number_format($inv->quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-center text-orange-600">{{ number_format($inv->reserved_quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-center font-bold {{ $available < $inv->min_stock ? 'text-red-600' : 'text-green-600' }}">{{ number_format($available) }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ number_format($inv->min_stock) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium status-badge {{ $statusColor }}">
                                {{ $statusIcon }} {{ $statusText }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->warehouse_location ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">Chưa có dữ liệu tồn kho</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 no-print">{{ $inventories->links() }}</div>
</div>
