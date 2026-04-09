<div>
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; margin: 0; padding: 0; color: black !important; }
            .bg-white { box-shadow: none !important; border: none !important; }
            table { width: 100% !important; border-collapse: collapse !important; margin-top: 10px; }
            th, td { border: 1px solid black !important; padding: 6px 4px !important; color: black !important; }
            
            /* Khu vực 1/3 trên */
            .print-header {
                height: 30vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                border-bottom: 2px solid black;
                margin-bottom: 20px;
            }

            /* Print-specific layout switching */
            body.printing-check-sheet .screen-layout { display: none !important; }
            body:not(.printing-check-sheet) .check-sheet-layout { display: none !important; }
        }
        .print-only { display: none; }
        .check-sheet-layout { display: none; }
    </style>

    <div class="screen-layout">
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4 no-print">
            <h2 class="text-xl font-bold">📋 Kiểm kê kho</h2>
            <div class="flex gap-2">
                @if(count($selectedItems) > 0)
                    <button onclick="printInventoryCheck()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2 text-sm shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        In phiếu kiểm kê ({{ count($selectedItems) }})
                    </button>
                @else
                    <button disabled title="Vui lòng chọn sản phẩm để in" class="bg-gray-100 text-gray-400 cursor-not-allowed px-4 py-2 rounded-lg flex items-center gap-2 text-sm border border-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        In phiếu kiểm kê
                    </button>
                @endif
            </div>
        </div>
        <p class="text-gray-500 mb-6 text-sm no-print">Nhập số lượng thực tế đếm được. Hệ thống sẽ tự động tính chênh lệch và điều chỉnh tồn kho.</p>

        <table class="w-full mb-4">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', array_keys($countItems)) }}])" 
                               {{ count($selectedItems) > 0 && count($selectedItems) === count($countItems) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </th>
                    <th class="px-3 py-2 text-left text-sm">Mã SP</th>
                    <th class="px-3 py-2 text-left text-sm">Tên sản phẩm</th>
                    <th class="px-3 py-2 text-center text-sm">Tồn hệ thống</th>
                    <th class="px-3 py-2 text-center text-sm">Tồn thực tế</th>
                    <th class="px-3 py-2 text-center text-sm">Chênh lệch</th>
                </tr>
            </thead>
            <tbody>
                @foreach($countItems as $index => $item)
                <tr class="border-b {{ $item['difference'] != 0 ? 'bg-yellow-50' : '' }} {{ in_array($index, $selectedItems) ? 'bg-indigo-50/50' : '' }}">
                    <td class="px-3 py-2 text-center no-print">
                        <input type="checkbox" wire:model.live="selectedItems" value="{{ $index }}"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </td>
                    <td class="px-3 py-2 text-sm font-mono">{{ $item['product_code'] }}</td>
                    <td class="px-3 py-2 text-sm">{{ $item['product_name'] }}</td>
                    <td class="px-3 py-2 text-center text-sm text-gray-500">{{ number_format($item['system_quantity']) }}</td>
                    <td class="px-3 py-2 text-center">
                        <input type="number" wire:model.lazy="countItems.{{ $index }}.actual_quantity"
                               wire:change="updateDifference({{ $index }})"
                               class="w-24 text-center rounded border-gray-300 shadow-sm text-sm">
                    </td>
                    <td class="px-3 py-2 text-center text-sm font-bold
                        {{ $item['difference'] > 0 ? 'text-green-600' : ($item['difference'] < 0 ? 'text-red-600' : 'text-gray-400') }}">
                        {{ $item['difference'] > 0 ? '+' : '' }}{{ $item['difference'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú kiểm kê</label>
            <textarea wire:model="note" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('warehouse.inventory') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</a>
            <button wire:click="save" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                Xác nhận kiểm kê
            </button>
        </div>
    </div>

    </div>

    <!-- Layout in phiếu kiểm kê (Ẩn trên màn hình) -->
    <div class="print-only check-sheet-layout px-8">
        <!-- 1/3 TRÊN -->
        <div class="print-header text-center">
            <h1 class="text-4xl font-black mb-4 tracking-tighter">KIỂM KÊ KHO</h1>
            <p class="text-lg">Ngày ...... tháng ...... năm 20......</p>
            <div class="mt-4 text-sm italic">
                (Kèm theo Chứng từ số: .................... / Ngày: ...../...../20.....)
            </div>
        </div>

        <!-- 2/3 DƯỚI (Dữ liệu) -->
        <div class="min-h-[50vh]">
            <table class="w-full border-collapse border-2 border-black">
                <thead>
                    <tr class="bg-gray-100 font-bold uppercase text-[10px]">
                        <th class="border-2 border-black w-6">STT</th>
                        <th class="border-2 border-black">Tên sản phẩm</th>
                        <th class="border-2 border-black w-20">Mã SP</th>
                        <th class="border-2 border-black w-16">Số lô</th>
                        <th class="border-2 border-black w-16">Hạn dùng</th>
                        <th class="border-2 border-black w-14">SL Hệ thống</th>
                        <th class="border-2 border-black w-14">SL Thực tế</th>
                        <th class="border-2 border-black w-14">Chênh lệch</th>
                        <th class="border-2 border-black w-16">Vị trí</th>
                    </tr>
                </thead>
                <tbody>
                    @php $count = 1; @endphp
                    @foreach($countItems as $index => $item)
                        @if(in_array($index, $selectedItems))
                        <tr class="text-[10px]">
                            <td class="text-center font-bold">{{ $count++ }}</td>
                            <td class="px-1 leading-tight">
                                <div class="font-bold">{{ $item['product_name'] }}</div>
                                <div class="text-[9px] italic">ĐVT: {{ $item['unit'] }}</div>
                            </td>
                            <td class="text-center font-mono">{{ $item['product_code'] }}</td>
                            <td class="text-center">{{ $item['batch_number'] ?? '-' }}</td>
                            <td class="text-center">{{ $item['expiry_date'] ? \Carbon\Carbon::parse($item['expiry_date'])->format('d/m/y') : '-' }}</td>
                            <td class="text-center font-bold">{{ number_format($item['system_quantity']) }}</td>
                            <td class="text-center font-bold">{{ number_format($item['actual_quantity']) }}</td>
                            <td class="text-center font-bold {{ $item['difference'] != 0 ? 'text-red-600' : '' }}">
                                {{ $item['difference'] > 0 ? '+' : '' }}{{ $item['difference'] }}
                            </td>
                            <td class="text-center text-[9px]">{{ $item['location'] ?? '-' }}</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- CHỮ KÝ -->
        <div class="grid grid-cols-3 gap-4 mt-16 text-center text-sm font-bold uppercase">
            <div>
                <p>NHÂN VIÊN KIỂM KHO</p>
                <p class="text-[10px] font-normal italic lowercase mt-1">(Ký, ghi rõ họ tên)</p>
            </div>
            <div>
                <p>KẾ TOÁN KHO</p>
                <p class="text-[10px] font-normal italic lowercase mt-1">(Ký, ghi rõ họ tên)</p>
            </div>
            <div>
                <p>QUẢN LÝ KHO</p>
                <p class="text-[10px] font-normal italic lowercase mt-1">(Ký, ghi rõ họ tên)</p>
            </div>
        </div>
    </div>

    <script>
        function printInventoryCheck() {
            document.body.classList.add('printing-check-sheet');
            window.print();
            window.onafterprint = function() {
                document.body.classList.remove('printing-check-sheet');
            };
            setTimeout(() => {
                document.body.classList.remove('printing-check-sheet');
            }, 500);
        }
    </script>
</div>
