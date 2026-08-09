<div class="space-y-4">
    {{-- Header --}}
    <div class="flex justify-between items-center bg-gradient-to-r from-amber-50 to-orange-50 p-2 rounded-xl shadow-sm border border-amber-200">
        <div>
            <h2 class="text-xl font-black text-amber-900">🛒 TRUNG TÂM MUA HÀNG</h2>
            <p class="text-sm text-amber-700">Phân tích vật tư, cảnh báo tồn kho, đề xuất mua hàng tổng hợp từ tất cả dự án.</p>
        </div>
        <button wire:click="openCreateModal" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tạo Đề Xuất Mua Hàng
        </button>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg font-medium">
            {{ session('message') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="flex gap-1 bg-white rounded-xl shadow-sm border border-gray-100 p-1.5">
        <button wire:click="switchTab('top-usage')" class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'top-usage' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
            📊 Top Vật Tư Sử Dụng (60 ngày)
        </button>
        <button wire:click="switchTab('low-stock')" class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'low-stock' ? 'bg-red-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
            ⚠️ Tồn Dưới Mức Tối Thiểu
        </button>
        <button wire:click="switchTab('proposals')" class="px-4 py-2 rounded-lg text-sm font-bold transition-all {{ $activeTab === 'proposals' ? 'bg-sky-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
            📋 Đề Xuất Mua Hàng (CRUD)
        </button>
    </div>

    {{-- ==================== TAB 1: Top Vật Tư Sử Dụng ==================== --}}
    @if($activeTab === 'top-usage')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-amber-50 border-b border-amber-100">
            <h3 class="font-bold text-amber-900">📊 Top 30 Mã Vật Tư Xuất Kho Nhiều Nhất (60 ngày gần nhất) — Tổng hợp từ tất cả dự án</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">STT</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Mã VT</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Tên Vật Tư</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">ĐVT</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">Tổng Xuất</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">Số Đơn</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Dự Án Xuất Nhiều Nhất</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->topUsageData as $index => $item)
                    <tr class="hover:bg-amber-50 transition-colors">
                        <td class="px-2 py-1.5 text-sm text-gray-500 font-bold">{{ $index + 1 }}</td>
                        <td class="px-2 py-1.5 text-sm font-mono font-bold text-gray-900">{{ $item['code'] }}</td>
                        <td class="px-2 py-1.5 text-sm text-gray-700">{{ $item['name'] }}</td>
                        <td class="px-2 py-1.5 text-sm text-center text-gray-500">{{ $item['unit'] }}</td>
                        <td class="px-2 py-1.5 text-sm text-center font-bold text-amber-700">{{ number_format($item['total_qty'], 2) }}</td>
                        <td class="px-2 py-1.5 text-sm text-center text-gray-600">{{ $item['total_orders'] }}</td>
                        <td class="px-2 py-1.5 text-sm text-sky-700 font-medium">{{ $item['top_project'] }}</td>
                        <td class="px-2 py-1.5 text-center">
                            <button wire:click="openCreateFromProduct({{ $item['product_id'] }}, {{ $item['house_id'] ?? 'null' }})" class="text-xs bg-amber-100 text-amber-700 hover:bg-amber-200 px-1.5 py-1 text-[11px] rounded font-bold transition-colors">
                                + Đề xuất
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">Không có dữ liệu xuất kho trong 60 ngày gần đây.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ==================== TAB 2: Tồn Dưới Min ==================== --}}
    @if($activeTab === 'low-stock')
    <div class="bg-white rounded-xl shadow-sm border border-red-100 overflow-hidden">
        <div class="px-5 py-3 bg-red-50 border-b border-red-100">
            <h3 class="font-bold text-red-900">⚠️ Vật Tư Tồn Kho Dưới Mức Tối Thiểu — Cần bổ sung gấp</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">STT</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Mã VT</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Tên Vật Tư</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">ĐVT</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">Tồn Hiện Tại</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">Tối Thiểu</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">Thiếu</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Dự Án</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->lowStockData as $index => $item)
                    <tr class="hover:bg-red-50 transition-colors">
                        <td class="px-2 py-1.5 text-sm text-gray-500 font-bold">{{ $index + 1 }}</td>
                        <td class="px-2 py-1.5 text-sm font-mono font-bold text-gray-900">{{ $item['code'] }}</td>
                        <td class="px-2 py-1.5 text-sm text-gray-700">{{ $item['name'] }}</td>
                        <td class="px-2 py-1.5 text-sm text-center text-gray-500">{{ $item['unit'] }}</td>
                        <td class="px-2 py-1.5 text-sm text-center font-bold text-red-600">{{ number_format($item['current_qty'], 2) }}</td>
                        <td class="px-2 py-1.5 text-sm text-center text-gray-600">{{ number_format($item['min_stock'], 2) }}</td>
                        <td class="px-2 py-1.5 text-sm text-center">
                            <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-bold">-{{ number_format($item['shortage'], 2) }}</span>
                        </td>
                        <td class="px-2 py-1.5 text-sm text-sky-700 font-medium">{{ $item['project'] }}</td>
                        <td class="px-2 py-1.5 text-center">
                            <button wire:click="openCreateFromProduct({{ $item['product_id'] }}, {{ $item['house_id'] }}, {{ $item['shortage'] }})" class="text-xs bg-red-100 text-red-700 hover:bg-red-200 px-1.5 py-1 text-[11px] rounded font-bold transition-colors">
                                + Đề xuất mua
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-400">✅ Tất cả vật tư đều đủ tồn kho tối thiểu.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ==================== TAB 3: Đề Xuất Mua Hàng (CRUD) ==================== --}}
    @if($activeTab === 'proposals')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 bg-sky-50 border-b border-sky-100 flex justify-between items-center">
            <h3 class="font-bold text-sky-900">📋 Danh sách Đề Xuất Mua Hàng</h3>
            <div class="flex gap-2">
                <input wire:model.live.debounce.300ms="searchProposal" type="text" placeholder="Tìm mã VT, tên, ghi chú..." class="pl-3 pr-4 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 w-64">
                <select wire:model.live="filterStatus" class="border border-gray-300 rounded-lg text-sm px-3 py-1.5 focus:ring-2 focus:ring-sky-500">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending">Chờ duyệt</option>
                    <option value="approved">Đã duyệt</option>
                    <option value="ordered">Đã đặt hàng</option>
                    <option value="delivered">Đã nhận</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">ID</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Mã VT</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Tên Vật Tư</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">SL Đề Xuất</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">SL Đã Giao</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Dự Án</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">Ngày Giao DK</th>
                        <th class="px-2 py-2 text-center text-xs font-bold text-gray-500 uppercase">Trạng Thái</th>
                        <th class="px-2 py-2 text-left text-xs font-bold text-gray-500 uppercase">Ghi chú</th>
                        <th class="px-2 py-2 text-right text-xs font-bold text-gray-500 uppercase">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($proposals as $plan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-2 py-1.5 text-sm text-gray-500">#{{ $plan->id }}</td>
                        <td class="px-2 py-1.5 text-sm font-mono font-bold text-gray-900">{{ $plan->product->code ?? 'N/A' }}</td>
                        <td class="px-2 py-1.5 text-sm text-gray-700">{{ $plan->product->name ?? 'N/A' }}</td>
                        <td class="px-2 py-1.5 text-sm text-center font-bold text-amber-700">{{ number_format($plan->proposed_quantity, 2) }}</td>
                        <td class="px-2 py-1.5 text-sm text-center text-green-600 font-bold">{{ number_format($plan->delivered_quantity, 2) }}</td>
                        <td class="px-2 py-1.5 text-sm text-sky-700 font-medium">
                            @php $proj = \App\Models\Project::find($plan->house_id); @endphp
                            {{ $proj->name ?? 'N/A' }}
                        </td>
                        <td class="px-2 py-1.5 text-sm text-center text-gray-500">{{ $plan->expected_delivery_date ? $plan->expected_delivery_date->format('d/m/Y') : '-' }}</td>
                        <td class="px-2 py-1.5 text-sm text-center">
                            @switch($plan->status)
                                @case('pending')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">Chờ duyệt</span>
                                    @break
                                @case('approved')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Đã duyệt</span>
                                    @break
                                @case('ordered')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">Đã đặt hàng</span>
                                    @break
                                @case('delivered')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Đã nhận</span>
                                    @break
                                @case('cancelled')
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Đã hủy</span>
                                    @break
                                @default
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">{{ $plan->status }}</span>
                            @endswitch
                        </td>
                        <td class="px-2 py-1.5 text-sm text-gray-500 max-w-[150px] truncate" title="{{ $plan->notes }}">{{ $plan->notes ?? '-' }}</td>
                        <td class="px-2 py-1.5 text-right text-sm space-x-2">
                            <button wire:click="editProposal({{ $plan->id }})" class="text-indigo-600 hover:text-indigo-900 font-bold">Sửa</button>
                            <button onclick="confirm('Bạn có chắc muốn xóa đề xuất #{{ $plan->id }}?') || event.stopImmediatePropagation()" wire:click="deleteProposal({{ $plan->id }})" class="text-red-600 hover:text-red-900 font-bold">Xóa</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-8 text-center text-gray-400">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p>Chưa có đề xuất mua hàng nào.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($proposals->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $proposals->links() }}
        </div>
        @endif
    </div>
    @endif

    {{-- ==================== Modal Tạo / Sửa Đề Xuất ==================== --}}
    @if($isModalOpen)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
            <form wire:submit.prevent="saveProposal">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-amber-50">
                    <h3 class="text-lg font-bold text-amber-900">
                        {{ $proposal_id ? '✏️ Sửa Đề Xuất Mua Hàng #'.$proposal_id : '➕ Tạo Đề Xuất Mua Hàng Mới' }}
                    </h3>
                    <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="px-6 py-4 space-y-4">
                    {{-- Chọn Dự Án --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dự án (phân bổ về) <span class="text-red-500">*</span></label>
                        <select wire:model="proposal_house_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm">
                            <option value="">-- Chọn dự án --</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                        @error('proposal_house_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Chọn Mã Vật Tư --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã Vật Tư <span class="text-red-500">*</span></label>
                        <select wire:model="proposal_product_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm">
                            <option value="">-- Chọn vật tư --</option>
                            @foreach($allProducts as $product)
                                <option value="{{ $product->id }}">[{{ $product->code }}] {{ $product->name }} ({{ $product->unit }})</option>
                            @endforeach
                        </select>
                        @error('proposal_product_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Số lượng --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số lượng đề xuất <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" wire:model="proposal_quantity" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm" placeholder="VD: 100">
                        @error('proposal_quantity') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Ngày giao dự kiến --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày giao dự kiến</label>
                        <input type="date" wire:model="proposal_expected_date" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm">
                    </div>

                    {{-- Ghi chú --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                        <textarea wire:model="proposal_notes" rows="3" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm" placeholder="Lý do, mục đích mua hàng..."></textarea>
                        @error('proposal_notes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" wire:click="closeModal" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Hủy
                    </button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white hover:bg-amber-700">
                        {{ $proposal_id ? 'Lưu thay đổi' : 'Tạo Đề Xuất' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
