<div class="px-4">
    <!-- Thống kê tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2 mb-8">
        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex items-center gap-2">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl font-bold">📦</div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Tổng thiết bị</p>
                <p class="text-2xl font-black text-slate-800">{{ $totalAssets }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex items-center gap-2">
            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl font-bold">✅</div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Đang hoạt động</p>
                <p class="text-2xl font-black text-slate-800">{{ $activeAssets }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm flex items-center gap-2">
            <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xl font-bold">🔧</div>
            <div>
                <p class="text-sm text-slate-500 font-medium">Đang sửa chữa</p>
                <p class="text-2xl font-black text-slate-800">{{ $maintenanceAssets }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-yellow-300 shadow-sm flex items-center gap-2 bg-yellow-50">
            <div class="w-12 h-12 rounded-full bg-yellow-200 flex items-center justify-center text-yellow-700 text-xl font-bold">⚠️</div>
            <div>
                <p class="text-sm text-yellow-700 font-medium">Sắp bảo dưỡng</p>
                <p class="text-2xl font-black text-yellow-800">{{ $warningCount }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-red-300 shadow-sm flex items-center gap-2 bg-red-50">
            <div class="w-12 h-12 rounded-full bg-red-200 flex items-center justify-center text-red-700 text-xl font-bold">🔴</div>
            <div>
                <p class="text-sm text-red-700 font-medium">Quá hạn</p>
                <p class="text-2xl font-black text-red-800">{{ $overdueCount }}</p>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-2 flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium">
            <span>✅</span> {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-sm font-bold text-slate-800">Kế Hoạch Bảo Dưỡng Cảnh Báo</h2>
        <div class="relative w-72">
            <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Tìm mã, tên thiết bị..."
                   class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead>
                    <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="px-3 py-3 text-left">Mã Kế Hoạch</th>
                        <th class="px-3 py-3 text-left">Thiết bị</th>
                        <th class="px-3 py-3 text-left">Hạng Mục</th>
                        <th class="px-3 py-3 text-right">Odo / Giờ Hiện Tại</th>
                        <th class="px-3 py-3 text-right">Odo / Giờ Định Mức</th>
                        <th class="px-3 py-3 text-center">Cảnh báo</th>
                        <th class="px-3 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($plans as $plan)
                        @php
                            $currentVal = $plan->asset ? max($plan->asset->current_odo, $plan->asset->current_hours) : 0; // Đơn giản hoá hiển thị
                            
                            $isOverdue = $currentVal >= $plan->maintenance_odo;
                            $isWarning = !$isOverdue && ($currentVal >= $plan->maintenance_odo - 50); // Cách 50 đv
                            
                            $rowClass = $isOverdue ? 'bg-red-50' : ($isWarning ? 'bg-yellow-50' : 'bg-white');
                            $badgeClass = $isOverdue ? 'bg-red-200 text-red-800 border-red-300' : ($isWarning ? 'bg-yellow-200 text-yellow-800 border-yellow-300' : 'bg-green-100 text-green-800 border-green-200');
                            $badgeText = $isOverdue ? 'Quá Hạn' : ($isWarning ? 'Sắp Đến Hạn' : 'Bình Thường');
                        @endphp
                        <tr class="{{ $rowClass }} transition-colors">
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-gray-900">{{ $plan->plan_code }}</td>
                            <td class="px-2 py-1.5 text-sm font-semibold text-indigo-700">
                                {{ $plan->asset->asset_code ?? '' }}<br>
                                <span class="text-xs text-gray-500">{{ $plan->asset->name ?? '' }}</span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-gray-800">{{ $plan->category }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-right text-gray-900">{{ number_format($currentVal) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-sm font-bold text-right text-indigo-600">{{ number_format($plan->maintenance_odo) }}</td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full border {{ $badgeClass }}">{{ $badgeText }}</span>
                            </td>
                            <td class="px-2 py-1.5 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="markAsCompleted({{ $plan->id }})" class="text-green-700 hover:text-green-900 mx-2 bg-green-100 border border-green-200 px-1.5 py-1 text-[11px].5 rounded-lg shadow-sm" title="Đã hoàn thành">Đã bảo dưỡng ✅</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                
                                <p>Không có kế hoạch bảo dưỡng nào đang chờ.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200">
            {{ $plans->links() }}
        </div>
    </div>
</div>
