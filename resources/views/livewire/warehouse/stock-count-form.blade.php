<div>
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-3 shadow-sm">
        <span class="text-xl">✅</span> <span class="font-semibold text-sm">{!! session('success') !!}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-center gap-3 shadow-sm">
        <span class="text-xl">❌</span> <span class="font-semibold text-sm">{{ session('error') }}</span>
    </div>
    @endif
    @if(session('info'))
    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl flex items-center gap-3 shadow-sm">
        <span class="text-xl">ℹ️</span> <span class="font-semibold text-sm">{{ session('info') }}</span>
    </div>
    @endif

    <div class="flex gap-2 mb-6 no-print">
        <button wire:click="$set('activeTab', 'stocktake')"
            class="px-5 py-2 rounded-lg text-sm font-bold transition {{ $activeTab === 'stocktake' ? 'bg-indigo-700 text-white shadow-md' : 'bg-white text-gray-600 border hover:bg-gray-50' }}">
            📋 Phiếu kiểm kê
        </button>
        <button wire:click="$set('activeTab', 'daily')"
            class="px-5 py-2 rounded-lg text-sm font-bold transition {{ $activeTab === 'daily' ? 'bg-orange-600 text-white shadow-md' : 'bg-white text-gray-600 border hover:bg-gray-50' }}">
            ☀️ Kiểm kê hàng ngày
        </button>
    </div>

    {{-- ======================== TAB: KIỂM KÊ ======================== --}}
    @if($activeTab === 'stocktake')
    <div>
        {{-- Nếu đang có phiếu kiểm kê đang mở --}}
        @if($currentCount)
        <div class="bg-white rounded-xl shadow border mb-6">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <div>
                    <h2 class="text-base font-black text-indigo-800 uppercase">📋 Phiếu kiểm kê: {{ $currentCount->code }}</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Nhập số lượng kiểm đếm thực tế vào cột "Thực tế". Hệ thống sẽ tính chênh lệch tự động.</p>
                </div>
                <div class="flex gap-2 no-print">
                    <button onclick="window.print()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-black transition cursor-pointer flex items-center gap-1">
                        🖨️ In phiếu
                    </button>
                    <button wire:click="confirmStockCount({{ $currentCount->id }})"
                        wire:confirm="Xác nhận hoàn tất kiểm kê? Hệ thống sẽ tự động điều chỉnh tồn kho theo số liệu thực tế."
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-black transition cursor-pointer">
                        ✅ Xác nhận
                    </button>
                    <button wire:click="cancelStockCount({{ $currentCount->id }})"
                        wire:confirm="Hủy phiếu kiểm kê này?"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-xs font-bold transition cursor-pointer">
                        ✖ Hủy phiếu
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Mã SP</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Tên sản phẩm</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Tồn hệ thống</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-yellow-600 uppercase">Thực tế (Nhập)</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Chênh lệch</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase no-print">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($currentCount->items as $item)
                        <tr class="hover:bg-gray-50 {{ $item->difference != 0 && $item->actual_quantity !== null ? ($item->difference < 0 ? 'bg-red-50' : 'bg-green-50') : '' }}">
                            <td class="px-4 py-2 text-xs font-bold text-indigo-700">{{ $item->product->code ?? '-' }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-800">{{ $item->product->name ?? '-' }}</td>
                            <td class="px-4 py-2 text-center text-sm font-black text-gray-700">{{ number_format($item->system_quantity) }}</td>
                            <td class="px-4 py-2 text-center no-print">
                                <input type="number" 
                                    value="{{ $item->actual_quantity }}"
                                    wire:change="updateActualQty({{ $item->id }}, $event.target.value)"
                                    class="w-24 text-center border border-yellow-300 rounded-lg px-2 py-1 text-sm font-bold focus:ring-yellow-500 focus:border-yellow-500 bg-yellow-50"
                                    placeholder="0">
                            </td>
                            <td class="px-4 py-2 text-center text-sm font-black 
                                {{ $item->difference < 0 ? 'text-red-600' : ($item->difference > 0 ? 'text-green-600' : 'text-gray-400') }}">
                                @if($item->actual_quantity !== null)
                                    {{ $item->difference > 0 ? '+' : '' }}{{ number_format($item->difference) }}
                                @else
                                    <span class="text-gray-300 text-xs">Chưa nhập</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-400 no-print">{{ $item->note }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        {{-- Form tạo phiếu kiểm kê mới --}}
        <div class="bg-white rounded-xl border shadow-sm p-5 mb-6 no-print">
            <h2 class="text-sm font-black text-gray-700 uppercase mb-3">➕ Tạo phiếu kiểm kê mới</h2>
            <p class="text-xs text-gray-400 mb-3">Hệ thống sẽ tự động tải toàn bộ danh sách sản phẩm và số tồn kho hiện tại vào phiếu kiểm kê để bạn đối chiếu thực tế.</p>
            <div class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Ghi chú phiếu kiểm kê</label>
                    <input type="text" wire:model="countNote" placeholder="VD: Kiểm kê tháng 5/2026..." class="w-full rounded-lg border-gray-200 shadow-sm text-sm focus:ring-indigo-500">
                </div>
                <button wire:click="createNewStockCount" wire:loading.attr="disabled"
                    class="px-6 py-2 bg-indigo-700 hover:bg-indigo-800 text-white rounded-lg text-sm font-black transition shadow cursor-pointer">
                    <span wire:loading.remove wire:target="createNewStockCount">📋 Tạo phiếu kiểm kê</span>
                    <span wire:loading wire:target="createNewStockCount">Đang tải...</span>
                </button>
            </div>
        </div>
        @endif

        {{-- Lịch sử phiếu kiểm kê --}}
        <div class="bg-white rounded-xl shadow border overflow-hidden">
            <div class="px-5 py-3 border-b flex flex-wrap justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-bold text-gray-700">Lịch sử phiếu kiểm kê</h3>
                    <div class="flex items-center gap-1 ml-4 no-print" wire:key="bulk-actions-toolbar">
                        <span class="text-[10px] font-bold {{ count($selectedStockCounts) > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400' }} px-2 py-1 rounded-full transition-colors">
                            Đã chọn {{ count($selectedStockCounts) }}
                        </span>
                        
                        <button type="button" wire:click="bulkPrint" 
                            {{ count($selectedStockCounts) == 0 ? 'disabled' : '' }}
                            class="p-1.5 rounded-lg transition-all {{ count($selectedStockCounts) > 0 ? 'bg-blue-50 text-blue-600 hover:bg-blue-100 cursor-pointer shadow-sm' : 'bg-gray-50 text-gray-300 cursor-not-allowed opacity-50' }}" 
                            title="In các phiếu đã chọn">
                            🖨️
                        </button>

                        <button type="button" wire:click="bulkDelete" 
                            wire:confirm="Bạn có chắc chắn muốn XÓA các phiếu đã chọn?"
                            {{ count($selectedStockCounts) == 0 ? 'disabled' : '' }}
                            class="p-1.5 rounded-lg transition-all {{ count($selectedStockCounts) > 0 ? 'bg-red-50 text-red-600 hover:bg-red-100 cursor-pointer shadow-sm' : 'bg-gray-50 text-gray-300 cursor-not-allowed opacity-50' }}" 
                            title="Xóa các phiếu đã chọn">
                            🗑️
                        </button>

                        @if(count($selectedStockCounts) === 1)
                        @php $firstId = reset($selectedStockCounts); @endphp
                        <button type="button" wire:click="editStockCount({{ $firstId }})" 
                            class="p-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-all cursor-pointer shadow-sm animate-bounce-short" 
                            title="Tiếp tục chỉnh sửa phiếu này">
                            ✏️
                        </button>
                        @endif
                    </div>
                </div>
                <input type="text" wire:model.live="listSearch" placeholder="Tìm mã phiếu..." class="rounded-lg border-gray-200 shadow-sm text-xs focus:ring-indigo-500 w-48">
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-center w-10">
                            <input type="checkbox" 
                                wire:click="toggleSelectAll([{{ implode(',', $stockCounts->pluck('id')->toArray()) }}])"
                                {{ count($selectedStockCounts) >= count($stockCounts) && count($selectedStockCounts) > 0 ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Mã phiếu</th>
                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Trạng thái</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Ghi chú</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Người tạo</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Ngày tạo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($stockCounts as $sc)
                    @php
                        $statusMap = [
                            'pending' => ['label' => '⏳ Đang kiểm', 'class' => 'bg-yellow-100 text-yellow-700'],
                            'completed' => ['label' => '✅ Hoàn thành', 'class' => 'bg-green-100 text-green-700'],
                            'cancelled' => ['label' => '✖ Đã hủy', 'class' => 'bg-red-100 text-red-700'],
                        ];
                        $s = $statusMap[$sc->status] ?? ['label' => $sc->status, 'class' => 'bg-gray-100 text-gray-600'];
                    @endphp
                    <tr wire:key="sc-row-{{ $sc->id }}" class="hover:bg-gray-50 transition-colors {{ in_array($sc->id, $selectedStockCounts) ? 'bg-indigo-50/50' : '' }}">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model.live="selectedStockCounts" value="{{ $sc->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-indigo-700">{{ $sc->code }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $s['class'] }}">{{ $s['label'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $sc->note }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">👤 {{ $sc->creator->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $sc->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 italic text-sm">Chưa có phiếu kiểm kê nào. Tạo phiếu đầu tiên để bắt đầu!</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $stockCounts->links() }}</div>
        </div>
    </div>
    @endif

    {{-- ======================== TAB: KIỂM KÊ HÀNG NGÀY ======================== --}}
    @if($activeTab === 'daily')
    <div class="bg-white rounded-xl border shadow-sm p-8 text-center max-w-2xl mx-auto my-12">
        <div class="w-20 h-20 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">☀️</div>
        <h2 class="text-2xl font-black text-gray-800 uppercase mb-4">Kiểm kê hàng ngày</h2>
        <p class="text-gray-500 mb-8 leading-relaxed">
            Hệ thống sẽ tự động chọn ngẫu nhiên <b>10 vật tư</b> dựa trên vị trí kho và quy tắc chống trùng lặp (không chọn lại vật tư đã kiểm trong 7 ngày qua).
        </p>
        <button wire:click="createDailyStockCount" wire:loading.attr="disabled"
            class="px-8 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-black shadow-lg transition-all transform hover:scale-105 active:scale-95 flex items-center gap-3 mx-auto cursor-pointer">
            <span wire:loading.remove wire:target="createDailyStockCount">📋 Tạo 10 mã kiểm kê ngay</span>
            <span wire:loading wire:target="createDailyStockCount" class="flex items-center gap-2">
                <span class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Đang chọn mã...
            </span>
        </button>
    </div>
    @endif
</div>

{{-- Print Styles --}}
<style>
    @media print {
        .no-print, header, nav, aside, footer { display: none !important; }
        body { background: white !important; margin: 0; padding: 20px; }
        .shadow, .border { box-shadow: none !important; border: 1px solid #eee !important; }
        table { width: 100% !important; border-collapse: collapse !important; }
        th, td { border: 1px solid #ddd !important; padding: 8px !important; text-align: left; }
        th { background-color: #f9f9f9 !important; -webkit-print-color-adjust: exact; }
        .bg-blue-50, .bg-green-50, .bg-indigo-50 { background-color: white !important; }
        .text-indigo-600, .text-blue-600 { color: black !important; }
    }
</style>
