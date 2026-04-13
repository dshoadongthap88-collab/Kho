<div x-data x-on:trigger-print.window="setTimeout(() => window.print(), 300)">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            .bg-white { box-shadow: none !important; border: none !important; }
        }
    </style>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 no-print shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 no-print shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Khu vực thêm Thành phẩm dự kiến (Bên trái) -->
        <div class="md:col-span-1 border rounded-xl bg-white shadow-sm p-4 no-print">
            <h3 class="font-bold text-gray-800 mb-4 border-b pb-2">🎯 Mục tiêu Sản xuất</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Chọn loại Thành phẩm</label>
                    <select wire:model="newProductId" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Click để chọn --</option>
                        @foreach($hasBomProducts as $product)
                            <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('newProductId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng dự kiến</label>
                    <input type="number" wire:model="newQuantity" min="1" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('newQuantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <button wire:click="addTarget" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-md font-semibold transition shadow-sm">
                    ➕ Thêm vào Kế hoạch
                </button>
            </div>

            <!-- Danh sách Hàng thành phẩm đã thêm -->
            @if(count($targetProducts) > 0)
                <div class="mt-6 pt-4 border-t">
                    <h4 class="font-semibold text-sm text-gray-700 mb-2">Đang lên kế hoạch cho:</h4>
                    <ul class="space-y-2">
                        @foreach($targetProducts as $tgt)
                            <li class="flex justify-between items-center bg-indigo-50 px-3 py-2 rounded border border-indigo-100">
                                <div>
                                    <span class="block text-xs font-bold text-indigo-800">{{ $tgt['name'] }}</span>
                                    <span class="text-[10px] text-gray-500">SL: {{ $tgt['quantity'] }}</span>
                                </div>
                                <button wire:click="removeTarget('{{ $tgt['id'] }}')" class="text-red-500 hover:text-red-700 p-1">✕</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="mt-6 pt-4 border-t text-sm text-gray-400 text-center italic">
                    Chưa có sản phẩm nào được chọn
                </div>
            @endif
        </div>

        <!-- Khu vực kết quả Tính toán BOM (Bên phải) -->
        <div class="md:col-span-2">
            <div class="border rounded-xl bg-white shadow-sm p-4 h-full flex flex-col">
                <div class="flex justify-between items-end mb-4 border-b pb-2">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Phân tích Nhu cầu Nguyên vật liệu</h3>
                        <p class="text-xs text-gray-500">Hệ thống phân rã và gộp tự động từ Định mức tiêu hao (BOM)</p>
                    </div>
                    @if(!empty($materialNeeds))
                    <div class="flex gap-2 no-print">
                        <button onclick="window.print()" class="bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 px-3 py-1.5 rounded text-sm font-semibold flex items-center gap-1 shadow-sm transition">
                            🖨️ In Yêu Cầu
                        </button>
                        <button wire:confirm="Xác nhận chuyển dữ liệu hàng hóa ĐANG THIẾU sang trang Phiếu Đề Xuất Mua Hàng tự động?" wire:click="sendToPurchase" class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded text-sm font-semibold flex items-center gap-1 shadow-sm transition">
                            📤 Trình mua hàng
                        </button>
                    </div>
                    @endif
                </div>

                @if(!empty($materialNeeds))
                    <div class="flex-1 overflow-auto print:overflow-visible">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 uppercase text-[10px] font-bold">
                                    <th class="px-3 py-2 border-y">Mã NVL</th>
                                    <th class="px-3 py-2 border-y">Tên Nguyên Vật Liệu</th>
                                    <th class="px-3 py-2 border-y text-center">ĐVT</th>
                                    <th class="px-3 py-2 border-y text-right">Cần dùng</th>
                                    <th class="px-3 py-2 border-y text-right">Tồn kho</th>
                                    <th class="px-3 py-2 border-y text-center">Tình trạng</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($materialNeeds as $mat)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-3 py-2 font-mono text-xs text-gray-500">{{ $mat['code'] }}</td>
                                        <td class="px-3 py-2 font-semibold text-slate-800">{{ $mat['name'] }}</td>
                                        <td class="px-3 py-2 text-center text-xs bg-slate-50">{{ $mat['unit'] }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-indigo-600">{{ number_format($mat['required'], 2) }}</td>
                                        <td class="px-3 py-2 text-right">{{ number_format($mat['in_stock'], 2) }}</td>
                                        <td class="px-3 py-2 text-center">
                                            @if($mat['shortage'] > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold leading-4 bg-red-100 text-red-700 print:bg-transparent print:text-red-700">
                                                    THIẾU {{ number_format($mat['shortage'], 2) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold leading-4 bg-green-100 text-green-700 print:bg-transparent print:text-green-700">
                                                    ĐỦ HÀNG
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-gray-400 italic py-12 print:hidden">
                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <p>Kế hoạch sản xuất đang trống.</p>
                        <p class="text-xs">Vui lòng chọn Thành phẩm ở bên trái để tiến hành tính toán định mức.</p>
                    </div>
                @endif
            </div>
            <div class="no-print mt-2 text-xs italic text-gray-500">
                Lưu ý: Bấm <b>In Yêu Cầu</b> để tải bản in xác nhận Nhu Cầu NVL. Bấm <b>Trình mua hàng</b> phần mềm sẽ điều hướng những nguyên vật liệu <span class="text-red-500 font-bold">THIẾU</span> sang trang Phiếu Mua Hàng tự động để tiết kiệm thời gian nhập liệu lại.
            </div>
        </div>
    </div>
</div>
