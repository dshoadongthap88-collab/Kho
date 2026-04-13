<div>
    <div class="flex justify-between items-center mb-4">
        <div class="flex-1 max-w-sm">
            <input wire:model.live="search" type="text" placeholder="Tìm theo tên, mã, hãng..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex gap-2">
            @if(count($selectedProducts) > 0)
                <button onclick="window.print()" class="bg-gray-800 hover:bg-black text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                    <span>🖨️</span> In {{ count($selectedProducts) }} mục đã chọn
                </button>
            @endif
            <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <span>➕</span> Thêm NVL
            </button>
        </div>
    </div>

    <div class="flex items-center gap-4 mb-4 bg-gray-50 p-3 rounded-lg border">
        <span class="text-sm font-medium text-gray-600">Phân loại nhanh:</span>
        <div class="flex gap-2">
            <button wire:click="$set('filterMode', 'all')" class="px-3 py-1 text-sm rounded-full {{ $filterMode === 'all' ? 'bg-blue-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-100' }}">
                Tất cả
            </button>
            <button wire:click="$set('filterMode', 'expiring')" class="px-3 py-1 text-sm rounded-full {{ $filterMode === 'expiring' ? 'bg-red-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-100' }}">
                Sắp hết hạn
            </button>
            <button wire:click="$set('filterMode', 'low_stock')" class="px-3 py-1 text-sm rounded-full {{ $filterMode === 'low_stock' ? 'bg-orange-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-100' }}">
                Sắp hết tồn
            </button>
        </div>
        
        <div class="h-6 w-px bg-gray-300 mx-2"></div>
        
        <div class="flex gap-2">
            <button wire:click="selectExpiring" class="text-xs text-red-600 hover:underline">Chọn mục hết hạn</button>
            <button wire:click="selectLowStock" class="text-xs text-orange-600 hover:underline">Chọn mục hết tồn</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b text-gray-600 uppercase text-xs font-semibold">
                    <th class="px-4 py-3 w-10 no-print">
                        <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $allProductIdsOnPage) }}])" 
                               {{ count($selectedProducts) === count($allProductIdsOnPage) && count($allProductIdsOnPage) > 0 ? 'checked' : '' }}
                               class="rounded border-gray-300">
                    </th>
                    <th class="px-4 py-3">Mã NVL</th>
                    <th class="px-4 py-3 w-16 text-center">Hình ảnh</th>
                    <th class="px-4 py-3">Tên Nguyên Vật Liệu</th>
                    <th class="px-4 py-3">Hãng sản xuất</th>
                    <th class="px-4 py-3">QC Hộp</th>
                    <th class="px-4 py-3">ĐV tính (g/kg/ml/L)</th>
                    <th class="px-4 py-3">Số lô</th>
                    <th class="px-4 py-3 text-center">Số lượng</th>
                    <th class="px-4 py-3">Tình trạng</th>
                    <th class="px-4 py-3 text-right no-print">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition border-b 
                        {{ $product->is_expiring_soon ? 'bg-red-50' : ($product->is_low_stock ? 'bg-orange-50' : '') }}
                        {{ in_array($product->id, $selectedProducts) ? 'ring-2 ring-blue-400' : '' }}">
                        <td class="px-4 py-3 no-print">
                            <input type="checkbox" wire:model.live="selectedProducts" value="{{ $product->id }}" class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3 font-mono text-sm text-blue-600">{{ $product->code }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Img" class="w-10 h-10 object-cover rounded shadow-sm border">
                            @else
                                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded border text-gray-400 text-xs">No img</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->brand }}</td>
                        <td class="px-4 py-3 text-gray-600 italic">{{ $product->box_spec }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->unit }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-purple-700">{{ $product->batch_number }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                {{ $product->is_low_stock ? 'bg-orange-600 text-white' : (($product->inventory?->quantity ?? 0) > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                                {{ number_format($product->inventory?->quantity ?? 0) }}
                                @if($product->is_low_stock) ⚠️ @endif
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($product->status === 'active')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Đang kinh doanh</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Ngừng kinh doanh</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right no-print">
                            <button wire:click="openModal({{ $product->id }})" class="text-blue-500 hover:text-blue-700 mr-2" title="Sửa">📝</button>
                            <button onclick="confirm('Xoá sản phẩm này?') || event.stopImmediatePropagation()" wire:click="delete({{ $product->id }})" class="text-red-500 hover:text-red-700" title="Xoá">🗑️</button>
                        </td>
                    </tr>
                @empty
                     <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ $isEdit ? 'Chỉnh sửa NVL' : 'Thêm NVL mới' }}</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã NVL</label>
                                <input type="text" wire:model="code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tên NVL</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2 hidden">
                                <input type="hidden" wire:model="type" value="material">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Hình ảnh</label>
                                <input type="file" wire:model="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hãng sản xuất</label>
                                <input type="text" wire:model="brand" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('brand') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Số lượng</label>
                                <input type="number" wire:model="quantity" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">QC Hộp (loại chai/hộp...)</label>
                                <input type="text" wire:model="box_spec" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="VD: Chai 500ml">
                                @error('box_spec') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">ĐV tính (g/kg/ml/L)</label>
                                <input type="text" wire:model="unit" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="VD: kg, g, ml, L...">
                                @error('unit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                             <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Vị trí</label>
                                <input type="text" wire:model="location" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Số lô <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="batch_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('batch_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hạn sử dụng</label>
                                <input type="date" wire:model="expiry_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('expiry_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tồn tối thiểu</label>
                                <input type="number" wire:model="min_stock" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('min_stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Lưu lại
                        </button>
                        <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Modal -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showImportModal', false)"></div>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Nhập dữ liệu từ Excel</h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-2">Tải tệp Excel (.xlsx, .xls) hoặc CSV để nhập hàng loạt sản phẩm.</p>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium underline">Tải tệp mẫu (.xlsx) tại đây</a>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chọn tệp của bạn</label>
                            <input type="file" wire:model="excelFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('excelFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div wire:loading wire:target="excelFile" class="mt-2 text-sm text-blue-600">
                            Đang tải tệp lên...
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="importExcel" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Bắt đầu nhập
                        </button>
                        <button type="button" wire:click="$set('showImportModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        @media print {
            .no-print, 
            header, 
            nav, 
            aside, 
            .sidebar, 
            .flex.justify-between.items-center.mb-4,
            .flex.items-center.gap-4.mb-4,
            .px-4.py-3.bg-gray-50.border-t {
                display: none !important;
            }
            .bg-white { background-color: transparent !important; }
            .shadow-sm { box-shadow: none !important; }
            .border { border: 1px solid #ddd !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #ddd !important; padding: 8px !important; }
            tr:not(.ring-2) { display: none !important; }
            tr.ring-2 { display: table-row !important; }
            @page { size: landscape; margin: 1cm; }
        }
    </style>
</div>
