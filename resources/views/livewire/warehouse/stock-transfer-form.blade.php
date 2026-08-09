<div class="p-2 max-w-5xl mx-auto">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-2 bg-green-100 border border-green-400 text-green-800 rounded-lg flex items-center gap-2">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-2 bg-red-100 border border-red-400 text-red-800 rounded-lg flex items-center gap-2">
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
                    Tạo phiếu chuyển và chờ chi nhánh nhận xác nhận
                </p>
            </div>
            <a href="{{ route('warehouse.stock-transfer.index') }}"
               class="text-indigo-200 hover:text-white text-sm transition">
                ← Quay lại danh sách
            </a>
        </div>

        <div class="p-2 space-y-6">
            {{-- Thông tin phiếu & Nhân sự --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2">
                {{-- Chọn nhà đích --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        🏠 Chuyển đến Chi nhánh / Dự án <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="to_project_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach($available_projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }} ({{ $project->code }})</option>
                        @endforeach
                    </select>
                    @error('to_project_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Ghi chú --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        📝 Ghi chú chung
                    </label>
                    <input wire:model="note" type="text"
                        placeholder="Lý do chuyển kho..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>

                {{-- Người nhận --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        👤 Người nhận <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="receiver_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="">-- Chọn người nhận --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->phone }}</option>
                        @endforeach
                    </select>
                    @error('receiver_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- SĐT Người nhận --}}
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        📞 SĐT Người nhận
                    </label>
                    <input wire:model="receiver_phone" type="text"
                        placeholder="Số điện thoại người nhận"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>

            {{-- Bảng danh sách vật tư --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="text-sm font-semibold text-gray-700">📦 Danh sách vật tư / sản phẩm</label>
                    <div class="w-1/2 relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">🔍</span>
                        <input type="text" wire:model.live.debounce.300ms="searchQuery" 
                               placeholder="Tìm kiếm mã hoặc tên vật tư để thêm vào danh sách..."
                               class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-all shadow-sm">
                        @if(!empty($searchResults))
                            <div class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto mt-1">
                                @foreach($searchResults as $res)
                                    <div wire:click="addSelectedProduct('{{ $res['code'] }}')"
                                         class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm border-b border-gray-50 last:border-0 flex justify-between items-center transition-colors">
                                        <span><strong class="text-indigo-700">{{ $res['code'] }}</strong> - {{ $res['name'] }}</span>
                                        <div class="text-right">
                                            <span class="text-xs font-semibold {{ $res['stock'] > 0 ? 'text-green-600' : 'text-red-500' }} block">
                                                Tồn: {{ $res['stock'] }} {{ $res['unit'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <button wire:click="addItem" type="button"
                        class="flex items-center gap-1 text-sm px-3 py-2 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition shadow-sm border border-gray-200">
                        ➕ Thêm dòng trống
                    </button>
                </div>

                <div class="border border-gray-200 rounded-lg overflow-hidden relative">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-3 py-2 text-left w-10">#</th>
                                <th class="px-3 py-2 text-left">Mã & Tên vật tư</th>
                                <th class="px-3 py-2 text-center w-28">Số lượng tồn</th>
                                <th class="px-3 py-2 text-center w-32">Số lượng xuất</th>
                                <th class="px-3 py-2 text-center w-32">Vị trí</th>
                                <th class="px-3 py-2 text-left w-48">Ghi chú mặt hàng</th>
                                <th class="px-3 py-2 text-center w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $index => $item)
                                <tr wire:key="item-{{ $index }}">
                                    <td class="px-3 py-2 text-gray-400 text-center">{{ $loop->iteration }}</td>
                                    <td class="px-3 py-2">
                                        <input list="products-list" type="text"
                                            wire:model.live="items.{{ $index }}.product_code"
                                            placeholder="Chọn vật tư..."
                                            class="w-full border border-gray-300 rounded px-1.5 py-1 text-[11px].5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-300 uppercase">
                                        @error("items.{$index}.product_code")
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-3 py-2 text-center font-bold text-gray-700">
                                        {{ $item['stock'] ?? 0 }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number"
                                            wire:model="items.{{ $index }}.quantity"
                                            min="0.01" step="0.01"
                                            class="w-full border border-gray-300 rounded px-1.5 py-1 text-[11px].5 text-xs text-center focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                        @error("items.{$index}.quantity")
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text"
                                            wire:model="items.{{ $index }}.location"
                                            placeholder="Kệ A..."
                                            class="w-full border border-gray-300 rounded px-1.5 py-1 text-[11px].5 text-xs text-center focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text"
                                            wire:model="items.{{ $index }}.note"
                                            placeholder="Ghi chú..."
                                            class="w-full border border-gray-300 rounded px-1.5 py-1 text-[11px].5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-300">
                                    </td>
                                    <td class="px-3 py-2 text-center">
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
                
                <datalist id="products-list">
                    @foreach($products as $product)
                        <option value="{{ $product->code }}">{{ $product->code }} - {{ $product->name }}</option>
                    @endforeach
                </datalist>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
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
