<div class="px-4">
    <!-- Header -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-2">
        <div>
            <p class="text-sm text-gray-500">Cập nhật chỉ số Odo và Giờ máy hàng ngày cho thiết bị</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="exportExcel" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
                <span>⬇️</span> Xuất Excel
            </button>
            <button wire:click="openImportModal" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
                <span>📁</span> Nhập Excel
            </button>
            <button wire:click="openModal" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow font-semibold transition flex items-center gap-2">
                <span>+</span> Cập nhật Odo mới
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2">
            <span>✅</span> {{ session('message') }}
        </div>
    @endif

    <!-- Toolbar -->
    <div class="filter-bar">
        <div class="filter-grid">
            <div class="filter-field">
                <label class="form-label" for="odo-date">Ngày cập nhật</label>
                <input id="odo-date" type="date" wire:model.live="filterDate" class="input-sm">
            </div>
            <div class="filter-field">
                <label class="form-label" for="odo-search">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input id="odo-search" type="text" wire:model.live.debounce.300ms="search"
                           class="input-sm" placeholder="Mã tài sản, thiết bị...">
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="px-3 py-3 text-left">Mã tài sản</th>
                        <th class="px-3 py-3 text-left">Thiết bị</th>
                        <th class="px-3 py-3 text-right">ODO tích lũy</th>
                        <th class="px-3 py-3 text-right">ODO hiện tại</th>
                        <th class="px-3 py-3 text-right">Số giờ làm việc</th>
                        <th class="px-3 py-3 text-left">Nhân viên lái xe</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-gray-900">{{ $log->asset->asset_code ?? '' }}</td>
                            <td class="px-2 py-1.5 text-sm text-indigo-600 font-semibold">{{ $log->asset->name ?? '' }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($log->old_odo) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-gray-900 text-right">{{ number_format($log->new_odo) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-green-600 text-right">{{ number_format($log->hours_diff) }} giờ</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm text-gray-700">{{ $log->operator }}</td>
                            <td class="px-2 py-1.5 text-sm text-gray-500">{{ Str::limit($log->note, 30) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="edit({{ $log->id }})" class="text-indigo-600 hover:text-indigo-900 mx-1 p-1.5 rounded bg-indigo-50 hover:bg-indigo-100" title="Sửa">✏️</button>
                                <button x-on:click="if(confirm('Xóa bản ghi này?')) $wire.delete({{ $log->id }})" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded" title="Xóa">🗑️</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                                <p class="mb-2 text-3xl">📭</p>
                                <p>Chưa có dữ liệu cập nhật Odo.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
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
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4" id="modal-title">
                                Cập nhật Odo / Giờ Máy
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Ngày cập nhật <span class="text-red-500">*</span></label>
                                        <input type="date" wire:model="reading_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error('reading_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Chọn Thiết Bị <span class="text-red-500">*</span></label>
                                        <select wire:model.live="asset_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">-- Chọn thiết bị --</option>
                                            @foreach($assets as $asset)
                                                <option value="{{ $asset->id }}">{{ $asset->asset_code }} - {{ $asset->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('asset_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">
                                    <div class="grid grid-cols-2 gap-2 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-500">ODO tích lũy (cũ)</label>
                                            <input type="text" disabled wire:model="old_odo" class="mt-1 block w-full bg-gray-100 border-gray-300 rounded-md sm:text-sm text-gray-500 font-bold text-right">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-indigo-700">ODO hiện tại (mới) <span class="text-red-500">*</span></label>
                                            <input type="number" wire:model.live.debounce.300ms="new_odo" class="mt-1 block w-full border-indigo-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-right font-bold text-indigo-900">
                                            @error('new_odo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Nhân viên lái xe</label>
                                            <input type="text" wire:model="operator" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            @error('operator') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-green-700">Số giờ làm việc (ca) <span class="text-red-500">*</span></label>
                                            <input type="number" step="0.5" wire:model="hours_diff" class="mt-1 block w-full border-green-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm text-right font-bold text-green-700">
                                            @error('hours_diff') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                                    <textarea wire:model="note" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Lưu nhật ký
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

    <!-- Import Modal -->
    @if($isImportModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeImportModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                    <form wire:submit.prevent="importExcel">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4" id="modal-title">
                                Import Dữ Liệu Odo / Giờ Máy
                            </h3>
                            <div class="space-y-4">
                                <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg text-sm text-blue-800">
                                    <p class="font-bold mb-1">Cấu trúc file Excel (.xlsx, .csv):</p>
                                    <ul class="list-disc list-inside">
                                        <li>Cột 1: <b>ma_may</b></li>
                                        <li>Cột 2: <b>ngay_bao_cao</b> (YYYY-MM-DD)</li>
                                        <li>Cột 3: <b>odo_hien_tai</b></li>
                                        <li>Cột 4: <b>gio_hien_tai</b></li>
                                        <li>Cột 5: <b>ghi_chu</b></li>
                                    </ul>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Chọn file Excel <span class="text-red-500">*</span></label>
                                    <input type="file" wire:model="excelFile" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                    <div wire:loading wire:target="excelFile" class="text-xs text-indigo-500 mt-1">Đang tải lên...</div>
                                    @error('excelFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-bold text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Tiến Hành Import
                            </button>
                            <button type="button" wire:click="closeImportModal" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Hủy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
