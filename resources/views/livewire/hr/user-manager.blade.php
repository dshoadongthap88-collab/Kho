<div>
    {{-- Flash --}}
    @if(session('message'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)"
             class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('message') }}
        </div>
    @endif
    @if(session('error'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative w-64">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Mã, tên, email, SĐT..."
                           class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                </div>

                {{-- Filter theo dự án/house --}}
                <select wire:model.live="filterHouse"
                        class="text-xs border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                    <option value="">Tất cả dự án</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>

            <button wire:click="openModal"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Thêm nhân viên
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="px-4 py-3 w-14 text-center">Avatar</th>
                        <th class="px-4 py-3">Mã NV</th>
                        <th class="px-4 py-3">Thông tin nhân viên</th>
                        <th class="px-4 py-3">Phòng ban</th>
                        <th class="px-4 py-3">Dự án</th>
                        <th class="px-4 py-3">Vai trò</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-center w-20">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            {{-- Avatar --}}
                            <td class="px-4 py-2.5 text-center">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}"
                                         alt="{{ $user->name }}"
                                         class="w-9 h-9 rounded-full object-cover border-2 border-slate-200 mx-auto">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs border-2 border-indigo-200 uppercase mx-auto">
                                        {{ mb_substr($user->name, 0, 2) }}
                                    </div>
                                @endif
                            </td>

                            {{-- Mã NV --}}
                            <td class="px-4 py-2.5 font-mono text-xs font-bold text-indigo-600">{{ $user->code }}</td>

                            {{-- Thông tin --}}
                            <td class="px-4 py-2.5">
                                <div class="font-semibold text-slate-800 text-sm">{{ $user->name }}</div>
                                <div class="flex items-center gap-3 mt-0.5">
                                    @if($user->email)
                                        <a href="mailto:{{ $user->email }}"
                                           class="text-xs text-slate-400 hover:text-indigo-500 transition truncate max-w-[160px]">
                                            {{ $user->email }}
                                        </a>
                                    @endif
                                    @if($user->phone)
                                        <a href="tel:{{ $user->phone }}"
                                           class="text-xs font-mono text-slate-400 hover:text-indigo-500 transition">
                                            {{ $user->phone }}
                                        </a>
                                    @endif
                                </div>
                            </td>

                            {{-- Phòng ban --}}
                            <td class="px-4 py-2.5 text-xs font-medium text-slate-600">
                                {{ $user->department ?: '—' }}
                            </td>

                            {{-- Dự án được phép --}}
                            <td class="px-4 py-2.5">
                                @php
                                    $userHouses = is_array($user->allowed_houses) ? $user->allowed_houses : [];
                                @endphp
                                @if(empty($userHouses))
                                    <span class="text-xs text-slate-300 italic">Chưa phân</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($projects->whereIn('id', $userHouses)->take(3) as $p)
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 ring-1 ring-sky-200">
                                                {{ $p->name }}
                                            </span>
                                        @endforeach
                                        @if(count($userHouses) > 3)
                                            <span class="text-[10px] text-slate-400">+{{ count($userHouses) - 3 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Vai trò --}}
                            <td class="px-4 py-2.5">
                                @php
                                    $roleMap = [
                                        'admin'            => ['QUẢN TRỊ',    'bg-rose-50 text-rose-700 ring-rose-200'],
                                        'manager'          => ['QUẢN LÝ',     'bg-blue-50 text-blue-700 ring-blue-200'],
                                        'team_leader_ktsc' => ['TỔ TRƯỞNG',   'bg-violet-50 text-violet-700 ring-violet-200'],
                                        'staff_ktsc'       => ['NV KTSC',     'bg-orange-50 text-orange-700 ring-orange-200'],
                                        'staff_kho'        => ['NV KHO',      'bg-emerald-50 text-emerald-700 ring-emerald-200'],
                                        'staff'            => ['NHÂN VIÊN',   'bg-slate-100 text-slate-600 ring-slate-200'],
                                    ];
                                    [$rLabel, $rClass] = $roleMap[$user->role] ?? [$user->role, 'bg-slate-100 text-slate-500 ring-slate-200'];
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-bold ring-1 {{ $rClass }}">{{ $rLabel }}</span>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-4 py-2.5 text-center">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 ring-1 ring-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Khóa
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="edit({{ $user->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $user->id }})"
                                            wire:confirm="Xác nhận xóa nhân viên {{ $user->name }}?"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Xóa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <p class="font-semibold text-slate-600">Không tìm thấy nhân viên nào</p>
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

    {{-- ===== MODAL THÊM / SỬA ===== --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl z-10 overflow-hidden flex flex-col max-h-[90vh]">

                <div class="flex items-center justify-between px-6 py-4 bg-indigo-600 shrink-0">
                    <h3 class="text-base font-black text-white uppercase tracking-wide">
                        {{ $userId ? 'Chỉnh sửa nhân viên' : 'Thêm nhân viên mới' }}
                    </h3>
                    <button wire:click="closeModal" class="p-1 rounded text-indigo-200 hover:text-white hover:bg-indigo-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Cột trái --}}
                        <div class="space-y-3">
                            <div>
                                <label class="form-label">Mã NV <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model.defer="code">
                                @error('code') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="form-label">Họ tên <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model.defer="name">
                                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" wire:model.defer="email">
                                @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" wire:model.defer="phone" class="font-mono">
                                @error('phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="form-label">Ngày vào làm</label>
                                <input type="date" wire:model.defer="hire_date">
                            </div>
                        </div>

                        {{-- Cột phải --}}
                        <div class="space-y-3 border-l border-slate-100 pl-5">
                            <div>
                                <label class="form-label">Tài khoản đăng nhập <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model.defer="username" autocomplete="new-username">
                                @error('username') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="form-label">Mật khẩu {!! !$userId ? '<span class="text-rose-500">*</span>' : '<span class="font-normal normal-case text-slate-400">(để trống nếu không đổi)</span>' !!}</label>
                                <input type="password" wire:model.defer="password" autocomplete="new-password">
                                @error('password') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="form-label">Phòng ban</label>
                                <select wire:model.live="department">
                                    <option value="">-- Chọn phòng ban --</option>
                                    <option value="BỘ PHẬN KHO">BỘ PHẬN KHO</option>
                                    <option value="PHÒNG KTSC">PHÒNG KTSC</option>
                                    @foreach($departments as $dept)
                                        @if(!in_array(mb_strtoupper($dept->name), ['BỘ PHẬN KHO', 'PHÒNG KTSC']))
                                            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="form-label">Vai trò</label>
                                    <select wire:model.defer="role">
                                        <option value="staff_kho">NV Kho</option>
                                        <option value="staff_ktsc">NV KTSC</option>
                                        <option value="team_leader_ktsc">Tổ trưởng KTSC</option>
                                        <option value="manager">Quản lý</option>
                                        <option value="admin">Quản trị</option>
                                        <option value="staff" class="hidden">Staff</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Trạng thái</label>
                                    <select wire:model.defer="status">
                                        <option value="active">Hoạt động</option>
                                        <option value="inactive">Khóa tài khoản</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Ảnh đại diện</label>
                                <input type="file" wire:model="newAvatar" accept="image/*"
                                       class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <div wire:loading wire:target="newAvatar" class="text-xs text-indigo-500 mt-1 flex items-center gap-1">
                                    <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Đang tải...
                                </div>
                                @if($newAvatar)
                                    <img src="{{ $newAvatar->temporaryUrl() }}" class="mt-2 w-12 h-12 rounded-full object-cover border-2 border-slate-200">
                                @elseif($avatar)
                                    <img src="{{ asset('storage/' . $avatar) }}" class="mt-2 w-12 h-12 rounded-full object-cover border-2 border-slate-200">
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Phân quyền --}}
                    <div class="mt-5 pt-5 border-t border-slate-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Dự án được phép --}}
                            <div>
                                <label class="form-label flex items-center gap-1.5 mb-2">
                                    <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    Dự án được phép truy cập
                                </label>
                                <div class="space-y-1.5 bg-slate-50 border border-slate-200 rounded-xl p-3">
                                    @foreach($projects as $project)
                                        <label class="flex items-center gap-2.5 p-2 rounded-lg cursor-pointer hover:bg-white transition border border-transparent hover:border-slate-200">
                                            <input type="checkbox" value="{{ $project->id }}"
                                                   wire:model="allowed_houses"
                                                   class="w-4 h-4 rounded text-sky-600 border-slate-300 focus:ring-sky-500 cursor-pointer">
                                            <span class="text-sm font-medium text-slate-700">{{ $project->name }}</span>
                                            @if($project->code)
                                                <span class="ml-auto text-[10px] text-slate-400 font-mono">{{ $project->code }}</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-slate-400 mt-1.5">Nhân viên chỉ thấy dự án đã tick ở màn hình chọn chi nhánh</p>
                            </div>

                            {{-- Module permissions --}}
                            <div>
                                <label class="form-label flex items-center gap-1.5 mb-2">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Quyền tính năng
                                </label>
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 h-52 overflow-y-auto space-y-3">
                                    @php $dbModules = \App\Models\SystemModule::where('is_active', true)->get()->groupBy('group_name'); @endphp
                                    @foreach($dbModules as $groupName => $modules)
                                        <div class="bg-white rounded-lg border border-slate-200 p-2.5">
                                            <p class="text-xs font-bold text-indigo-700 mb-1.5 border-b border-slate-100 pb-1">{{ $groupName }}</p>
                                            <div class="space-y-1">
                                                @foreach($modules as $module)
                                                    <label class="flex items-center gap-2 p-1 rounded cursor-pointer hover:bg-slate-50 transition">
                                                        <input type="checkbox" value="{{ $module->route_name }}"
                                                               wire:model="permissions"
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
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0"
                     x-data="{saved:false}" @user-saved.window="saved=true; setTimeout(()=>{saved=false; $wire.closeModal()},1500)">
                    <button wire:click="closeModal"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                        Hủy bỏ
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                            class="px-6 py-2 text-sm font-black text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition flex items-center gap-2 min-w-[100px] justify-center disabled:opacity-60">
                        <span wire:loading.remove wire:target="save" x-show="!saved">LƯU LẠI</span>
                        <span x-show="saved" style="display:none" class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Đã lưu
                        </span>
                        <span wire:loading wire:target="save">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
