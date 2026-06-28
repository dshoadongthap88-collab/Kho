<div class="p-6 bg-white rounded shadow-md mt-6">
    <h2 class="text-xl font-bold mb-4">Danh Sách Phiếu Bảo Dưỡng</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã Phiếu</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thiết Bị</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày Sinh</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                    <th class="px-6 py-3 border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chi Phí</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($tickets as $ticket)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold">{{ $ticket->ticket_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ticket->asset->asset_code ?? 'N/A' }} - {{ $ticket->asset->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ticket->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($ticket->status == 'completed')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Hoàn thành</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Chờ xử lý</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($ticket->total_cost) }} đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
