<div class="p-6 bg-white rounded shadow-md mt-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Danh Sách Phiếu Bảo Dưỡng</h2>
        <div class="flex gap-2 items-center">
            @if(session()->has('error'))
                <span class="text-sm text-red-600 bg-red-50 px-3 py-1 rounded">{{ session('error') }}</span>
            @endif
            <button wire:click="printSelected" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded shadow-sm transition flex items-center gap-2">
                <span>🖨️</span> In Phiếu
                @if(count($selectedTickets) > 0)
                    <span class="bg-white text-sky-700 px-1.5 py-0.5 rounded text-xs ml-1">{{ count($selectedTickets) }}</span>
                @endif
            </button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 border-b text-center w-12">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                    </th>
                    <th class="px-6 py-3 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ngày bảo dưỡng</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên thiết bị bảo dưỡng</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mã tài sản bảo dưỡng</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mức bảo dưỡng</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tên tài xế</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($tickets as $ticket)
                    <tr class="hover:bg-slate-50 {{ in_array($ticket->id, $selectedTickets) ? 'bg-sky-50' : '' }}">
                        <td class="px-4 py-4 whitespace-nowrap text-center">
                            <input type="checkbox" value="{{ $ticket->id }}" wire:model.live="selectedTickets" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ticket->maintenance_date ? \Carbon\Carbon::parse($ticket->maintenance_date)->format('d/m/Y') : $ticket->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $ticket->asset->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $ticket->asset->asset_code ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-700">{{ $ticket->maintenance_rule_id ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $ticket->staff_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($ticket->status == 'completed')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Hoàn thành</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ xử lý</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                            Chưa có phiếu bảo dưỡng nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
