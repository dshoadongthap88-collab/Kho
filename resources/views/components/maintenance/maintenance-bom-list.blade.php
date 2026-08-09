<div class="p-2 bg-white rounded-lg shadow-sm">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Danh sách Định mức bảo dưỡng (BOM)</h2>
        <a href="{{ route('maintenance-boms.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
            + Tạo BOM mới
        </a>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <div class="flex-1 min-w-[200px]">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm theo Mã BOM, Tên xe, Cấp BD, Mã vật tư..." class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none">
        </div>
        <div class="w-48">
            <select wire:model.live="cycleFilter" class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-200 focus:outline-none">
                <option value="">-- Lọc theo chu kỳ --</option>
                <option value="250">250h</option>
                <option value="500">500h</option>
                <option value="1000">1000h</option>
                <option value="2000">2000h</option>
                <option value="4000">4000h</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b">
                    <th class="p-3 font-semibold text-gray-600">Mã BOM</th>
                    <th class="p-3 font-semibold text-gray-600">Mã tài sản</th>
                    <th class="p-3 font-semibold text-gray-600">Tên xe</th>
                    <th class="p-3 font-semibold text-gray-600">Cấp bảo dưỡng</th>
                    <th class="p-3 font-semibold text-gray-600">Chu kỳ</th>
                    <th class="p-3 font-semibold text-gray-600">Tổng vật tư</th>
                    <th class="p-3 font-semibold text-gray-600">Người tạo</th>
                    <th class="p-3 font-semibold text-gray-600">Ngày tạo</th>
                    <th class="p-3 font-semibold text-gray-600">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($boms as $bom)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 font-medium text-blue-600">{{ $bom->bom_code }}</td>
                        <td class="p-3">{{ $bom->asset->asset_code ?? 'N/A' }}</td>
                        <td class="p-3">{{ $bom->asset->name ?? 'N/A' }}</td>
                        <td class="p-3"><span class="px-1.5 py-1 text-[11px] bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">{{ $bom->maintenance_level }}</span></td>
                        <td class="p-3">{{ $bom->cycle }}</td>
                        <td class="p-3 font-bold">{{ $bom->items->count() }}</td>
                        <td class="p-3">{{ $bom->creator->name ?? 'System' }}</td>
                        <td class="p-3">{{ $bom->created_at->format('d/m/Y') }}</td>
                        <td class="p-3">
                            <a href="{{ route('maintenance-boms.edit', $bom->id) }}" class="text-blue-500 hover:underline">Sửa</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-2 text-center text-gray-500">Không tìm thấy kết quả nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $boms->links() }}
    </div>
</div>
