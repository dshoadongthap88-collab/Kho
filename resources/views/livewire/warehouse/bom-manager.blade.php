<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">🔧 Quản lý BOM - Định mức nguyên vật liệu</h2>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Chọn sản phẩm thành phẩm</label>
            <select wire:model.live="selectedProductId" class="w-full rounded-lg border-gray-300 shadow-sm">
                <option value="">-- Chọn sản phẩm --</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }}</option>
                @endforeach
            </select>
        </div>

        @if($selectedProductId)
        <div class="mb-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-semibold">Danh sách NVL</h3>
                <div class="flex gap-2 no-print">
                    <button onclick="window.print()" class="bg-gray-800 hover:bg-black text-white px-4 py-2 rounded-lg flex items-center gap-2 transition text-sm">
                        🖨️ In BOM NVL
                    </button>
                    <button wire:click="saveBom" class="bg-blue-600 border border-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition text-sm">
                        💾 Lưu cấu hình
                    </button>
                </div>
            </div>
            @if(count($bomItems) > 0)
            <table class="w-full mb-4">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left text-sm">NVL</th>
                        <th class="px-3 py-2 text-center text-sm">Định mức / 1 SP</th>
                        <th class="px-3 py-2 text-center text-sm">ĐVT</th>
                        @if($availability)
                        <th class="px-3 py-2 text-center text-sm">Tồn kho</th>
                        <th class="px-3 py-2 text-center text-sm">Trạng thái</th>
                        @endif
                        <th class="px-3 py-2 text-center text-sm w-20"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bomItems as $index => $item)
                    <tr class="border-b">
                        <td class="px-3 py-2 text-sm">{{ $item['material_name'] }}</td>
                        <td class="px-3 py-2 text-center text-sm">{{ floatval($item['quantity']) }}</td>
                        <td class="px-3 py-2 text-center text-sm text-gray-500">{{ $item['unit'] }}</td>
                        @if($availability && isset($availability['details'][$index]))
                        <td class="px-3 py-2 text-center text-sm">{{ number_format($availability['details'][$index]['available']) }}</td>
                        <td class="px-3 py-2 text-center">
                            @if($availability['details'][$index]['is_sufficient'])
                                <span class="text-green-600 text-xs">🟢 Đủ</span>
                            @else
                                <span class="text-red-600 text-xs">🔴 Thiếu</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-3 py-2 text-center">
                            <button wire:click="removeMaterial({{ $item['id'] }})" class="text-red-500 hover:text-red-700 text-sm">Xóa</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <p class="text-gray-400 text-sm mb-4">Chưa có NVL nào được khai báo cho sản phẩm này.</p>
            @endif

            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold mb-3">Thêm nguyên vật liệu</h4>
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">NVL</label>
                        <select wire:model="newMaterialId" class="w-full rounded border-gray-300 shadow-sm text-sm">
                            <option value="">-- Chọn NVL --</option>
                            @foreach($materials as $mat)
                                <option value="{{ $mat->id }}">{{ $mat->code }} - {{ $mat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-28">
                        <label class="block text-xs text-gray-500 mb-1">Số lượng</label>
                        <input type="number" wire:model="newQuantity" step="0.001" min="0.001" class="w-full rounded border-gray-300 shadow-sm text-sm">
                    </div>
                    <div class="w-24">
                        <label class="block text-xs text-gray-500 mb-1">ĐVT</label>
                        <input type="text" wire:model="newUnit" placeholder="tự động" class="w-full rounded border-gray-300 shadow-sm text-sm">
                    </div>
                    <button wire:click="addMaterial" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">Thêm</button>
                </div>
            </div>
        </div>
        @endif
    </div>
    <style>
        @media print {
            .no-print, header, nav, aside.sidebar, .mb-6:first-child, .bg-gray-50.p-4 { display: none !important; }
            .bg-white { box-shadow: none !important; }
            body { font-size: 12pt; }
        }
    </style>
</div>
