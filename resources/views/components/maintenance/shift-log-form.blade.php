<div class="p-6 bg-white rounded shadow-md">
    <h2 class="text-xl font-bold mb-4">Cập Nhật Ca Làm Việc</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Thiết Bị</label>
            <select wire:model="assetId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">-- Chọn thiết bị --</option>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}">{{ $asset->asset_code }} - {{ $asset->name }}</option>
                @endforeach
            </select>
            @error('assetId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Ngày Cập Nhật</label>
            <input type="date" wire:model="readingDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            @error('readingDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Số Ca (Hôm nay)</label>
            <input type="number" step="0.5" wire:model="shiftsCount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            @error('shiftsCount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
            <textarea wire:model="note" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
        </div>

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Lưu Ca Làm Việc
        </button>
    </form>
</div>
