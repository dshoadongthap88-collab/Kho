<div>
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">📋 Kiểm kê kho</h2>
        <p class="text-gray-500 mb-6 text-sm">Nhập số lượng thực tế đếm được. Hệ thống sẽ tự động tính chênh lệch và điều chỉnh tồn kho.</p>

        <table class="w-full mb-4">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-left text-sm">Mã SP</th>
                    <th class="px-3 py-2 text-left text-sm">Tên sản phẩm</th>
                    <th class="px-3 py-2 text-center text-sm">Tồn hệ thống</th>
                    <th class="px-3 py-2 text-center text-sm">Tồn thực tế</th>
                    <th class="px-3 py-2 text-center text-sm">Chênh lệch</th>
                </tr>
            </thead>
            <tbody>
                @foreach($countItems as $index => $item)
                <tr class="border-b {{ $item['difference'] != 0 ? 'bg-yellow-50' : '' }}">
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
