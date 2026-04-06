<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">📤 Phiếu xuất kho</h2>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Khách hàng / Bộ phận nhận</label>
                <input type="text" wire:model="customer_name" class="w-full rounded-lg border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Loại xuất</label>
                <select wire:model="type" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="manual">Xuất thủ công</option>
                    <option value="sales">Bán hàng</option>
                    <option value="material_usage">Xuất NVL sản xuất</option>
                </select>
            </div>
        </div>

        <table class="w-full mb-4">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-left text-sm">Sản phẩm</th>
                    <th class="px-3 py-2 text-center text-sm w-32">Số lượng</th>
                    <th class="px-3 py-2 text-center text-sm w-20"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr class="border-b">
                    <td class="px-3 py-2">
                        <select wire:model="items.{{ $index }}.product_id" class="w-full rounded-lg border-gray-300 shadow-sm text-sm">
                            <option value="">-- Chọn sản phẩm --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                        @error("items.{$index}.product_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" wire:model="items.{{ $index }}.quantity" min="1"
                               class="w-full text-center rounded-lg border-gray-300 shadow-sm text-sm">
                    </td>
                    <td class="px-3 py-2 text-center">
                        @if(count($items) > 1)
                            <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700">✕</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button wire:click="addItem" class="text-indigo-600 hover:text-indigo-800 text-sm mb-4">+ Thêm dòng</button>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            <textarea wire:model="note" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('warehouse.inventory') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</a>
            <button wire:click="save" class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700 transition">
                Xác nhận xuất kho
            </button>
        </div>
    </div>
</div>
