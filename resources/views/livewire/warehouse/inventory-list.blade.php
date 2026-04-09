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

            /* Switch layouts based on printing mode */
            body.printing-check-sheet .report-layout { display: none !important; }
            body:not(.printing-check-sheet) .check-sheet-layout { display: none !important; }
        }
        .print-only { display: none; }
        .check-sheet-layout { display: none; }
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
                <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    In danh sách ({{ count($selectedItems) }})
                </button>
                <button onclick="printInventoryCheck()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    In phiếu kiểm kê
                </button>
            @else
                <button disabled title="Vui lòng chọn sản phẩm để in" class="bg-gray-200 text-gray-400 cursor-not-allowed px-4 py-2 rounded-lg flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    In danh sách
                </button>
                <button disabled class="bg-gray-200 text-gray-400 cursor-not-allowed px-4 py-2 rounded-lg flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    In phiếu kiểm kê
                </button>
            @endif
        </div>
    </div>

    <div class="print-only report-layout text-center mb-6">
        <h1 class="text-2xl font-bold uppercase">Báo cáo tồn kho chi tiết</h1>
        <p class="text-sm text-gray-600">Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Layout in phiếu kiểm kê -->
    <div class="print-only check-sheet-layout mb-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h2 class="text-lg font-bold">CÔNG TY TNHH PHÁT TRIỂN CÔNG NGHỆ</h2>
                <p class="text-xs">Bộ phận: Kho vận</p>
            </div>
            <div class="text-right">
                <p class="text-xs italic">Mẫu số: 01-KK/KHO</p>
                <p class="text-xs">Ngày lập: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-extrabold uppercase tracking-widest">Phiếu kiểm kê kho</h1>
            <p class="text-sm italic">Thời điểm kiểm kê: ..... giờ ..... ngày ..... tháng ..... năm 202...</p>
        </div>

        <table class="w-full border-collapse border border-gray-400 mb-8">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-400 px-2 py-1 text-[10px] w-8">STT</th>
                    <th class="border border-gray-400 px-2 py-1 text-[10px] w-24">Mã SP</th>
                    <th class="border border-gray-400 px-2 py-1 text-[10px]">Tên SP / Quy cách</th>
                    <th class="border border-gray-400 px-2 py-1 text-[10px] w-20">Số lô</th>
                    <th class="border border-gray-400 px-2 py-1 text-[10px] w-16">Hạn dùng</th>
                    <th class="border border-gray-400 px-2 py-1 text-[10px] w-12">ĐVT</th>
                    <th class="border border-gray-400 px-2 py-1 text-[10px] w-16">Vị trí</th>
                    <th class="border border-gray-400 px-2 py-1 text-[10px] w-16">Tồn sổ</th>
                    <th class="border border-gray-400 px-2 py-1 text-[10px] w-16">Thực tế</th>
                    <th class="border border-gray-400 px-2 py-1 text-[10px] w-24">Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                @php $count = 1; @endphp
                @foreach($inventories as $inv)
                    @if(in_array($inv->id, $selectedItems))
                    <tr>
                        <td class="border border-gray-400 px-2 py-2 text-[10px] text-center">{{ $count++ }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-[10px] font-mono">{{ $inv->product_code }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-[10px]">{{ $inv->product_name }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-[10px] text-center">{{ $inv->batch_number ?? '-' }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-[10px] text-center">{{ $inv->expiry_date ? \Carbon\Carbon::parse($inv->expiry_date)->format('d/m/y') : '-' }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-[10px] text-center">{{ $inv->unit }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-[10px] text-center">{{ $inv->warehouse_location ?? '-' }}</td>
                        <td class="border border-gray-400 px-2 py-2 text-[10px] text-center font-bold text-gray-400 italic">({{ number_format($inv->quantity) }})</td>
                        <td class="border border-gray-400 px-2 py-2 text-[10px]"></td>
                        <td class="border border-gray-400 px-2 py-2 text-[10px]"></td>
                    </tr>
                    @endif
                @endforeach
                @for($i = 0; $i < 3; $i++)
                    <tr>
                        <td class="border border-gray-400 px-2 py-3"></td>
                        <td class="border border-gray-400 px-2 py-3"></td>
                        <td class="border border-gray-400 px-2 py-3"></td>
                        <td class="border border-gray-400 px-2 py-3"></td>
                        <td class="border border-gray-400 px-2 py-3"></td>
                        <td class="border border-gray-400 px-2 py-3"></td>
                        <td class="border border-gray-400 px-2 py-3"></td>
                        <td class="border border-gray-400 px-2 py-3"></td>
                        <td class="border border-gray-400 px-2 py-3"></td>
                        <td class="border border-gray-400 px-2 py-3"></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="grid grid-cols-3 gap-4 mt-12 text-center text-xs">
            <div>
                <p class="font-bold">NGƯỜI LẬP PHIẾU</p>
                <p class="italic text-[10px] mb-12">(Ký, họ tên)</p>
                <p class="mt-12">................................</p>
            </div>
            <div>
                <p class="font-bold">THỦ KHO</p>
                <p class="italic text-[10px] mb-12">(Ký, họ tên)</p>
                <p class="mt-12">................................</p>
            </div>
            <div>
                <p class="font-bold">BAN KIỂM KÊ</p>
                <p class="italic text-[10px] mb-12">(Ký, họ tên)</p>
                <p class="mt-12">................................</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden report-layout">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll([{{ $inventories->pluck('id')->implode(',') }}])" 
                               {{ count($selectedItems) > 0 && count($selectedItems) === count($inventories->pluck('id')) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </th>
                    <th wire:click="sortBy('products.code')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 italic">Mã SP</th>
                    <th wire:click="sortBy('products.name')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Tên sản phẩm</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hãng SX</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Số lô</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hạn dùng</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">ĐVT</th>
                    <th wire:click="sortBy('inventories.quantity')" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Tồn kho</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vị trí</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
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
                        <td class="px-4 py-3 text-sm font-mono text-indigo-600">{{ $inv->product_code }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $inv->product_name }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->brand ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center font-mono text-gray-600">{{ $inv->batch_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500 italic">{{ $inv->expiry_date ? \Carbon\Carbon::parse($inv->expiry_date)->format('d/m/y') : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->unit }}</td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-indigo-700">{{ number_format($inv->quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->warehouse_location ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium status-badge {{ $statusColor }}">
                                {{ $statusIcon }} {{ $statusText }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">Chưa có dữ liệu tồn kho</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function printInventoryCheck() {
            document.body.classList.add('printing-check-sheet');
            window.print();
            // Xóa class ngay sau khi hộp thoại in đóng lại
            window.onafterprint = function() {
                document.body.classList.remove('printing-check-sheet');
            };
            // Một số trình duyệt không hỗ trợ onafterprint ổn định, 
            // dùng timeout làm dự phòng nếu cần, nhưng ClassList sẽ bị xóa khi render lại bởi Livewire nếu state thay đổi.
            setTimeout(() => {
                document.body.classList.remove('printing-check-sheet');
            }, 500);
        }
    </script>
</div>
