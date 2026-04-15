<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-5 rounded-xl shadow-sm border border-slate-200">
        <div>
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <span>🚚</span> Quản lý Giao Hàng
            </h2>
            <p class="text-sm text-slate-500 mt-1">Theo dõi trạng thái giao hàng và thanh toán công nợ</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <!-- Search -->
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm tên khách, mã phiếu..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm text-sm">
            </div>

            <!-- Filter Status -->
            <select wire:model.live="filterStatus" class="border border-gray-300 rounded-lg shadow-sm px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Chờ giao hàng</option>
                <option value="delivered">Đã giao</option>
            </select>
        </div>
    </div>

    <!-- Messages -->
    @if(session('message'))
        <div class="bg-green-100 border border-green-200 text-green-800 rounded-lg p-4 flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('message') }}
        </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="px-6 py-4">Mã Phiếu Xuất</th>
                        <th class="px-6 py-4">Khách hàng</th>
                        <th class="px-6 py-4 text-center">Tình trạng Giao</th>
                        <th class="px-6 py-4 text-center">Thanh toán</th>
                        <th class="px-6 py-4">Ghi chú</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $report)
                        <tr class="hover:bg-slate-50 transition-colors {{ $report->status === 'pending' ? 'bg-red-50/30' : '' }}">
                            <td class="px-6 py-4 font-semibold text-indigo-700">
                                {{ $report->stockOut->code ?? 'N/A' }}
                                <div class="text-[11px] text-slate-400 font-normal mt-1">{{ optional($report->stockOut->created_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $report->customer_name ?: 'Khách lẻ' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($report->status === 'delivered')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        Đã nhận hàng 
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold border border-red-500 text-red-600 animate-pulse bg-red-50">
                                        🚨 Chờ đi giao
                                    </span>
                                @endif
                                @if($report->delivered_at)
                                    <div class="text-[10px] text-slate-500 mt-1">{{ \Carbon\Carbon::parse($report->delivered_at)->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($report->payment_status === 'paid')
                                    <span class="inline-block px-2 text-xs font-semibold rounded bg-emerald-100 text-emerald-800">Đã thanh toán</span>
                                @elseif($report->payment_status === 'debt')
                                    <span class="inline-block px-2 text-xs font-semibold rounded bg-amber-100 text-amber-800">Ghi nợ</span>
                                @else
                                    <span class="inline-block px-2 text-xs font-semibold rounded bg-slate-100 text-slate-600">Chưa TT</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 max-w-[200px] truncate" title="{{ $report->notes }}">
                                {{ $report->notes ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($report->status !== 'delivered')
                                    <button wire:click="openConfirmModal({{ $report->id }})" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1.5 rounded shadow-sm transition">
                                        Xác nhận
                                    </button>
                                @else
                                    @if($report->photo_path)
                                        <a href="{{ Storage::url($report->photo_path) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs font-semibold underline flex items-center justify-center gap-1">
                                            <span>📷</span> Xem ảnh
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">Không có ảnh</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Không tìm thấy báo cáo giao hàng nào</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $reports->links() }}
        </div>
    </div>

    <!-- Confirm Modal -->
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showConfirmModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg sm:w-full">
                    <div class="bg-blue-50 px-4 py-3 border-b border-blue-100">
                        <h3 class="text-lg leading-6 font-bold text-blue-900 flex items-center gap-2">
                            <span>✅</span> Xác nhận Giao Hàng Thành Công
                        </h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        
                        <!-- Upload Ảnh -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Ảnh Minh Chứng (Bắt buộc / Tùy chọn) <span class="text-blue-500">📷</span></label>
                            <input type="file" wire:model="photo" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 rounded-lg p-1">
                            <div wire:loading wire:target="photo" class="text-xs text-blue-500 mt-1">Đang tải ảnh lên...</div>
                            @if ($photo)
                                <div class="mt-2 rounded border border-slate-200 overflow-hidden w-24 relative">
                                    <img src="{{ $photo->temporaryUrl() }}" class="w-full h-auto object-cover">
                                </div>
                            @endif
                            @error('photo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Trạng thái Thanh toán -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Trạng thái Thanh toán / Công Nợ</label>
                            <select wire:model="paymentStatus" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="paid">✅ Tiền mặt / Đã thanh toán</option>
                                <option value="bank_transfer">🏦 Chuyển khoản công ty</option>
                                <option value="debt">📝 Ghi Nợ Khách Hàng</option>
                                <option value="unpaid">⏳ Chưa thanh toán (chờ xử lý)</option>
                            </select>
                            @error('paymentStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Ghi chú -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Ghi chú thêm</label>
                            <textarea wire:model="notes" rows="3" class="w-full rounded-lg border-slate-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Người thân nhận hộ, tiền chuyển khoản, v.v..."></textarea>
                            @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-xl">
                        <button type="button" wire:click="saveCompletion" wire:loading.attr="disabled" class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm transition items-center gap-2">
                            Xác nhận hoàn thành
                            <div wire:loading wire:target="saveCompletion" class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin ml-2"></div>
                        </button>
                        <button type="button" wire:click="$set('showConfirmModal', false)" class="inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none sm:w-auto sm:text-sm">
                            Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
