<div>
    <!-- Header & Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm mã hoặc tên vật tư..." class="w-full md:w-64 rounded-lg border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
            <select wire:model.live="statusFilter" class="w-full md:w-48 rounded-lg border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="pending">Đề xuất (Chờ duyệt)</option>
                <option value="ordered">Đã đặt hàng</option>
                <option value="unreceived">Chưa giao</option>
                <option value="partial">Giao thiếu</option>
                <option value="completed">Đủ hàng (Hoàn thành)</option>
            </select>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
            <button wire:click="printSelected" class="flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg font-bold shadow transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                In phiếu
                @if(count($selected) > 0)
                    <span class="bg-white text-slate-800 text-xs px-2 py-0.5 rounded-full ml-1">{{ count($selected) }}</span>
                @endif
            </button>
            <button wire:click="openAddModal" class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-bold shadow transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Thêm đề xuất
            </button>
            <button wire:click="autoSuggest" wire:loading.attr="disabled" class="flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold shadow transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Tự động đề xuất mua
            </button>
            <button wire:click="closeDay" wire:confirm="Bạn có chắc chắn muốn chốt sổ và dọn dẹp bảng? Dữ liệu sẽ được lưu vào file Lịch sử và chuyển sang trạng thái đã lưu trữ." wire:loading.attr="disabled" class="flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-bold shadow transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                Chốt sổ cuối ngày
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-50 text-emerald-700 p-4 rounded-lg border border-emerald-200 font-medium">
            {{ session('message') }}
        </div>
    @endif

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-3">Ngày ĐX</th>
                        <th class="px-4 py-3">Mã & Tên Vật Tư</th>
                        <th class="px-4 py-3 text-right">Tồn Kho</th>
                        <th class="px-4 py-3 text-right">SL Đề Xuất</th>
                        <th class="px-4 py-3 text-right">Đã Giao</th>
                        <th class="px-4 py-3 text-right text-rose-600">Còn Thiếu</th>
                        <th class="px-4 py-3 text-center">Trạng Thái</th>
                        <th class="px-4 py-3">Ghi Chú</th>
                        <th class="px-4 py-3 text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($plans as $plan)
                        @php
                            $missing = $plan->proposed_quantity - $plan->delivered_quantity;
                            $missing = $missing > 0 ? $missing : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" wire:model.live="selected" value="{{ $plan->id }}" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $plan->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs mr-1">{{ $plan->product?->code ?? 'N/A' }}</span>
                                {{ $plan->product?->name ?? 'Vật tư đã bị xóa' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-600">
                                {{ number_format($plan->product?->inventory?->quantity ?? 0, 0) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($plan->status !== 'completed')
                                    <input type="number" 
                                           value="{{ $plan->proposed_quantity }}" 
                                           wire:change="updateProposedQuantity({{ $plan->id }}, $event.target.value)"
                                           class="w-20 text-right p-1 text-sm border-slate-300 rounded font-bold text-slate-700 focus:ring-sky-500 focus:border-sky-500">
                                @else
                                    <span class="font-bold text-slate-700">{{ number_format($plan->proposed_quantity, 0) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ number_format($plan->delivered_quantity, 0) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-rose-600">{{ number_format($missing, 0) }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($plan->status === 'pending')
                                    <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">Đề xuất</span>
                                @elseif($plan->status === 'ordered')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-bold">Đã đặt</span>
                                @elseif($plan->status === 'unreceived')
                                    <span class="px-2 py-1 bg-rose-100 text-rose-600 rounded-full text-xs font-bold">Chưa giao</span>
                                @elseif($plan->status === 'partial')
                                    <span class="px-2 py-1 bg-amber-100 text-amber-600 rounded-full text-xs font-bold">Giao thiếu</span>
                                @else
                                    <span class="px-2 py-1 bg-emerald-100 text-emerald-600 rounded-full text-xs font-bold">Đủ hàng</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-xs truncate" title="{{ $plan->notes }}">
                                {{ $plan->notes }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                @if($plan->status === 'pending')
                                    <button wire:click="placeOrder({{ $plan->id }})" class="px-2 py-1 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded text-xs font-bold transition">Đặt hàng</button>
                                @endif
                                
                                @if($plan->status !== 'completed')
                                    <button wire:click="openUpdateModal({{ $plan->id }})" class="px-2 py-1 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded text-xs font-bold transition">Cập nhật giao</button>
                                @endif

                                <button wire:click="delete({{ $plan->id }})" wire:confirm="Bạn có chắc chắn muốn xóa?" class="px-2 py-1 text-rose-600 hover:bg-rose-50 rounded text-xs transition">Xóa</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-500 font-medium">Chưa có kế hoạch mua hàng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-200">
            {{ $plans->links() }}
        </div>
    </div>

    <!-- Modal Cập nhật giao hàng (Sử dụng x-data của AlpineJS cho modal đơn giản) -->
    <div x-data="{ show: false }" 
         @open-modal.window="if ($event.detail[0] === 'update-delivery-modal') show = true"
         @close-modal.window="if ($event.detail[0] === 'update-delivery-modal') show = false"
         x-show="show" 
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-75" aria-hidden="true" @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 mb-4">Cập nhật số lượng giao hàng</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Số lượng đã giao</label>
                            <input type="number" wire:model="delivered_quantity" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                            @error('delivered_quantity') <span class="text-rose-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Ngày dự kiến nhận (nếu chưa giao đủ)</label>
                            <input type="date" wire:model="expected_delivery_date" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                        </div>
                    </div>
                </div>
                <div class="mt-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="saveDeliveryUpdate" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-bold text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Lưu cập nhật
                    </button>
                    <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Thêm Đề Xuất (Thủ công) -->
    <div x-data="{ show: false }" 
         @open-modal.window="if ($event.detail[0] === 'add-plan-modal') show = true"
         @close-modal.window="if ($event.detail[0] === 'add-plan-modal') show = false"
         x-show="show" 
         class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-75" aria-hidden="true" @click="show = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 mb-4">Thêm đề xuất mua hàng thủ công</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Vật tư / Sản phẩm <span class="text-rose-500">*</span></label>
                            <select wire:model="new_product_id" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                                <option value="">-- Chọn vật tư --</option>
                                @foreach($allProducts as $product)
                                    <option value="{{ $product->id }}">[{{ $product->code }}] {{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('new_product_id') <span class="text-rose-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Số lượng <span class="text-rose-500">*</span></label>
                            <input type="number" wire:model="new_quantity" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500">
                            @error('new_quantity') <span class="text-rose-600 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Ghi chú</label>
                            <textarea wire:model="new_notes" rows="2" class="w-full rounded-lg border-slate-300 focus:border-sky-500 focus:ring-sky-500"></textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="saveNewPlan" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Lưu đề xuất
                    </button>
                    <button type="button" @click="show = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
