<div class="px-4 pb-10">
    <!-- Header -->
    <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-2">
        <div>
            <p class="text-sm text-slate-400 mt-0.5">Quản lý các phiếu thực hiện bảo dưỡng sửa chữa</p>
        </div>
        <button wire:click="openModal" class="flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition">
            <span>+</span> Lập Phiếu Bảo Dưỡng
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-2 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium">
            <span>✅</span> {{ session('message') }}
        </div>
    @endif

    <!-- Toolbar -->
    <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-200 mb-5 flex flex-wrap gap-2 items-center justify-between">
        <div class="w-full md:w-1/3 relative">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm số phiếu, thiết bị..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl text-sm focus:ring-indigo-500 focus:border-indigo-500">
            
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="px-3 py-3 text-left">Số Phiếu</th>
                        <th class="px-3 py-3 text-left">Ngày bảo dưỡng</th>
                        <th class="px-3 py-3 text-left">Tên thiết bị</th>
                        <th class="px-3 py-3 text-left">Mã tài sản</th>
                        <th class="px-3 py-3 text-left">Mức bảo dưỡng</th>
                        <th class="px-3 py-3 text-left">Tên tài xế</th>
                        <th class="px-3 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-indigo-600">{{ $ticket->ticket_code }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-600">{{ $ticket->maintenance_date ? $ticket->maintenance_date->format('d/m/Y') : '-' }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-gray-900">{{ $ticket->asset->name ?? 'N/A' }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $ticket->asset->asset_code ?? '' }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-semibold text-gray-700">{{ $ticket->maintenance_rule_id ?? 'N/A' }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-600">{{ $ticket->staff_name }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $ticket->id }})" class="text-indigo-600 hover:text-indigo-900 mx-2 bg-indigo-50 hover:bg-indigo-100 p-1.5 rounded" title="Chi tiết / Sửa">✏️</button>
                                <button x-on:click="if(confirm('Xóa phiếu bảo dưỡng này?')) $wire.delete({{ $ticket->id }})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded" title="Xóa">🗑️</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                
                                <p>Chưa có phiếu bảo dưỡng nào.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200">
            {{ $tickets->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-xl text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full max-h-[90vh] flex flex-col">
                    <form wire:submit.prevent="save" class="flex flex-col h-full">
                        <div class="bg-white px-6 pt-5 pb-4 border-b border-gray-100 flex-shrink-0 flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                {{ $ticketId ? 'Cập nhật phiếu bảo dưỡng' : 'Lập phiếu bảo dưỡng mới' }}
                            </h3>
                            <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                                <span class="text-2xl">&times;</span>
                            </button>
                        </div>

                        <div class="p-2 overflow-y-auto" style="max-height: calc(90vh - 140px);">
                            <div class="space-y-6">
                                
                                <!-- Khối Thông Tin Cơ Bản -->
                                <div class="bg-slate-50 p-2 rounded-lg border border-slate-200 space-y-4">
                                    <h4 class="font-bold text-indigo-800 border-b border-slate-200 pb-2 mb-4 uppercase text-sm">1. Thông tin chung</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Số Phiếu</label>
                                            <input type="text" wire:model="ticket_code" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 sm:text-sm font-mono font-bold" readonly>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Ngày Thực Hiện <span class="text-red-500">*</span></label>
                                            <input type="date" wire:model="maintenance_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            @error('maintenance_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700">Thiết Bị <span class="text-red-500">*</span></label>
                                            <select wire:model.live="asset_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">-- Chọn Thiết Bị --</option>
                                                @foreach($assets as $asset)
                                                    <option value="{{ $asset->id }}">[{{ $asset->asset_code }}] {{ $asset->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('asset_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Cấp Bảo Dưỡng / Hạng Mục</label>
                                            <select wire:model="maintenance_rule_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                <option value="">-- Chọn Cấp --</option>
                                                @foreach($rules as $rule)
                                                    <option value="{{ $rule->rule_code ?: $rule->category }}">{{ $rule->rule_code ?: $rule->category }} - {{ $rule->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-xs text-gray-500">Phải chọn thiết bị trước để hiển thị cấp bảo dưỡng tương ứng.</span>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Giờ Máy Hiện Tại <span class="text-red-500">*</span></label>
                                            <div class="mt-1 flex rounded-md shadow-sm">
                                                <input type="number" step="0.01" wire:model="maintenance_odo" class="flex-1 block w-full border-gray-300 rounded-none rounded-l-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right font-bold text-blue-600">
                                                <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">giờ</span>
                                            </div>
                                            @error('maintenance_odo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Khối Nội Dung Công Việc -->
                                <div class="bg-white p-2 rounded-lg border border-gray-200 space-y-4">
                                    <h4 class="font-bold text-indigo-800 border-b border-gray-200 pb-2 mb-4 uppercase text-sm">2. Nội dung thực hiện</h4>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Chi tiết công việc đã làm</label>
                                        <textarea wire:model="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Mô tả các hạng mục đã kiểm tra, sửa chữa..."></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Vật tư / Phụ tùng thay thế</label>
                                        <textarea wire:model="materials_used" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="VD: 20L nhớt động cơ, 1 lọc dầu, 1 lọc gió..."></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nhân Viên Thực Hiện</label>
                                            <input type="text" wire:model="staff_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Người Kiểm Tra (KCS / Quản Đốc)</label>
                                            <input type="text" wire:model="inspector" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Kết Quả Bàn Giao</label>
                                        <select wire:model="result" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">-- Trạng thái máy sau bảo dưỡng --</option>
                                            <option value="Hoạt động bình thường">Hoạt động bình thường</option>
                                            <option value="Cần theo dõi thêm">Cần theo dõi thêm</option>
                                            <option value="Chưa đạt yêu cầu">Chưa đạt yêu cầu</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Khối Hình Ảnh -->
                                <div class="bg-white p-2 rounded-lg border border-gray-200 space-y-4">
                                    <h4 class="font-bold text-indigo-800 border-b border-gray-200 pb-2 mb-4 uppercase text-sm">3. Hình ảnh đính kèm</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Hình ảnh TRƯỚC bảo dưỡng</label>
                                            <input type="file" wire:model="image_before" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            <div wire:loading wire:target="image_before" class="text-xs text-indigo-500 mt-1">Đang tải lên...</div>
                                            
                                            @if ($image_before)
                                                <div class="mt-2"><img src="{{ $image_before->temporaryUrl() }}" class="h-32 object-contain border rounded p-1"></div>
                                            @elseif ($existing_image_before)
                                                <div class="mt-2"><img src="{{ Storage::url($existing_image_before) }}" class="h-32 object-contain border rounded p-1"></div>
                                            @endif
                                            @error('image_before') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Hình ảnh SAU bảo dưỡng</label>
                                            <input type="file" wire:model="image_after" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            <div wire:loading wire:target="image_after" class="text-xs text-indigo-500 mt-1">Đang tải lên...</div>
                                            
                                            @if ($image_after)
                                                <div class="mt-2"><img src="{{ $image_after->temporaryUrl() }}" class="h-32 object-contain border rounded p-1"></div>
                                            @elseif ($existing_image_after)
                                                <div class="mt-2"><img src="{{ Storage::url($existing_image_after) }}" class="h-32 object-contain border rounded p-1"></div>
                                            @endif
                                            @error('image_after') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex-shrink-0 flex justify-end gap-3 rounded-b-xl">
                            <button type="button" wire:click="closeModal" class="px-6 py-2 bg-white border border-gray-300 rounded-lg text-sm font-bold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                                Hủy
                            </button>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 rounded-lg text-sm font-bold text-white hover:bg-indigo-700 transition shadow-sm">
                                Hoàn Thành & Lưu Phiếu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
