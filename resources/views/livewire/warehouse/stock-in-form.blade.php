<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">📥 Phiếu nhập kho</h2>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                <input type="text" wire:model="supplier_name" list="suppliers_list" class="w-full rounded-lg border-gray-300 shadow-sm" placeholder="Chọn hoặc nhập tên...">
                <datalist id="suppliers_list">
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->name }}">
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hãng sản xuất</label>
                <input type="text" wire:model="manufacturer" list="brands_list" class="w-full rounded-lg border-gray-300 shadow-sm" placeholder="Nhập hãng SX...">
                <datalist id="brands_list">
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}">
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Loại nhập</label>
                <select wire:model="type" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="manual">Nhập thủ công</option>
                    <option value="purchase">Mua hàng</option>
                    <option value="return">Trả hàng</option>
                    <option value="production">Từ sản xuất</option>
                </select>
            </div>
        </div>

        <table class="w-full mb-4">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Sản phẩm</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider w-32">Số lô</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider w-40">Hạn dùng</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider w-40">Vị trí</th>
                    <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wider w-24">SL</th>
                    <th class="px-3 py-2 text-center text-xs font-semibold uppercase tracking-wider w-10"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-3 py-3">
                        <select wire:model.live="items.{{ $index }}.product_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Chọn sản phẩm --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->code }} - {{ $product->name }} ({{ $product->brand }})</option>
                            @endforeach
                        </select>
                        @error("items.{$index}.product_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </td>
                    <td class="px-3 py-3">
                        <input type="text" wire:model.live="items.{{ $index }}.batch_number" 
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Số lô...">
                        @error("items.{$index}.batch_number") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </td>
                    <td class="px-3 py-3">
                        <input type="date" wire:model="items.{{ $index }}.expiry_date" 
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </td>
                    <td class="px-3 py-3">
                        <input type="text" wire:model="items.{{ $index }}.warehouse_location" 
                               class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Vị trí...">
                    </td>
                    <td class="px-3 py-3">
                        <input type="number" wire:model.live="items.{{ $index }}.quantity" step="0.0001" min="0"
                               class="w-full text-center rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error("items.{$index}.quantity") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </td>
                    <td class="px-3 py-3 text-center">
                        @if(count($items) > 1)
                            <button wire:click="removeItem({{ $index }})" class="text-red-400 hover:text-red-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex items-center gap-4 mb-4">
            @if($this->canAddItem())
                <button wire:click="addItem" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center gap-1 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="12 4v16m8-8H4"></path></svg>
                    Thêm dòng
                </button>
            @else
                <button disabled class="text-gray-400 cursor-not-allowed text-sm flex items-center gap-1" title="Vui lòng nhập đủ thông tin dòng hiện tại trước khi thêm dòng mới">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="12 4v16m8-8H4"></path></svg>
                    Thêm dòng (Chưa hoàn tất dòng trên)
                </button>
            @endif

            <button wire:click="openProductModal" class="text-green-600 hover:text-green-800 font-medium text-sm flex items-center gap-1 transition ml-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                ➕ Thêm sản phẩm mới
            </button>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            <textarea wire:model="note" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('warehouse.inventory') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</a>
            <button wire:click="save" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                Xác nhận nhập kho
            </button>
        </div>
    </div>

    <!-- Quick Product Modal -->
    @if($showProductModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-bold mb-4">Tạo nhanh sản phẩm mới</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mã sản phẩm</label>
                        <input type="text" wire:model="newPCode" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('newPCode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tên sản phẩm</label>
                        <input type="text" wire:model="newPName" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('newPName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Đơn vị tính</label>
                        <input type="text" wire:model="newPUnit" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('newPUnit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hãng sản xuất (Kế thừa từ header)</label>
                        <input type="text" disabled value="{{ $manufacturer }}" class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showProductModal', false)" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button wire:click="createProduct" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Lưu & Thêm dòng</button>
                </div>
            </div>
        </div>
    @endif
</div>
