<div class="p-2 bg-white rounded shadow-md mt-6">
    <h2 class="text-xl font-bold mb-4">Xác Nhận Hoàn Thành Bảo Dưỡng</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="complete" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Chọn Phiếu Bảo Dưỡng (Chờ xử lý)</label>
            <select wire:model="ticketId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">-- Chọn phiếu --</option>
                @foreach($pendingTickets as $ticket)
                    <option value="{{ $ticket->id }}">{{ $ticket->ticket_code }} - {{ $ticket->asset->name ?? 'N/A' }} (Tạo: {{ $ticket->created_at->format('d/m/Y') }})</option>
                @endforeach
            </select>
            @error('ticketId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Ngày Thực Hiện</label>
            <input type="date" wire:model="completionDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            @error('completionDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nội Dung Thực Hiện</label>
            <textarea wire:model="content" placeholder="Vd: Thay dầu động cơ, thay lọc gió..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
            @error('content') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Vật Tư Thay Thế (Mỗi dòng 1 loại)</label>
            <textarea wire:model="replacedMaterials" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Tổng Chi Phí (VNĐ)</label>
            <input type="number" wire:model="totalCost" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            @error('totalCost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Xác Nhận Hoàn Thành
        </button>
    </form>
</div>
