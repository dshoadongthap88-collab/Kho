<div>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Date Filter Standard -->
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:ring-2 focus-within:ring-indigo-100">
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Từ ngày</label>
                    <input type="date" wire:model.live="dateFrom" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                </div>
                <div class="w-px h-4 bg-slate-200 mx-1"></div>
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Đến ngày</label>
                    <input type="date" wire:model.live="dateTo" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                </div>
            </div>

            <!-- Search Standard -->
            <div class="relative w-64">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm tên, mã, hãng..." class="w-full pl-9 pr-4 py-2 text-xs font-bold rounded-xl border-slate-200 focus:ring-indigo-500 shadow-sm transition-all">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div class="flex gap-1 bg-slate-100 p-1 rounded-xl">
                <button wire:click="$set('filterMode', 'all')" class="px-4 py-1.5 text-[11px] font-black uppercase rounded-lg transition-all {{ $filterMode === 'all' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Tất cả</button>
                <button wire:click="$set('filterMode', 'low_stock')" class="px-4 py-1.5 text-[11px] font-black uppercase rounded-lg transition-all {{ $filterMode === 'low_stock' ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Sắp hết tồn</button>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if(count($selectedIds) > 0)
                <div class="flex items-center gap-2 pr-3 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4 duration-300">
                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded">Chọn: {{ count($selectedIds) }}</span>
                    <button wire:click="deleteSelected" wire:confirm="Xóa {{ count($selectedIds) }} sản phẩm đã chọn?" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-black transition">
                        <span>🗑️</span> XÓA
                    </button>
                    <button wire:click="printLabels" class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-lg text-xs font-black transition">
                        <span>🏷️</span> IN NHÃN
                    </button>
                </div>
            @endif

            <button wire:click="openModal" class="bg-indigo-600 font-black hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md shadow-indigo-100">
                <span>➕</span> THÊM SẢN PHẨM
            </button>
            <button wire:click="$set('showImportModal', true)" class="bg-emerald-600 font-black hover:bg-emerald-700 text-white px-5 py-2 rounded-xl text-xs transition shadow-md shadow-emerald-100">
                📥 IMPORT EXCEL
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-black tracking-widest">
                    <th class="px-6 py-4 w-10 text-center no-print bg-slate-100/30">
                        <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $allProductIdsOnPage) }}])" 
                               {{ count($selectedIds) >= count($allProductIdsOnPage) && count($allProductIdsOnPage) > 0 ? 'checked' : '' }}
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-4 py-3">Mã sản phẩm</th>
                    <th class="px-4 py-3 w-16 text-center">Hình ảnh</th>
                    <th class="px-4 py-3">Tên sản phẩm</th>
                    <th class="px-4 py-3">Phân loại</th>
                    <th class="px-4 py-3">Hãng sản xuất</th>
                    <th class="px-4 py-3">QC Hộp</th>
                    <th class="px-4 py-3">QC Thùng</th>
                    <th class="px-4 py-3">Số lô</th>
                    <th class="px-4 py-3">Hạn dùng</th>
                    <th class="px-4 py-3 text-center">Số lượng</th>
                    <th class="px-4 py-3">Vị trí</th>
                    <th class="px-4 py-3">Tình trạng</th>
                    <th class="px-4 py-3">Tồn tối thiểu</th>
                    <th class="px-4 py-3 text-right no-print">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr class="hover:bg-slate-50/80 transition group {{ $product->is_low_stock ? 'bg-orange-50/30' : '' }} {{ in_array($product->id, $selectedIds) ? 'bg-indigo-50/30 ring-inset ring-1 ring-indigo-200' : '' }}">
                        <td class="px-6 py-4 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $product->id }}" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </td>
                        <td class="px-4 py-3 font-mono text-sm text-blue-600">{{ $product->code }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Img" class="w-10 h-10 object-cover rounded shadow-sm border">
                            @else
                                <div class="w-10 h-10 bg-gray-100 flex items-center justify-center rounded border text-gray-400 text-xs">No img</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                        <td class="px-4 py-3">
                            @if($product->type === 'product_produced')
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs">SX</span>
                            @elseif($product->type === 'product_purchased')
                                <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-xs">Mua</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->brand }}</td>
                        <td class="px-4 py-3 text-gray-600 italic">{{ $product->box_spec }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->carton_spec }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-purple-700">{{ $product->batch_number }}</td>
                        <td class="px-4 py-3 text-sm {{ $product->is_expiring_soon ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                            {{ $product->expiry_date ? $product->expiry_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                {{ $product->is_low_stock ? 'bg-orange-600 text-white' : (($product->inventory?->quantity ?? 0) > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                                {{ number_format($product->inventory?->quantity ?? 0) }}
                                @if($product->is_low_stock) ⚠️ @endif
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->location }}</td>
                        <td class="px-4 py-3">
                            @if($product->status === 'active')
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Đang kinh doanh</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Ngừng kinh doanh</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-700">
                            {{ $product->min_stock > 0 ? number_format($product->min_stock) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right no-print">
                            <button wire:click="openModal({{ $product->id }})" class="text-blue-500 hover:text-blue-700 mr-2" title="Sửa">📝</button>
                            <button onclick="confirm('Xoá sản phẩm này?') || event.stopImmediatePropagation()" wire:click="delete({{ $product->id }})" class="text-red-500 hover:text-red-700" title="Xoá">🗑️</button>
                        </td>
                    </tr>
                @empty
                     <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ $isEdit ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới' }}</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã sản phẩm</label>
                                <input type="text" wire:model="code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tên sản phẩm</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2 flex gap-4 my-2">
                                <label class="block text-sm font-medium text-gray-700 items-center flex">Phân loại gốc:</label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="type" value="product_produced" class="text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Thành phẩm (SX)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="type" value="product_purchased" class="text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Thành phẩm (Mua)</span>
                                </label>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Hình ảnh</label>
                                <input type="file" wire:model="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hãng sản xuất</label>
                                <input type="text" wire:model="brand" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('brand') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Số lượng</label>
                                <input type="number" wire:model="quantity" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">QC Hộp (loại chai/hộp...)</label>
                                <input type="text" wire:model="box_spec" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="VD: Chai 500ml">
                                @error('box_spec') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">QC Thùng (ghi tay)</label>
                                <input type="text" wire:model="carton_spec" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="VD: 24 chai/thùng">
                                @error('carton_spec') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                             <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Vị trí</label>
                                <input type="text" wire:model="location" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Số lô <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="batch_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('batch_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hạn sử dụng</label>
                                <input type="date" wire:model="expiry_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('expiry_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tồn tối thiểu</label>
                                <input type="number" wire:model="min_stock" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('min_stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Lưu lại
                        </button>
                        <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Modal -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showImportModal', false)"></div>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Nhập dữ liệu từ Excel</h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-2">Tải tệp Excel (.xlsx, .xls) hoặc CSV để nhập hàng loạt sản phẩm.</p>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium underline">Tải tệp mẫu (.xlsx) tại đây</a>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chọn tệp của bạn</label>
                            <input type="file" wire:model="excelFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('excelFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div wire:loading wire:target="excelFile" class="mt-2 text-sm text-blue-600">
                            Đang tải tệp lên...
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="importExcel" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Bắt đầu nhập
                        </button>
                        <button type="button" wire:click="$set('showImportModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        @media print {
            .no-print, header, nav, aside, .sidebar, .px-4.py-3.bg-gray-50.border-t {
                display: none !important;
            }
            .bg-white { background-color: transparent !important; }
            .shadow-sm { box-shadow: none !important; }
            .border { border: 1px solid #000 !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #000 !important; padding: 4px !important; font-size: 10px !important; }
            tr:not(.bg-indigo-50) { display: none !important; } /* In các mục đã chọn */
            tr.bg-indigo-50 { display: table-row !important; }
            @page { size: portrait; margin: 1cm; }
        }
    </style>
</div>
