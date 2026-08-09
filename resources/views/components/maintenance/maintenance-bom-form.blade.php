<div class="p-2 bg-white rounded-lg shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">{{ $bomId ? 'Cập nhật BOM bảo dưỡng' : 'Tạo mới BOM bảo dưỡng' }}</h2>
        <a href="{{ route('maintenance-boms.index') }}" class="text-gray-500 hover:underline">&larr; Quay lại danh sách</a>
    </div>

    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã BOM</label>
                <input type="text" wire:model="bom_code" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" required>
                @error('bom_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã tài sản (Xe)</label>
                <select wire:model.live="asset_id" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" required>
                    <option value="">-- Chọn tài sản --</option>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->asset_code }} - {{ $asset->name }}</option>
                    @endforeach
                </select>
                @error('asset_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cấp bảo dưỡng</label>
                <input type="text" wire:model="maintenance_level" placeholder="VD: 1000 giờ" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" required>
                @error('maintenance_level') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chu kỳ (giờ/km)</label>
                <input type="number" wire:model="cycle" placeholder="VD: 1000" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200" required>
                @error('cycle') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        @if($asset_id)
        <div class="p-2 bg-gray-50 rounded-lg border mb-8 flex gap-2">
            <div><span class="text-gray-500 text-sm">Tên xe:</span> <span class="font-semibold">{{ $asset_name }}</span></div>
            <div><span class="text-gray-500 text-sm">Model:</span> <span class="font-semibold">{{ $asset_model ?: 'N/A' }}</span></div>
            <div><span class="text-gray-500 text-sm">Hãng:</span> <span class="font-semibold">{{ $asset_manufacturer ?: 'N/A' }}</span></div>
        </div>
        @endif

        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Chi tiết vật tư</h3>
                <button type="button" wire:click="addItem" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm">
                    + Thêm vật tư
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="p-2 font-semibold text-gray-600 w-1/4">Mã vật tư</th>
                            <th class="p-2 font-semibold text-gray-600">Tên & Thông số</th>
                            <th class="p-2 font-semibold text-gray-600 w-24">ĐVT</th>
                            <th class="p-2 font-semibold text-gray-600 w-24">Số lượng</th>
                            <th class="p-2 font-semibold text-gray-600 w-28">Dự phòng</th>
                            <th class="p-2 font-semibold text-gray-600">Ghi chú</th>
                            <th class="p-2 font-semibold text-gray-600 w-16">Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                        <tr class="border-b" wire:key="item-{{ $index }}">
                            <td class="p-2">
                                <select wire:model.live="items.{{ $index }}.product_id" class="w-full p-2 border rounded" required>
                                    <option value="">-- Chọn VT --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-2 text-sm text-gray-700">
                                <div>{{ $item['product_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $item['product_desc'] }}</div>
                            </td>
                            <td class="p-2 text-sm">{{ $item['product_unit'] }}</td>
                            <td class="p-2">
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.quantity" class="w-full p-2 border rounded" required>
                            </td>
                            <td class="p-2">
                                <input type="number" step="0.01" wire:model="items.{{ $index }}.backup_quantity" class="w-full p-2 border rounded" required>
                            </td>
                            <td class="p-2">
                                <input type="text" wire:model="items.{{ $index }}.note" class="w-full p-2 border rounded" placeholder="Ghi chú...">
                            </td>
                            <td class="p-2 text-center">
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                            </td>
                        </tr>
                        @endforeach
                        @if(count($items) === 0)
                        <tr>
                            <td colspan="7" class="p-2 text-center text-gray-500">Chưa có vật tư nào. Bấm "Thêm vật tư" để bắt đầu.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-8">
            <a href="{{ route('maintenance-boms.index') }}" class="px-6 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Hủy</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold shadow">
                {{ $bomId ? 'Cập nhật BOM' : 'Lưu BOM' }}
            </button>
        </div>
    </form>
</div>
