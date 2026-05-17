<div class="p-6 max-w-7xl mx-auto" wire:poll.5s>
    {{-- Grid Layout for Split Screen --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left 2 Columns: Transfer History --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Header Card --}}
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white rounded-2xl p-6 shadow-lg relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="absolute inset-0 bg-white/5 backdrop-blur-3xl -z-10"></div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight">🚚 ĐIỀU CHUYỂN KHO LIÊN CHI NHÁNH</h1>
                    <p class="text-sm text-indigo-100 mt-1">Lập phiếu, chuyển đổi và theo dõi hàng hóa tự động trừ tồn kho giữa các Dự án</p>
                </div>
                <a href="{{ route('warehouse.stock-transfer.create') }}"
                    class="bg-white text-indigo-800 px-5 py-2.5 rounded-xl font-bold text-sm shadow-md hover:bg-indigo-50 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                    <span>➕</span> Tạo Phiếu Chuyển Kho
                </a>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center gap-3 text-sm shadow-sm">
                    <span class="text-xl">✅</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center gap-3 text-sm shadow-sm">
                    <span class="text-xl">❌</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Filter & Search Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm flex items-center gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 text-sm">🔍</span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                        placeholder="Tìm kiếm nhanh theo mã phiếu điều chuyển..."
                        class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition-all">
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transfers as $transfer)
                                <tr class="hover:bg-indigo-50/30 transition-all">
                                    <td class="px-5 py-4">
                                        <div class="font-mono font-black text-indigo-700 text-sm tracking-tight">{{ $transfer->transfer_code }}</div>
                                        @if($transfer->note)
                                            <div class="text-xs text-gray-400 mt-1 italic max-w-xs truncate">📝 {{ $transfer->note }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 text-xs font-black border border-amber-200">
                                            🏡 {{ $transfer->from_house == 1 ? 'Hóc Môn' : ($transfer->from_house == 2 ? 'Hậu Nghĩa' : ($transfer->from_house == 3 ? 'Cần Giờ' : 'Số 4')) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-sky-50 text-sky-800 text-xs font-black border border-sky-200">
                                            🏡 {{ $transfer->to_house == 1 ? 'Hóc Môn' : ($transfer->to_house == 2 ? 'Hậu Nghĩa' : ($transfer->to_house == 3 ? 'Cần Giờ' : 'Số 4')) }}
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
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-black">
                                                ⏳ Đang Xử Lý
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-16 text-gray-400">
                                        <div class="text-5xl mb-3">📦</div>
                                        <div class="text-sm font-semibold">Chưa có giao dịch chuyển kho nào được thực hiện</div>
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

        {{-- Right 1 Column: Chat & Real-Time Notification Board --}}
        <div class="lg:col-span-1 flex flex-col bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-[500px]">
            
            {{-- Chat Header --}}
            <div class="bg-indigo-900 text-white p-4 flex items-center justify-between shadow-md">
                <div class="flex items-center gap-2.5">
                    <div class="relative">
                        <span class="text-2xl">💬</span>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-indigo-900 rounded-full animate-ping"></span>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-indigo-900 rounded-full"></span>
                    </div>
                    <div>
                        <h2 class="font-black text-sm tracking-tight text-white">CHAT & THÔNG BÁO CHUNG</h2>
                        <p class="text-[10px] text-indigo-200">Liên lạc và cập nhật điều chuyển giữa các dự án</p>
                    </div>
                </div>
                <div class="text-xs bg-indigo-800 text-indigo-200 px-2 py-0.5 rounded font-mono">
                    {{ session('current_house', 1) == 2 ? 'Hậu Nghĩa' : (session('current_house', 1) == 3 ? 'Cần Giờ' : 'Hóc Môn') }}
                </div>
            </div>

            {{-- Messages Area --}}
            <div class="flex-1 p-4 overflow-y-auto space-y-4 bg-slate-50/50" id="chat-messages-container">
                @forelse($messages as $msg)
                    @if($msg->type === 'system')
                        {{-- System notification message --}}
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-xl p-3.5 shadow-sm text-xs text-amber-900">
                            <div class="flex items-center justify-between font-bold mb-1">
                                <span>{{ $msg->sender_name }} (Hệ thống)</span>
                                <span class="text-[9px] text-amber-600/80 font-normal">
                                    {{ \Carbon\Carbon::parse($msg->created_at)->diffForHumans() }}
                                </span>
                            </div>
                            <p class="leading-relaxed font-semibold">{{ $msg->message }}</p>
                        </div>
                    @else
                        {{-- Standard User Chat Message --}}
                        <div class="flex flex-col {{ $msg->sender_id == auth()->id() ? 'items-end' : 'items-start' }}">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="text-[10px] font-black text-gray-700">{{ $msg->sender_name }}</span>
                                <span class="text-[9px] text-gray-400">
                                    {{ \Carbon\Carbon::parse($msg->created_at)->format('H:i d/m') }}
                                </span>
                            </div>
                            <div class="max-w-[85%] rounded-2xl px-3.5 py-2 text-xs shadow-sm leading-relaxed
                                {{ $msg->sender_id == auth()->id() 
                                    ? 'bg-indigo-600 text-white rounded-tr-none font-medium' 
                                    : 'bg-white text-gray-800 border border-gray-100 rounded-tl-none font-medium' }}">
                                {{ $msg->message }}
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-center py-20 text-gray-400">
                        <span class="text-4xl mb-2">💬</span>
                        <p class="text-xs font-semibold">Chưa có tin nhắn hoặc thông báo nào</p>
                        <p class="text-[10px] text-gray-400 mt-1">Gõ nội dung phía dưới để gửi tin nhắn đến các kho</p>
                    </div>
                @endforelse
            </div>

            {{-- Message Input Area --}}
            <div class="p-4 border-t border-gray-100 bg-white flex gap-2">
                <input wire:model="chatMessage" 
                    wire:keydown.enter.prevent="sendMessage"
                    type="text"
                    placeholder="Gõ tin nhắn gửi đến các kho..."
                    class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                <button wire:click="sendMessage" type="button"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-xs font-black transition-all hover:scale-[1.03] active:scale-[0.97]">
                    Gửi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const container = document.getElementById("chat-messages-container");
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });

    document.addEventListener("livewire:navigated", () => {
        const container = document.getElementById("chat-messages-container");
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
