<div class="px-4">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">Quản lý các kế hoạch bảo dưỡng, bảo trì tài sản thiết bị</p>
        </div>
        <button wire:click="openModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
            <span>+</span> Thêm Kế Hoạch Mới
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2">
            <span>✅</span> {{ session('message') }}
        </div>
    @endif

    <!-- Toolbar -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6 flex flex-wrap gap-4 items-center justify-between">
        <div class="w-full md:w-1/3 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm mã KH, tài sản, hạng mục..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
        </div>
        
        <div class="flex gap-2">
            <button wire:click="$set('statusFilter', '')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $statusFilter === '' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">Tất cả</button>
            <button wire:click="$set('statusFilter', 'pending')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $statusFilter === 'pending' ? 'bg-yellow-50 text-yellow-700' : 'text-gray-600 hover:bg-gray-50' }}">Đã lập</button>
            <button wire:click="$set('statusFilter', 'doing')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $statusFilter === 'doing' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">Đang thực hiện</button>
            <button wire:click="$set('statusFilter', 'completed')" class="px-3 py-1.5 text-sm font-medium rounded-lg {{ $statusFilter === 'completed' ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-50' }}">Hoàn thành</button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã Kế Hoạch</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tài Sản</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hạng Mục</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dự Kiến</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Odo Hiện Tại</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Odo Bảo Dưỡng</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($plans as $plan)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">{{ $plan->plan_code }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{ $plan->asset->asset_code ?? '' }}<br><span class="text-xs font-normal text-gray-500">{{ $plan->asset->name ?? '' }}</span></td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $plan->category }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $plan->expected_date ? \Carbon\Carbon::parse($plan->expected_date)->format('d/m/Y') : '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($plan->current_odo) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">{{ number_format($plan->maintenance_odo) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($plan->status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Đã lập</span>
                                @elseif($plan->status === 'cho_chuan_bi_vat_tu')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Chờ VT</span>
                                @elseif($plan->status === 'thieu_vat_tu')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Thiếu VT</span>
                                @elseif($plan->status === 'san_sang_xuat')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sẵn sàng xuất</span>
                                @elseif($plan->status === 'dang_bao_duong')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Đang bảo dưỡng</span>
                                @elseif($plan->status === 'completed' || $plan->status === 'hoan_thanh')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Hoàn thành</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $plan->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if(in_array($plan->status, ['cho_chuan_bi_vat_tu', 'pending', 'thieu_vat_tu']))
                                    <button wire:click="checkInventory({{ $plan->id }})" class="text-white bg-blue-600 hover:bg-blue-700 px-2 py-1.5 rounded mr-1" title="Kiểm tra tồn kho vật tư">🔍 Kiểm tra kho</button>
                                @endif
                                @if($plan->status === 'san_sang_xuat')
                                    <button wire:click="createStockOut({{ $plan->id }})" class="text-white bg-green-600 hover:bg-green-700 px-2 py-1.5 rounded mr-1" title="Tạo phiếu xuất kho">📦 Xuất kho</button>
                                @endif
                                <button wire:click="edit({{ $plan->id }})" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-1.5 rounded mr-1" title="Sửa">✏️</button>
                                <button x-on:click="if(confirm('Xóa kế hoạch này?')) $wire.delete({{ $plan->id }})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded" title="Xóa">🗑️</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <p class="mb-2 text-3xl">📭</p>
                                <p>Chưa có kế hoạch bảo dưỡng nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $plans->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4" id="modal-title">
                                {{ $planId ? 'Cập nhật kế hoạch bảo dưỡng' : 'Thêm mới kế hoạch bảo dưỡng' }}
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Mã kế hoạch <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="plan_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error('plan_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tài sản <span class="text-red-500">*</span></label>
                                        <select wire:model="asset_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">-- Chọn tài sản --</option>
                                            @foreach($assets as $asset)
                                                <option value="{{ $asset->id }}">{{ $asset->asset_code }} - {{ $asset->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('asset_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Hạng mục <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Ngày dự kiến</label>
                                        <input type="date" wire:model="expected_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error('expected_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Odo Hiện Tại (Lúc lập KH)</label>
                                        <input type="number" wire:model="current_odo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Odo / Giờ máy Bảo Dưỡng</label>
                                        <input type="number" wire:model="maintenance_odo" class="mt-1 block w-full border-indigo-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right font-bold text-indigo-900">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Trạng thái <span class="text-red-500">*</span></label>
                                        <select wire:model="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="pending">Đã lập</option>
                                            <option value="doing">Đang thực hiện</option>
                                            <option value="completed">Hoàn thành</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Người phụ trách</label>
                                        <input type="text" wire:model="assigned_to" placeholder="VD: Anh Tùng, Đội thợ..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Lưu kế hoạch
                            </button>
                            <button type="button" wire:click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Hủy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
