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
                        <option value="{{ $supplier->name }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hãng sản xuất</label>
                <input type="text" wire:model="manufacturer" list="brands_list" class="w-full rounded-lg border-gray-300 shadow-sm" placeholder="Nhập hãng SX...">
                <datalist id="brands_list">
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Loại nhập</label>
                <select wire:model="type" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="purchase_produced">Nhập mua hàng TP</option>
                    <option value="return_produced">Nhập trả hàng TP</option>
                    <option value="production">Nhập từ sản xuất</option>
                    <option value="import_material">Nhập nguyên vật liệu</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto mb-4">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 min-w-[200px]">Sản phẩm</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-24">Số lô</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-32">Hạn dùng</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-24">Vị trí</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-20">SL</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-16">ĐVT</th>
                                    <th class="px-2 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-24">Đơn giá</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-14">VAT</th>
                                    <th class="px-2 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-32">Thành tiền</th>
                                    <th class="px-2 py-3 border-b border-gray-200 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model.live.debounce.250ms="items.{{ $index }}.product_search" list="product_list_{{ $index }}" 
                                               class="w-full rounded-lg border-gray-300 text-xs font-semibold focus:ring-indigo-500 focus:border-indigo-500 transition"
                                               placeholder="Mã hoặc tên SP...">
                                        <datalist id="product_list_{{ $index }}">
                                            @foreach($products as $product)
                                                <option value="{{ $product->code }} - {{ $product->name }}"></option>
                                            @endforeach
                                        </datalist>
                                        @error("items.{$index}.product_id") <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model.live="items.{{ $index }}.batch_number" 
                                               class="w-full rounded-lg border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Số lô...">
                                        @error("items.{$index}.batch_number") <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="date" wire:model="items.{{ $index }}.expiry_date" 
                                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model="items.{{ $index }}.warehouse_location" 
                                               class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Vị trí...">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="number" wire:model.live="items.{{ $index }}.quantity" step="0.0001" min="0"
                                               class="w-full text-center text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                        @error("items.{$index}.quantity") <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model="items.{{ $index }}.unit" 
                                               class="w-full text-center text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="...">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="number" wire:model.live="items.{{ $index }}.unit_price" step="0.01" min="0"
                                               class="w-full text-right text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="number" wire:model.live="items.{{ $index }}.vat_rate" step="0.1" min="0"
                                               class="w-full text-center text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-4 text-right font-bold text-indigo-700">
                                        {{ number_format($items[$index]['total_amount'] ?? 0) }} đ
                                    </td>
                                    <td class="px-2 py-4 text-center">
                                        @if(count($items) > 1)
                                            <button wire:click="removeItem({{ $index }})" class="text-gray-400 hover:text-red-500 transition p-1 rounded-full hover:bg-red-50">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if(count($items) > 0)
                            <tfoot>
                                <tr class="bg-indigo-50/30">
                                    <td colspan="8" class="px-2 py-3 text-right font-bold text-gray-700 uppercase tracking-wider text-xs">Tổng cộng:</td>
                                    <td class="px-2 py-3 text-right font-extrabold text-indigo-800 text-sm">
                                        {{ number_format(collect($items)->sum('total_amount')) }} đ
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

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
