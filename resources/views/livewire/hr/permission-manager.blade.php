<div>
    @if(session('success'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)"
             class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative w-72">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Tìm tên, email, SĐT, mã NV..."
                       class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
            </div>

            {{-- Filter theo dự án --}}
            <select wire:model.live="filterHouse"
                    class="text-xs border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                <option value="">Tất cả dự án</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                @endforeach
            </select>

            <span class="ml-auto text-xs text-slate-400">
                {{ $users->total() }} nhân viên
            </span>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="px-4 py-3">Mã NV / SĐT</th>
                        <th class="px-4 py-3">Họ và Tên</th>
                        <th class="px-4 py-3">Vai trò</th>
                        <th class="px-4 py-3">Dự án được phép</th>
                        <th class="px-4 py-3">Module đã cấp</th>
                        <th class="px-4 py-3 text-center w-28">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-2.5">
                                <div class="font-mono text-xs font-bold text-indigo-600">{{ $user->code ?? '—' }}</div>
                                @if($user->phone)
                                    <a href="tel:{{ $user->phone }}" class="text-xs text-slate-400 hover:text-slate-600 transition font-mono">{{ $user->phone }}</a>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-semibold text-slate-800">
                                {{ $user->name }}
                                @if($user->department)
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $user->department }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @php
                                    $rMap = [
                                        'admin'            => ['bg-rose-50 text-rose-700 ring-rose-200',    'Admin'],
                                        'manager'          => ['bg-blue-50 text-blue-700 ring-blue-200',    'Quản lý'],
                                        'team_leader_ktsc' => ['bg-violet-50 text-violet-700 ring-violet-200', 'Tổ trưởng'],
                                        'staff_ktsc'       => ['bg-orange-50 text-orange-700 ring-orange-200', 'NV KTSC'],
                                        'staff_kho'        => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'NV Kho'],
                                    ];
                                    [$rC, $rL] = $rMap[$user->role] ?? ['bg-slate-100 text-slate-500 ring-slate-200', $user->role ?? 'Staff'];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-bold ring-1 {{ $rC }}">{{ $rL }}</span>
                            </td>
                            <td class="px-4 py-2.5">
                                @php $userHouses = is_array($user->allowed_houses) ? $user->allowed_houses : []; @endphp
                                @if(empty($userHouses))
                                    <span class="text-xs text-rose-400 font-medium italic">Chưa phân quyền</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($projects->whereIn('id', $userHouses) as $p)
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 ring-1 ring-sky-200">{{ $p->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @php $perms = is_array($user->permissions) ? $user->permissions : []; @endphp
                                @if(empty($perms))
                                    <span class="text-xs text-slate-400 italic">Chưa cấp</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        {{ count($perms) }} quyền
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <button wire:click="edit({{ $user->id }})"
                                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-lg transition mx-auto border border-indigo-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Phân quyền
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <p class="font-medium text-slate-600 text-sm">Không tìm thấy nhân viên</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    </div>

    {{-- ===== MODAL PHÂN QUYỀN ===== --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl z-10 overflow-hidden flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between px-6 py-4 bg-indigo-600 shrink-0">
                    <div>
                        <h3 class="text-sm font-black text-white">Phân quyền nhân viên</h3>
                        <p class="text-xs text-indigo-200 mt-0.5">{{ $userName }}</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="p-1 rounded text-indigo-200 hover:text-white hover:bg-indigo-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Cột trái: Vai trò + Dự án --}}
                    <div class="space-y-5">
                        <div>
                            <label class="form-label">Vai trò hệ thống</label>
                            <select wire:model="role">
                                <option value="staff">Nhân viên (Chỉ dùng module được cấp)</option>
                                <option value="staff_kho">NV Kho</option>
                                <option value="staff_ktsc">NV KTSC</option>
                                <option value="team_leader_ktsc">Tổ trưởng KTSC</option>
                                <option value="manager">Quản lý</option>
                                <option value="admin">Quản trị viên (Admin)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label flex items-center gap-1.5 mb-2">
                                <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Dự án được phép truy cập
                            </label>
                            <div class="space-y-1.5 bg-slate-50 border border-slate-200 rounded-xl p-3">
                                @foreach($projects as $project)
                                    <label class="flex items-center gap-2.5 p-2 rounded-lg cursor-pointer hover:bg-white transition border border-transparent hover:border-slate-200">
                                        <input type="checkbox" value="{{ $project->id }}"
                                               wire:model="selectedHouses"
                                               class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500 cursor-pointer">
                                        <span class="text-sm font-medium text-slate-700">{{ $project->name }}</span>
                                        @if($project->code)
                                            <span class="ml-auto text-[10px] font-mono text-slate-400">{{ $project->code }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-slate-400 mt-1.5 italic">Tick dự án nào → nhân viên thấy dự án đó ở màn hình chọn chi nhánh</p>
                        </div>
                    </div>

                    {{-- Cột phải: Module permissions --}}
                    <div>
                        <label class="form-label flex items-center gap-1.5 mb-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Quyền tính năng (Module Permissions)
                        </label>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 space-y-3 overflow-y-auto" style="max-height:380px;">
                            @php $dbModules = \App\Models\SystemModule::where('is_active', true)->get()->groupBy('group_name'); @endphp
                            @foreach($dbModules as $groupName => $modules)
                                <div class="bg-white rounded-lg border border-slate-200 p-2.5">
                                    <p class="text-xs font-bold text-indigo-700 mb-1.5 border-b border-slate-100 pb-1">{{ $groupName }}</p>
                                    <div class="space-y-1">
                                        @foreach($modules as $module)
                                            <label class="flex items-center gap-2 p-1 rounded cursor-pointer hover:bg-slate-50 transition">
                                                <input type="checkbox" value="{{ $module->route_name }}"
                                                       wire:model="selectedPermissions"
                                                       class="w-3.5 h-3.5 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500 cursor-pointer">
                                                <span class="text-xs text-slate-700">{{ $module->label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
                    <button wire:click="$set('showModal', false)"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                        Hủy
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                            class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">Lưu phân quyền</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Đang lưu...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
