<div class="p-2 w-full" x-data="{
    selected: @entangle('selectedTransfers').live,
    toggle(id) {
        if (!Array.isArray(this.selected)) {
            this.selected = [];
        }
        if (this.selected.includes(id)) {
            this.selected = this.selected.filter(i => i !== id);
        } else {
            this.selected.push(id);
        }
    },
    selectAll(checked) {
        if (checked) {
            $wire.selectAll(true);
        } else {
            this.selected = [];
            $wire.selectAll(false);
        }
    }
}">
    {{-- Grid Layout --}}
    <div class="grid grid-cols-1 gap-2">

        {{-- Table Card --}}
        <div class="space-y-6">

            {{-- Header Card --}}
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white rounded-2xl p-2 shadow-lg relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-2">
                <div class="absolute inset-0 bg-white/5 backdrop-blur-3xl -z-10"></div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight">🚚 ĐIỀU CHUYỂN KHO LIÊN CHI NHÁNH</h1>
                    <p class="text-sm text-indigo-100 mt-1">Lập phiếu, chuyển đổi và theo dõi hàng hóa tự động trừ tồn kho giữa các Dự án</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('warehouse.stock-transfer.create') }}"
                        class="bg-white text-indigo-800 px-5 py-2.5 rounded-xl font-bold text-sm shadow-md hover:bg-indigo-50 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                        <span>➕</span> Tạo Phiếu Chuyển Kho
                    </a>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-2 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 text-sm shadow-sm">
                    <span class="text-xl">✅</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-2 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3 text-sm shadow-sm">
                    <span class="text-xl">❌</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Filter & Search Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-2 shadow-sm flex items-center gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-sm">🔍</span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Tìm kiếm nhanh theo mã phiếu điều chuyển..."
                        class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all">
                </div>
            </div>

            {{-- Action Bar (Hành động hàng loạt) --}}
            <div class="bg-indigo-600 rounded-2xl p-3 shadow-xl flex items-center justify-between mb-4 transition-all" :class="selected.length > 0 ? 'opacity-100' : 'opacity-70'">
                <div class="flex items-center gap-2 text-white text-sm font-bold">
                    <span class="bg-white text-indigo-600 px-2 py-0.5 rounded-full text-xs font-black" x-text="selected.length"></span>
                    <span x-text="selected.length > 0 ? 'phiếu đã được chọn' : 'Hãy chọn phiếu để thực hiện thao tác'"></span>
                </div>
                <div class="flex gap-2">
                    <button wire:click="printSelected" type="button"
                        :disabled="selected.length === 0"
                        class="bg-emerald-500 hover:bg-emerald-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-xl font-bold text-xs shadow-md transition-all flex items-center gap-2">
                        <span>🖨️</span> In lần lượt
                    </button>
                    <button wire:click="deleteSelected" onclick="return confirm('Bạn có chắc chắn muốn xóa các phiếu đã chọn?')" type="button"
                        :disabled="selected.length === 0"
                        class="bg-rose-500 hover:bg-rose-600 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-xl font-bold text-xs shadow-md transition-all flex items-center gap-2">
                        <span>🗑️</span> Xóa đã chọn
                    </button>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-indigo-50/50 text-indigo-900 border-b border-gray-100 font-bold">
                            <tr>
                                <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">Mã Phiếu</th>
                                <th class="px-5 py-4 text-center font-bold uppercase tracking-wider text-xs">Từ Kho</th>
                                <th class="px-5 py-4 text-center font-bold uppercase tracking-wider text-xs">Đến Kho</th>
                                <th class="px-5 py-4 text-center font-bold uppercase tracking-wider text-xs">Mặt Hàng</th>
                                <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">Ngày Chuyển</th>
                                <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">Người Lập</th>
                                <th class="px-5 py-4 text-left font-bold uppercase tracking-wider text-xs">Trạng Thái</th>
                                <th class="px-5 py-4 text-center font-bold uppercase tracking-wider text-xs">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transfers as $transfer)
                                <tr wire:key="transfer-{{ $transfer->id }}" class="hover:bg-indigo-50/30 transition-all" :class="selected.includes('{{ $transfer->id }}') ? 'bg-indigo-50/50' : ''">
                                    <td class="px-5 py-4 cursor-pointer hover:bg-indigo-100 transition-colors group">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox"
                                                   @click.stop="toggle('{{ $transfer->id }}')"
                                                   :checked="selected.includes('{{ $transfer->id }}')"
                                                   class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                            <div class="font-mono font-black text-indigo-700 text-xs tracking-tight group-hover:text-indigo-900 cursor-pointer" wire:click="viewDetail({{ $transfer->id }})">
                                                {{ $transfer->transfer_code }}
                                            </div>
                                        </div>
                                        @if($transfer->note)
                                            <div class="text-xs text-gray-400 mt-1 italic max-w-xs truncate">📝 {{ $transfer->note }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 text-xs font-black border border-amber-200">
                                            🏡 {{ $transfer->fromProject?->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-sky-50 text-sky-800 text-xs font-black border border-sky-200">
                                            🏡 {{ $transfer->toProject?->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center font-bold text-gray-800">
                                        <span class="bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full text-xs font-black">
                                            {{ $transfer->items->count() }} mặt hàng
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600 font-medium">
                                        {{ $transfer->transfer_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-5 py-4 text-gray-600 font-medium">
                                        {{ $transfer->creator?->name ?? '—' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($transfer->status === 'completed')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                ✔ Đã Trừ Tồn
                                            </span>
                                        @elseif($transfer->status === 'rejected')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-black">
                                                ❌ Từ Chối
                                            </span>
                                        @elseif($transfer->status === 'reverted')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-black">
                                                ↩️ Đã Hủy
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-black">
                                                ⏳ Đang Xử Lý
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <button wire:click="viewDetail({{ $transfer->id }})" type="button" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="Xem Chi Tiết">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-16 text-gray-400">
                                        <div class="text-5xl mb-3">📦</div>
                                        <div class="text-xs font-semibold">Chưa có giao dịch chuyển kho nào được thực hiện</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($transfers->hasPages())
                    <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                        {{ $transfers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $this->selectedTransferDetail)
        <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-data @keydown.escape.window="$wire.closeDetailModal()">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$wire.closeDetailModal()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight" id="modal-title">Chi Tiết Phiếu Chuyển Kho</h3>
                                <p class="text-sm text-gray-500">Mã phiếu: <span class="font-mono font-bold text-indigo-600">{{ $this->selectedTransferDetail->transfer_code }}</span></p>
                            </div>
                            <button wire:click="closeDetailModal()" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-6 bg-gray-50 p-2 rounded-xl">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400">Ngày chuyển</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $this->selectedTransferDetail->transfer_date?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400">Người lập</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $this->selectedTransferDetail->creator?->name ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400">Từ Chi nhánh</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-800 text-xs font-black border border-amber-200">
                                        🏡 {{ $this->selectedTransferDetail->fromProject?->name ?? '—' }}
                                    </span>
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase font-bold text-gray-400">Đến Chi nhánh</p>
                                <p class="text-sm font-semibold text-gray-800">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-sky-50 text-sky-800 text-xs font-black border border-sky-200">
                                        🏡 {{ $this->selectedTransferDetail->toProject?->name ?? '—' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        @if($this->selectedTransferDetail->note)
                            <div class="mb-6">
                                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Ghi chú</p>
                                <p class="text-sm text-gray-700 italic bg-yellow-50 p-3 rounded-lg border border-yellow-100">{{ $this->selectedTransferDetail->note }}</p>
                            </div>
                        @endif

                        @if($this->selectedTransferDetail->status === 'rejected' && $this->selectedTransferDetail->reject_reason)
                            <div class="mb-6">
                                <p class="text-[10px] uppercase font-bold text-rose-400 mb-1">Lý do từ chối</p>
                                <p class="text-sm text-rose-700 italic bg-rose-50 p-3 rounded-lg border border-rose-100">{{ $this->selectedTransferDetail->reject_reason }}</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 mb-2">Danh sách mặt hàng</p>
                            <div class="overflow-x-auto border border-gray-100 rounded-xl">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-gray-50 text-gray-600 font-bold">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Mã Vật Tư</th>
                                            <th class="px-4 py-2 text-left">Tên Vật Tư</th>
                                            <th class="px-4 py-2 text-center">Số Lượng</th>
                                            <th class="px-4 py-2 text-center">ĐVT</th>
                                            <th class="px-4 py-2 text-center">Vị trí</th>
                                            <th class="px-4 py-2 text-left">Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($this->selectedTransferDetail->items ?? [] as $item)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-2 font-mono font-bold">{{ $item->product?->code ?? '—' }}</td>
                                                <td class="px-4 py-2">{{ $item->product?->name ?? '—' }}</td>
                                                <td class="px-4 py-2 text-center font-bold">{{ $item->quantity }}</td>
                                                <td class="px-4 py-2 text-center">{{ $item->product?->unit ?? '—' }}</td>
                                                <td class="px-4 py-2 text-center">{{ $item->location ?? '—' }}</td>
                                                <td class="px-4 py-2">{{ $item->note ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col gap-3" x-data="{ showReject: false }">
                        <div x-show="showReject" class="w-full mb-2 p-3 border border-rose-200 bg-rose-50 rounded-lg">
                            <label class="block text-sm font-bold text-rose-800 mb-2">Lý do từ chối (bắt buộc):</label>
                            <select wire:model="rejectReason" class="w-full border border-rose-300 rounded-md shadow-sm focus:border-rose-500 focus:ring-rose-500 text-sm mb-2 p-2">
                                <option value="">-- Chọn lý do --</option>
                                <option value="Sai số lượng">Sai số lượng</option>
                                <option value="Sai vật tư">Sai vật tư</option>
                                <option value="Không đúng kho nhận">Không đúng kho nhận</option>
                                <option value="Khác">Khác</option>
                            </select>
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="showReject = false" class="px-3 py-1.5 text-xs font-bold text-gray-600 bg-white border border-gray-300 rounded-md">Đóng</button>
                                <button type="button" wire:click="submitReject()" class="px-3 py-1.5 text-xs font-bold text-white bg-rose-600 rounded-md hover:bg-rose-700">Xác nhận Từ chối</button>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 w-full" x-show="!showReject">
                            @if($this->selectedTransferDetail->status === 'pending' && $this->selectedTransferDetail->to_project_id == $currentHouse)
                                <button type="button" @click="showReject = true" class="inline-flex justify-center items-center px-4 py-2 text-sm font-bold text-rose-600 bg-white border border-rose-600 rounded-md shadow-sm hover:bg-rose-50 focus:outline-none">
                                    ❌ Từ chối
                                </button>
                                <button type="button" wire:click="confirmTransfer({{ $this->selectedTransferDetail->id }})" class="inline-flex justify-center items-center px-4 py-2 text-sm font-bold text-white bg-green-600 border border-green-600 rounded-md shadow-sm hover:bg-green-700 focus:outline-none">
                                    ✅ Xác nhận nhận hàng
                                </button>
                            @endif
                            @if($this->selectedTransferDetail->status === 'completed' && $this->selectedTransferDetail->to_project_id == $currentHouse)
                                <button type="button" wire:click="revertTransfer({{ $this->selectedTransferDetail->id }})" onclick="return confirm('Hành động này sẽ TRỪ lại tồn kho của bạn và CỘNG lại cho kho gửi. Bạn có chắc chắn?')" class="inline-flex justify-center items-center px-4 py-2 text-sm font-bold text-orange-600 bg-white border border-orange-600 rounded-md shadow-sm hover:bg-orange-50 focus:outline-none">
                                    ↩️ Hủy nhập (Hoàn tác)
                                </button>
                            @endif
                            @if($this->selectedTransferDetail->status === 'pending')
                            <button type="button" wire:click="deleteTransfer({{ $this->selectedTransferDetail->id }})" onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu chuyển kho này?')" class="inline-flex justify-center items-center px-4 py-2 text-sm font-bold text-white bg-red-600 border border-red-600 rounded-md shadow-sm hover:bg-red-700 focus:outline-none">
                                🗑️ Xóa
                            </button>
                            @endif
                            <a href="{{ route('warehouse.stock-transfer.print', $this->selectedTransferDetail->id) }}" target="_blank" class="inline-flex justify-center items-center px-4 py-2 text-sm font-bold text-indigo-700 bg-indigo-100 border border-indigo-200 rounded-md shadow-sm hover:bg-indigo-200 focus:outline-none">
                                🖨️ In
                            </a>
                            <button type="button" wire:click="closeDetailModal()" class="inline-flex justify-center items-center px-4 py-2 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none">
                                Đóng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    window.addEventListener('open-print-window', event => {
        window.open(event.detail.url, '_blank');
    });
</script>