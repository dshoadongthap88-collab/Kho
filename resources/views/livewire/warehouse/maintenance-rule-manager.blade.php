<div class="px-4">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-gray-500">Cấu hình định mức, chu kỳ bảo dưỡng cho từng loại thiết bị</p>
        </div>
        <button wire:click="openModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
            <span>+</span> Thêm Định Mức
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
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm loại thiết bị, hạng mục..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Loại Thiết Bị</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hạng Mục</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Chu Kỳ Km</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Chu Kỳ Giờ Máy</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Chu Kỳ Tháng</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Vật Tư Cần Thay</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($rules as $rule)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $rule->machine_type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-indigo-600">{{ $rule->category }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold text-orange-600">{{ $rule->cycle_km > 0 ? number_format($rule->cycle_km) . ' km' : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold text-blue-600">{{ $rule->cycle_hours > 0 ? number_format($rule->cycle_hours) . ' giờ' : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600">{{ $rule->cycle_months > 0 ? $rule->cycle_months . ' tháng' : '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if(!empty($rule->material_needed))
                                    @foreach($rule->material_needed as $mat)
                                        <span class="inline-block bg-slate-100 px-2 py-0.5 rounded text-xs border border-slate-200 mr-1 mb-1">{{ $mat }}</span>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $rule->id }})" class="text-indigo-600 hover:text-indigo-900 mx-2 bg-indigo-50 hover:bg-indigo-100 p-1.5 rounded" title="Sửa">✏️</button>
                                <button x-on:click="if(confirm('Xóa định mức này?')) $wire.delete({{ $rule->id }})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded" title="Xóa">🗑️</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <p class="mb-2 text-3xl">📭</p>
                                <p>Chưa có định mức bảo dưỡng nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $rules->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4" id="modal-title">
                                {{ $ruleId ? 'Cập nhật định mức' : 'Thêm định mức mới' }}
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Loại Thiết Bị <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="machine_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="VD: Máy Xúc, Xe Lu...">
                                        @error('machine_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Hạng Mục <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="VD: Thay nhớt, Đại tu...">
                                        @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-4 p-4 bg-slate-50 rounded border border-slate-200">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Chu Kỳ Km <span class="text-red-500">*</span></label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="number" wire:model="cycle_km" class="flex-1 block w-full border-gray-300 rounded-none rounded-l-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right">
                                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">km</span>
                                        </div>
                                        @error('cycle_km') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Chu Kỳ Giờ Máy <span class="text-red-500">*</span></label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="number" wire:model="cycle_hours" class="flex-1 block w-full border-gray-300 rounded-none rounded-l-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right">
                                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">giờ</span>
                                        </div>
                                        @error('cycle_hours') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Chu Kỳ Tháng <span class="text-red-500">*</span></label>
                                        <div class="mt-1 flex rounded-md shadow-sm">
                                            <input type="number" wire:model="cycle_months" class="flex-1 block w-full border-gray-300 rounded-none rounded-l-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right">
                                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">tháng</span>
                                        </div>
                                        @error('cycle_months') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-span-3 text-xs text-gray-500 mt-1 italic">
                                        * Đặt giá trị 0 nếu hạng mục không phụ thuộc vào chỉ số đó. (Ví dụ: Thay nhớt chỉ cần 5000km hoặc 200 giờ máy thì đặt Tháng = 0)
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Vật tư cần thay</label>
                                    <input type="text" wire:model="material_needed_raw" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="VD: Nhớt động cơ 15W40, Lọc dầu (cách nhau bởi dấu phẩy)">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nội dung / Ghi chú bảo dưỡng</label>
                                    <textarea wire:model="content" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Lưu định mức
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
