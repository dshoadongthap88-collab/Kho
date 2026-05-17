<div class="space-y-4">
    <!-- Thống báo trạng thái -->
    @if (session()->has('message'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center gap-2 shadow-sm transition-all duration-300">
            <span class="text-base">✨</span>
            <div class="font-semibold">{{ session('message') }}</div>
        </div>
    @endif

    <!-- Thanh công cụ tìm kiếm & nút thao tác -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-wrap items-center justify-between gap-4 no-print">
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-80">
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

        <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <!-- Nút Thêm thiết bị nhanh -->
            <button wire:click="toggleAddAsset" 
                    class="px-4 py-2 text-sm font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg border border-sky-200 transition duration-150 flex items-center gap-1.5 shadow-sm">
                ➕ Thêm thiết bị
            </button>

            <!-- Nút Lưu định mức hàng loạt -->
            <button wire:click="saveBoms" 
                    class="px-5 py-2 text-sm font-extrabold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-lg shadow-sm hover:shadow transition duration-150 flex items-center gap-1.5">
                💾 LƯU ĐỊNH MỨC
            </button>
        </div>
    </div>

    <!-- Form thêm thiết bị nhanh -->
    @if($isAddingAsset)
        <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 shadow-inner max-w-2xl transition-all duration-300">
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
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-800 uppercase text-[11px] font-black tracking-tight border-b border-slate-200 select-none">
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
                        <tr class="hover:bg-slate-50/50 transition-colors" wire:key="row-{{ $asset->id }}">
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
                            <td colspan="9" class="py-12 px-4 text-center text-slate-400 font-bold text-sm bg-slate-25/50">
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
</div>
