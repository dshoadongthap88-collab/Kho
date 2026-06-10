<div class="p-4 max-w-4xl mx-auto">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-lg flex items-center gap-2">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-lg flex items-center gap-2">
            ❌ {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        {{-- Form Header --}}
        <div class="bg-indigo-700 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-white text-lg font-bold flex items-center gap-2">
                    🚚 Tạo phiếu chuyển kho
                </h2>
                <p class="text-indigo-200 text-sm mt-0.5">
                    Chuyển vật tư/sản phẩm từ
                    <strong class="text-white">Dự án {{ session('current_house', 1) == 2 ? 'Hậu Nghĩa' : (session('current_house', 1) == 3 ? 'Cần Giờ' : 'Hóc Môn') }}</strong>
                    sang dự án khác
                </p>
            </div>
            <a href="{{ route('warehouse.stock-transfer.index') }}"
               class="text-indigo-200 hover:text-white text-sm transition">
                ← Quay lại danh sách
            </a>
        </div>

        <div class="p-6 space-y-6">
            {{-- Thông tin phiếu --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Chọn nhà đích --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        🏠 Chuyển đến Dự án <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="to_house"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($available_houses as $house)
                            <option value="{{ $house }}">{{ $house == 1 ? 'Dự án Hóc Môn' : ($house == 2 ? 'Dự án Hậu Nghĩa' : ($house == 3 ? 'Dự án Cần Giờ' : 'Dự án Số 4')) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Ghi chú --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        📝 Ghi chú
                    </label>
                    <input wire:model="note" type="text"
                        placeholder="Lý do chuyển kho..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            {{-- Bảng danh sách vật tư --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-semibold text-gray-700">📦 Danh sách vật tư / sản phẩm</label>
                    <button wire:click="addItem" type="button"
                        class="flex items-center gap-1 text-sm px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        ➕ Thêm dòng
                    </button>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden relative">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-2 text-left w-12">#</th>
                                <th class="px-4 py-2 text-left">Mã vật tư / sản phẩm</th>
                                <th class="px-4 py-2 text-center w-36">Số lượng</th>
                                <th class="px-4 py-2 text-center w-16"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $index => $item)
                                <tr wire:key="item-{{ $index }}">
                                    <td class="px-4 py-2 text-gray-400 text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 relative">
                                        <div class="relative">
                                            <input type="text"
                                                wire:model.live.debounce.300ms="items.{{ $index }}.product_code"
                                                placeholder="Nhập mã hoặc tên vật tư..."
                                                class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 uppercase">

                                            @if(isset($searchResults) && count($searchResults) > 0 && $activeIndex === $index)
                                                <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto mt-1">
                                                    @foreach($searchResults as $res)
                                                        <div wire:click="selectProduct({{ $index }}, '{{ $res['code'] }}')"
                                                             class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm border-b border-gray-50 last:border-0 flex justify-between items-center">
                                                            <span><strong>{{ $res['code'] }}</strong> - {{ $res['label'] }}</span>
                                                            <span class="text-xs text-gray-400">{{ $res['unit'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        @error("items.{$index}.product_code")
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number"
                                            wire:model="items.{{ $index }}.quantity"
                                            min="0.01" step="0.01"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                        @error("items.{$index}.quantity")
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if(count($items) > 1)
                                            <button wire:click="removeItem({{ $index }})" type="button"
                                                class="text-red-400 hover:text-red-600 transition text-lg leading-none">
                                                ×
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-400 mt-2">
                    💡 Nhập đúng <strong>Mã vật tư/sản phẩm</strong>. Nếu nhà nhận chưa có mã này, hệ thống sẽ tự động tạo mới.
                </p>
            </div>

            {{-- Tóm tắt --}}
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3 flex items-center gap-3 text-sm text-indigo-800">
                <span class="text-2xl">🔄</span>
                <div>
                    <strong>Dự án {{ session('current_house', 1) == 2 ? 'Hậu Nghĩa' : (session('current_house', 1) == 3 ? 'Cần Giờ' : 'Hóc Môn') }}</strong>
                    → <strong>{{ $to_house == 1 ? 'Dự án Hóc Môn' : ($to_house == 2 ? 'Dự án Hậu Nghĩa' : ($to_house == 3 ? 'Dự án Cần Giờ' : 'Dự án Số 4')) }}</strong>
                    &nbsp;|&nbsp; {{ count($items) }} mặt hàng
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('warehouse.stock-transfer.index') }}"
                    class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition">
                    Hủy
                </a>
                <button wire:click="save" wire:loading.attr="disabled"
                    class="px-6 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 disabled:opacity-60">
                    <span wire:loading.remove>🚚 Hoàn tất chuyển kho</span>
                    <span wire:loading>⏳ Đang xử lý...</span>
                </button>
            </div>
        </div>
    </div>
</div>
