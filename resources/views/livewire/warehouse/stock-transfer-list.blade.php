<div class="p-4">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-lg flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-lg flex items-center gap-2">
            <span>❌</span> {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Tìm mã phiếu..."
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <a href="{{ route('warehouse.stock-transfer.create') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
            ➕ Tạo phiếu chuyển kho
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-100">
        <table class="min-w-full text-sm">
            <thead class="bg-indigo-50 text-indigo-800 font-semibold uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Mã phiếu</th>
                    <th class="px-4 py-3 text-center">Từ nhà</th>
                    <th class="px-4 py-3 text-center">Sang nhà</th>
                    <th class="px-4 py-3 text-center">Số mặt hàng</th>
                    <th class="px-4 py-3 text-left">Ngày chuyển</th>
                    <th class="px-4 py-3 text-left">Người tạo</th>
                    <th class="px-4 py-3 text-left">Ghi chú</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transfers as $transfer)
                    <tr class="hover:bg-indigo-50 transition">
                        <td class="px-4 py-3 font-mono font-semibold text-indigo-700">{{ $transfer->transfer_code }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-bold">
                                🏠 Nhà {{ $transfer->from_house }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                🏠 Nhà {{ $transfer->to_house }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold">{{ $transfer->items->count() }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $transfer->transfer_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $transfer->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 italic">{{ $transfer->note ?: '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($transfer->status === 'completed')
                                <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">✔ Hoàn tất</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-bold">⏳ Đang xử lý</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400">
                            <div class="text-4xl mb-2">📦</div>
                            <div>Chưa có phiếu chuyển kho nào</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $transfers->links() }}
    </div>
</div>
