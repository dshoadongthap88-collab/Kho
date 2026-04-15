<div>
    <div class="flex justify-between items-center mb-4">
        <div class="flex gap-4 flex-1 max-w-2xl">
            <div class="flex-1">
                <input wire:model.live="search" type="text" placeholder="Tìm theo tên, sđt, người liên hệ..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
            </div>
            <select wire:model.live="filterType" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                <option value="">Tất cả đối tác</option>
                <option value="customer">Khách hàng</option>
                <option value="supplier">Nhà cung cấp</option>
                <option value="both">Cả hai</option>
            </select>
        </div>
        <button wire:click="openModal" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <span>➕</span> Thêm đối tác
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
                    <th class="px-4 py-3">Phân loại</th>
                    <th class="px-4 py-3">Tên Khách hàng/NCC</th>
                    <th class="px-4 py-3">Địa chỉ</th>
                    <th class="px-4 py-3">Số điện thoại</th>
                    <th class="px-4 py-3">Người liên hệ</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3 text-right">Công nợ (đ)</th>
                    <th class="px-4 py-3">Tình trạng</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($contacts as $contact)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($contact->type === 'customer')
                                <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Khách hàng</span>
                            @elseif($contact->type === 'supplier')
                                <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs">Nhà cung cấp</span>
                            @else
                                <span class="bg-amber-100 text-amber-700 px-2 py-1 rounded text-xs">Cả hai</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $contact->name }}</td>
                        <td class="px-4 py-3 text-gray-600 text-sm max-w-xs truncate">{{ $contact->address }}</td>
                        <td class="px-4 py-3 text-gray-600 font-mono">{{ $contact->phone }}</td>
                        <td class="px-4 py-3 text-gray-800 font-medium">{{ $contact->contact_person }}</td>
                        <td class="px-4 py-3 text-blue-600 text-sm italic underline">{{ $contact->email }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $contact->total_debt > 0 ? 'text-red-500' : 'text-slate-400' }}">
                            {{ $contact->total_debt > 0 ? number_format($contact->total_debt) : '0' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($contact->status === 'active')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Hoạt động</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Ngừng HĐ</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="openModal({{ $contact->id }})" class="text-blue-500 hover:text-blue-700 mr-2" title="Sửa">📝</button>
                            <button onclick="confirm('Xoá đối tác này?') || event.stopImmediatePropagation()" wire:click="delete({{ $contact->id }})" class="text-red-500 hover:text-red-700" title="Xoá">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">Chưa có dữ liệu đối tác.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $contacts->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ $isEdit ? 'Chỉnh sửa đối tác' : 'Thêm đối tác mới' }}</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tên Khách hàng/NCC</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Người liên hệ</label>
                                    <input type="text" wire:model="contact_person" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    @error('contact_person') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số điện thoại</label>
                                    <input type="text" wire:model="phone" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" wire:model="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Địa chỉ</label>
                                <textarea wire:model="address" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"></textarea>
                                @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phân loại</label>
                                    <select wire:model="type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="customer">Khách hàng</option>
                                        <option value="supplier">Nhà cung cấp</option>
                                        <option value="both">Cả hai</option>
                                    </select>
                                    @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tình trạng</label>
                                    <select wire:model="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="active">Hoạt động</option>
                                        <option value="inactive">Ngừng hoạt động</option>
                                    </select>
                                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Lưu thông tin
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