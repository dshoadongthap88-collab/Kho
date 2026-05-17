<div class="space-y-4">
    <!-- CSS để in ấn -->
    <style>
        @media print {
            .no-print { display: none !important; }
            body {
                background: white !important;
                font-family: 'Times New Roman', Times, serif !important;
                padding: 0 !important;
                margin: 0 !important;
                color: black !important;
            }
            main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .print-only { display: block !important; }
            .print-container { width: 100%; padding: 10mm; }
            table.print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px;
            }
            table.print-table th, table.print-table td {
                border: 1.5px solid black !important;
                padding: 6px 8px !important;
                font-size: 13px !important;
                color: black !important;
            }
            table.print-table th {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 12px !important;
            }
            @page { size: A4 landscape; margin: 10mm; }
        }
        .print-only { display: none; }
    </style>

    <!-- Thống báo trạng thái -->
    @if (session()->has('message'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center gap-2 shadow-sm transition-all duration-300 no-print">
            <span class="text-base">✨</span>
            <div class="font-semibold">{{ session('message') }}</div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 mb-4 text-sm text-rose-800 rounded-lg bg-rose-50 border border-rose-100 flex items-center gap-2 shadow-sm transition-all duration-300 no-print">
            <span class="text-base">⚠️</span>
            <div class="font-semibold">{{ session('error') }}</div>
        </div>
    @endif

    <!-- Thanh công cụ tìm kiếm & nút thao tác -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4 no-print">
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <div class="relative w-full lg:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    🔍
                </span>
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Tìm kiếm mã tài sản, tên, bộ phận..." 
                       class="pl-9 pr-4 py-2 w-full text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-slate-50 hover:bg-slate-100/50 transition-colors"
                />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-end">
            <!-- Nút Thêm thiết bị nhanh -->
            <button wire:click="toggleAddAsset" 
                    class="px-3 py-2 text-sm font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg border border-sky-200 transition duration-150 flex items-center gap-1.5 shadow-sm">
                ➕ Thêm thiết bị
            </button>

            <!-- Nút NHẬP EXCEL -->
            <button wire:click="$set('showImportModal', true)" 
                    class="px-3 py-2 text-sm font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg border border-emerald-200 shadow-sm transition duration-150 flex items-center gap-1">
                📥 Nhập Excel
            </button>

            <!-- Nút XUẤT EXCEL -->
            <button wire:click="exportExcel" 
                    class="px-3 py-2 text-sm font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 rounded-lg border border-teal-200 shadow-sm transition duration-150 flex items-center gap-1">
                📤 Xuất Excel
            </button>

            <!-- Nút SỬA -->
            <button wire:click="openEditModal" 
                    @if(count($selectedIds) !== 1) disabled class="px-3 py-2 text-sm font-bold text-slate-400 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200"
                    @else class="px-3 py-2 text-sm font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded-lg border border-amber-200 shadow-sm transition duration-150 flex items-center gap-1"
                    @endif>
                ✏️ Sửa
            </button>

            <!-- Nút XÓA -->
            <button wire:click="deleteSelected" 
                    onclick="confirm('Anh/chị có chắc chắn muốn xóa các thiết bị đang chọn?') || event.stopImmediatePropagation()"
                    @if(empty($selectedIds)) disabled class="px-3 py-2 text-sm font-bold text-slate-400 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200"
                    @else class="px-3 py-2 text-sm font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-200 shadow-sm transition duration-150 flex items-center gap-1"
                    @endif>
                🗑️ Xóa
            </button>

            <!-- Nút IN TICK CHỌN -->
            <button wire:click="printSelected" 
                    @if(empty($selectedIds)) disabled class="px-3 py-2 text-sm font-bold text-slate-400 bg-slate-50 rounded-lg cursor-not-allowed border border-slate-200"
                    @else class="px-3 py-2 text-sm font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 shadow-sm transition duration-150 flex items-center gap-1"
                    @endif>
                🖨️ In tích chọn
            </button>

            <div class="h-6 w-[1px] bg-slate-200 mx-1"></div>

            <!-- Nút Lưu định mức hàng loạt -->
            <button wire:click="saveBoms" 
                    class="px-4 py-2 text-sm font-extrabold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-lg shadow-sm hover:shadow transition duration-150 flex items-center gap-1">
                💾 LƯU ĐỊNH MỨC
            </button>
        </div>
    </div>

    <!-- Form thêm thiết bị nhanh -->
    @if($isAddingAsset)
        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 shadow-inner max-w-2xl transition-all duration-300 no-print">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight mb-4 flex items-center gap-1.5">
                <span class="bg-sky-600 text-white p-1 rounded-md text-xs">📝</span>
                Thêm thiết bị mới vào hệ thống
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Mã tài sản <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="new_asset_code" placeholder="Ví dụ: TS-003" 
                           class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                    @error('new_asset_code') <span class="text-xs text-red-600 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tên thiết bị <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="new_name" placeholder="Ví dụ: Xe ben Howo" 
                           class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                    @error('new_name') <span class="text-xs text-red-600 font-bold block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bộ phận sử dụng</label>
                    <input type="text" wire:model.defer="new_department" placeholder="Ví dụ: Cơ giới, Vận chuyển" 
                           class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                </div>
            </div>

            <div class="flex items-center gap-2 mt-4 justify-end">
                <button wire:click="toggleAddAsset" type="button" class="px-4 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-700 bg-white border border-slate-200 rounded-lg">
                    Hủy bỏ
                </button>
                <button wire:click="addAsset" type="button" class="px-4 py-1.5 text-xs font-black text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow-sm">
                    Xác nhận thêm
                </button>
            </div>
        </div>
    @endif

    <!-- Bảng danh sách định mức tài sản (BOM) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden no-print">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 uppercase text-[11px] font-black tracking-tight border-b border-slate-200 select-none">
                        <th class="py-3.5 px-3 text-center w-10">
                            <input type="checkbox" 
                                   wire:model.live="selectAll" 
                                   class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" 
                            />
                        </th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[90px]">Mã tài sản</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[150px]">Tên thiết bị</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[110px]">Bộ phận</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[120px] text-center">Dầu động cơ 15W40 (Lít)</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[120px] text-center">Nhớt thủy lực AW68 (Lít)</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[130px]">Lọc nhớt động cơ</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[130px]">Lọc thủy lực</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[130px]">Lọc gió</th>
                        <th class="py-3.5 px-4 font-black text-slate-900 min-w-[100px] text-center">Chu kỳ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    @forelse($assets as $asset)
                        <tr class="hover:bg-slate-50/50 transition-colors {{ in_array((string)$asset->id, $selectedIds) ? 'bg-sky-50/40' : '' }}" wire:key="row-{{ $asset->id }}">
                            <!-- Checkbox -->
                            <td class="py-3 px-3 text-center">
                                <input type="checkbox" 
                                       wire:model.live="selectedIds" 
                                       value="{{ $asset->id }}" 
                                       class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" 
                                />
                            </td>
                            
                            <!-- Mã tài sản -->
                            <td class="py-3 px-4 font-bold text-sky-800 select-all uppercase">
                                {{ $asset->asset_code }}
                            </td>
                            
                            <!-- Tên thiết bị -->
                            <td class="py-3 px-4 text-slate-900 font-extrabold uppercase text-[11px] tracking-tight">
                                {{ $asset->name }}
                            </td>
                            
                            <!-- Bộ phận -->
                            <td class="py-3 px-4 text-slate-600 font-semibold">
                                {{ $asset->department ?: '---' }}
                            </td>
                            
                            <!-- Dầu động cơ 15W40 (Lít) -->
                            <td class="py-3 px-4 text-center">
                                <input type="text" 
                                       wire:model.defer="engine_oil_caps.{{ $asset->id }}" 
                                       placeholder="8" 
                                       class="w-16 py-1 px-1.5 text-center text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300"
                                />
                            </td>
                            
                            <!-- Nhớt thủy lực AW68 (Lít) -->
                            <td class="py-3 px-4 text-center">
                                <input type="text" 
                                       wire:model.defer="hydraulic_oil_caps.{{ $asset->id }}" 
                                       placeholder="35" 
                                       class="w-16 py-1 px-1.5 text-center text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300"
                                />
                            </td>
                            
                            <!-- Lọc nhớt động cơ -->
                            <td class="py-3 px-4">
                                <input type="text" 
                                       wire:model.defer="engine_oil_filters.{{ $asset->id }}" 
                                       placeholder="LF-3349" 
                                       class="w-full py-1 px-2.5 text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300 uppercase"
                                />
                            </td>
                            
                            <!-- Lọc thủy lực -->
                            <td class="py-3 px-4">
                                <input type="text" 
                                       wire:model.defer="hydraulic_filters.{{ $asset->id }}" 
                                       placeholder="HF-6710" 
                                       class="w-full py-1 px-2.5 text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300 uppercase"
                                />
                            </td>
                            
                            <!-- Lọc gió -->
                            <td class="py-3 px-4">
                                <input type="text" 
                                       wire:model.defer="air_filters.{{ $asset->id }}" 
                                       placeholder="AF-2555" 
                                       class="w-full py-1 px-2.5 text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300 uppercase"
                                />
                            </td>
                            
                            <!-- Chu kỳ -->
                            <td class="py-3 px-4 text-center">
                                <input type="text" 
                                       wire:model.defer="maintenance_cycles.{{ $asset->id }}" 
                                       placeholder="250 giờ" 
                                       class="w-24 py-1 px-2 text-center text-xs font-bold text-slate-800 bg-slate-50 hover:bg-white focus:bg-white border border-slate-200 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 rounded-md focus:outline-none transition-all placeholder:text-slate-300"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 px-4 text-center text-slate-400 font-bold text-sm bg-slate-25/50">
                                📭 Không tìm thấy mã tài sản hay thiết bị nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($assets->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 no-print bg-slate-50/50">
                {{ $assets->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL NHẬP EXCEL -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto no-print">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" wire:click="$set('showImportModal', false)"></div>
                
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-black text-slate-800 uppercase tracking-tight">📥 Nhập định mức từ Excel/CSV</h3>
                            <button wire:click="$set('showImportModal', false)" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                        </div>
                        
                        <div class="p-3 bg-sky-50 text-sky-850 rounded-lg text-xs font-semibold mb-4 leading-relaxed">
                            💡 Cột trong tệp Excel/CSV cần xếp theo đúng thứ tự:
                            <span class="font-extrabold text-sky-950">Mã tài sản, Tên thiết bị, Bộ phận, Dầu động cơ, Dầu thủy lực, Lọc nhớt, Lọc thủy lực, Lọc gió, Chu kỳ.</span>
                        </div>

                        <div class="mb-4">
                            <p class="text-xs text-slate-500 mb-2">Tải tệp mẫu để điền thông tin nhanh chóng và đúng định dạng:</p>
                            <button wire:click="downloadTemplate" class="text-sky-600 hover:text-sky-800 text-xs font-black underline flex items-center gap-1">
                                📥 Tải tệp tin Excel/CSV mẫu tại đây
                            </button>
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Chọn tệp từ máy tính</label>
                            <input type="file" wire:model="excelFile" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border file:border-slate-200 file:text-xs file:font-bold file:bg-slate-50 file:text-slate-700 hover:file:bg-slate-100" />
                            @error('excelFile') <span class="text-red-500 text-xs font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div wire:loading wire:target="excelFile" class="mt-3 text-xs text-sky-600 font-bold flex items-center gap-1.5">
                            ⏳ Đang tải tệp tin lên hệ thống...
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" wire:click="importExcel" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-xs font-black text-white sm:w-auto transition">
                            Xác nhận nhập
                        </button>
                        <button type="button" wire:click="$set('showImportModal', false)" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-200 shadow-sm px-4 py-2 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto transition">
                            Hủy bỏ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL SỬA THÔNG TIN THIẾT BỊ -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto no-print">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-slate-500 bg-opacity-75 transition-opacity" wire:click="$set('showEditModal', false)"></div>
                
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100">
                    <div class="bg-white px-6 pt-6 pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-black text-slate-800 uppercase tracking-tight">✏️ Sửa thông tin thiết bị</h3>
                            <button wire:click="$set('showEditModal', false)" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Mã tài sản <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="edit_asset_code" class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                                @error('edit_asset_code') <span class="text-xs text-red-600 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tên thiết bị <span class="text-red-500">*</span></label>
                                <input type="text" wire:model.defer="edit_name" class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                                @error('edit_name') <span class="text-xs text-red-600 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bộ phận sử dụng</label>
                                <input type="text" wire:model.defer="edit_department" class="w-full text-sm px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white" />
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-3 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" wire:click="updateAsset" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-sky-600 hover:bg-sky-700 text-xs font-black text-white sm:w-auto transition">
                            Lưu thay đổi
                        </button>
                        <button type="button" wire:click="$set('showEditModal', false)" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-200 shadow-sm px-4 py-2 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 sm:mt-0 sm:w-auto transition">
                            Hủy bỏ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- BẢNG IN ĐỊNH MỨC MÃ TÀI SẢN -->
    <div class="print-only print-container" style="font-family: 'Times New Roman', Times, serif;">
        <!-- Header công ty -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 16px; font-weight: bold; text-transform: uppercase; margin: 0;">CÔNG TY CỔ PHẦN ĐẦU TƯ XÂY DỰNG</h1>
                <p style="font-size: 11px; margin: 4px 0;">BỘ PHẬN QUẢN LÝ THIẾT BỊ & CƠ GIỚI</p>
                <p style="font-size: 11px; margin: 4px 0;">Điện thoại hỗ trợ: 0708091050</p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 11px; margin: 0;">Mẫu số: 03-BOM/TS</p>
                <p style="font-size: 11px; margin: 4px 0; font-style: italic;">Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div style="border-bottom: 1.5px solid black; margin-bottom: 15px;"></div>

        <!-- Tiêu đề phiếu -->
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin: 0;">BẢNG ĐỊNH MỨC VẬT TƯ & BẢO DƯỠNG MÃ TÀI SẢN</h2>
            <p style="font-size: 11px; font-style: italic; margin-top: 4px;">
                (Danh sách thiết bị cơ giới chọn lọc phục vụ công tác bảo dưỡng định kỳ)
            </p>
        </div>

        <!-- Bảng in -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Mã tài sản</th>
                    <th style="width: 22%;">Tên thiết bị</th>
                    <th style="width: 13%;">Bộ phận</th>
                    <th style="width: 11%; text-align: center;">Dầu động cơ (Lít)</th>
                    <th style="width: 11%; text-align: center;">Dầu thủy lực (Lít)</th>
                    <th style="width: 11%;">Lọc nhớt</th>
                    <th style="width: 11%;">Lọc thủy lực</th>
                    <th style="width: 11%;">Lọc gió</th>
                    <th style="width: 10%; text-align: center;">Chu kỳ</th>
                </tr>
            </thead>
            <tbody>
                @foreach(App\Models\Asset::whereIn('id', $selectedIds)->get() as $item)
                    <tr>
                        <td style="font-weight: bold; text-align: center; text-transform: uppercase;">{{ $item->asset_code }}</td>
                        <td style="font-weight: bold; text-transform: uppercase; font-size: 12px !important;">{{ $item->name }}</td>
                        <td>{{ $item->department ?: '---' }}</td>
                        <td style="text-align: center;">{{ $item->engine_oil_cap ?: '---' }}</td>
                        <td style="text-align: center;">{{ $item->hydraulic_oil_cap ?: '---' }}</td>
                        <td>{{ $item->engine_oil_filter ?: '---' }}</td>
                        <td>{{ $item->hydraulic_filter ?: '---' }}</td>
                        <td>{{ $item->air_filter ?: '---' }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $item->maintenance_cycle ?: '---' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Chữ ký -->
        <div style="margin-top: 40px; display: flex; justify-content: space-between; text-align: center;">
            <div style="width: 40%;">
                <p style="font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">Người lập bảng</p>
                <p style="font-size: 10px; font-style: italic; margin: 0;">(Ký, ghi rõ họ tên)</p>
                <div style="height: 50px;"></div>
                <p style="font-weight: bold; text-transform: uppercase;">{{ auth()->user()->name ?? '........................' }}</p>
            </div>
            <div style="width: 40%;">
                <p style="font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">Trưởng bộ phận cơ giới</p>
                <p style="font-size: 10px; font-style: italic; margin: 0;">(Ký, ghi rõ họ tên)</p>
                <div style="height: 50px;"></div>
                <p style="font-weight: bold;">.................................</p>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 400);
        });
    </script>
    @endscript
</div>
