<div>
    <div class="flex justify-between items-center mb-4">
        <div class="flex-1 max-w-sm">
            <input wire:model.live="search" type="text" placeholder="Tìm theo tên, mã, hãng..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>
        <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <span>➕</span> Thêm sản phẩm
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b text-gray-600 uppercase text-xs font-semibold">
                    <th class="px-4 py-3">Mã sản phẩm</th>
                    <th class="px-4 py-3">Tên sản phẩm</th>
                    <th class="px-4 py-3">Hãng sản xuất</th>
                    <th class="px-4 py-3">QC Hộp</th>
                    <th class="px-4 py-3">QC Thùng</th>
                    <th class="px-4 py-3 text-center">Số lượng</th>
                    <th class="px-4 py-3">Vị trí</th>
                    <th class="px-4 py-3">Tình trạng</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-sm text-blue-600">{{ $product->code }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->brand }}</td>
                        <td class="px-4 py-3 text-gray-600 italic">{{ $product->box_spec }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->carton_spec }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ ($product->inventory?->quantity ?? 0) > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ number_format($product->inventory?->quantity ?? 0) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->location }}</td>
                        <td class="px-4 py-3">
                            @if($product->status === 'active')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Đang kinh doanh</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Ngừng kinh doanh</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="openModal({{ $product->id }})" class="text-blue-500 hover:text-blue-700 mr-2" title="Sửa">📝</button>
                            <button onclick="confirm('Xoá sản phẩm này?') || event.stopImmediatePropagation()" wire:click="delete({{ $product->id }})" class="text-red-500 hover:text-red-700" title="Xoá">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">Không tìm thấy sản phẩm nào.</td>
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
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ $isEdit ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới' }}</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã sản phẩm</label>
                                <input type="text" wire:model="code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tên sản phẩm</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                                <label class="block text-sm font-medium text-gray-700">QC Thùng (ghi tay)</label>
                                <input type="text" wire:model="carton_spec" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="VD: 24 chai/thùng">
                                @error('carton_spec') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Vị trí</label>
                                <input type="text" wire:model="location" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tình trạng</label>
                                <select wire:model="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <option value="active">Đang kinh doanh</option>
                                    <option value="inactive">Ngừng kinh doanh</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
</div>
