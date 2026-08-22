<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Quản lý Nhân Viên</h2>
            <p class="text-sm text-gray-500">Danh sách tài khoản và hồ sơ nhân sự hệ thống</p>
        </div>
        <button wire:click="openModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            Thêm nhân viên mới
        </button>
    </div>

    <!-- Thông báo -->
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <p>{{ session('message') }}</p>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <div class="relative w-full max-w-md">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm kiếm theo mã, tên, email, sđt..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-xs font-semibold uppercase tracking-wider border-b">
                        <th class="px-4 py-3 w-16 text-center">Avatar</th>
                        <th class="px-4 py-3">Mã NV</th>
                        <th class="px-4 py-3">Thông tin NV</th>
                        <th class="px-4 py-3">Phòng ban</th>
                        <th class="px-4 py-3">Vai trò</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-right w-24">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-4 py-3 text-center">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm border border-indigo-200 uppercase">
                                        {{ substr($user->name, 0, 2) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-indigo-600 font-mono">{{ $user->code }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-bold text-gray-800">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500 flex items-center gap-2 mt-1">
                                    <span class="truncate max-w-[150px]" title="{{ $user->email }}">📧 {{ $user->email }}</span>
                                    @if($user->phone)<span class="truncate">📞 {{ $user->phone }}</span>@endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold text-gray-700">
                                {{ $user->department ?: 'Chưa phân bổ' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($user->role === 'admin')
                                    <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-0.5 rounded-md uppercase">QUẢN TRỊ</span>
                                @elseif($user->role === 'manager')
                                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-0.5 rounded-md uppercase">QUẢN LÝ</span>
                                @elseif($user->role === 'team_leader_ktsc')
                                    <span class="bg-purple-100 text-purple-800 text-xs font-bold px-2 py-0.5 rounded-md uppercase">TỔ TRƯỞNG KTSC</span>
                                @elseif($user->role === 'staff_ktsc' || ($user->role === 'staff' && str_contains(mb_strtoupper($user->department ?? ''), 'KTSC')))
                                    <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-0.5 rounded-md uppercase">NV KỸ THUẬT SỬA CHỮA</span>
                                @elseif($user->role === 'staff_kho' || $user->role === 'staff')
                                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-0.5 rounded-md uppercase">NHÂN VIÊN KHO</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2 py-0.5 rounded-md uppercase">{{ $user->role ?: 'STAFF' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($user->status === 'active')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full flex items-center justify-center w-max mx-auto gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Hoạt động
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full flex items-center justify-center w-max mx-auto gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Khóa
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="edit({{ $user->id }})" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 p-1.5 rounded transition" title="Sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button wire:click="delete({{ $user->id }})" wire:confirm="Bạn có chắc chắn muốn xóa nhân viên này?" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded transition" title="Xóa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 bg-gray-50/50">Không tìm thấy nhân viên nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl sm:w-full">
                    <div class="bg-indigo-600 px-6 py-4">
                        <h3 class="text-lg font-black text-white uppercase tracking-wider">
                            {{ $userId ? 'Chỉnh sửa nhân viên' : 'Thêm mới nhân viên' }}
                        </h3>
                    </div>
                    
                    <div class="bg-white px-6 py-5">
                        <form wire:submit.prevent="save">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Trái -->
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Mã NV <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="code" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Họ tên <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Email</label>
                                        <input type="email" wire:model.defer="email" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Số điện thoại</label>
                                        <input type="text" wire:model.defer="phone" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Ngày vào làm</label>
                                        <input type="date" wire:model.defer="hire_date" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        @error('hire_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                <!-- Phải -->
                                <div class="space-y-4 border-l border-gray-100 pl-5">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Tài khoản đăng nhập <span class="text-red-500">*</span></label>
                                        <input type="text" wire:model.defer="username" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" autocomplete="new-username">
                                        @error('username') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Mật khẩu {{ !$userId ? '<span class="text-red-500">*</span>' : '(để trống nếu ko đổi)' }}</label>
                                        <input type="password" wire:model.defer="password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" autocomplete="new-password">
                                        @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Phòng ban</label>
                                        <select wire:model.live="department" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                            <option value="">-- Chọn phòng ban --</option>
                                            <option value="BỘ PHẬN KHO">BỘ PHẬN KHO</option>
                                            <option value="PHÒNG KTSC">PHÒNG KTSC</option>
                                            @foreach($departments as $dept)
                                                @if(!in_array(mb_strtoupper($dept->name), ['BỘ PHẬN KHO', 'PHÒNG KTSC']))
                                                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('department') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Vai trò</label>
                                            <select wire:model.defer="role" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                                @if(str_contains(mb_strtoupper($department), 'KHO'))
                                                    <option value="staff_kho">NHÂN VIÊN KHO</option>
                                                    <option value="manager">QUẢN LÝ</option>
                                                    <option value="admin">QUẢN TRỊ</option>
                                                @elseif(str_contains(mb_strtoupper($department), 'KTSC'))
                                                    <option value="staff_ktsc">NHÂN VIÊN KỸ THUẬT SỬA CHỮA</option>
                                                    <option value="team_leader_ktsc">TỔ TRƯỞNG KTSC</option>
                                                    <option value="manager">QUẢN LÝ</option>
                                                    <option value="admin">QUẢN TRỊ</option>
                                                @else
                                                    <option value="staff_kho">NHÂN VIÊN KHO</option>
                                                    <option value="staff_ktsc">NHÂN VIÊN KỸ THUẬT SỬA CHỮA</option>
                                                    <option value="team_leader_ktsc">TỔ TRƯỞNG KTSC</option>
                                                    <option value="manager">QUẢN LÝ</option>
                                                    <option value="admin">QUẢN TRỊ</option>
                                                @endif
                                                <!-- Fallback cho dữ liệu cũ -->
                                                <option value="staff" class="hidden">Nhân viên (Staff)</option>
                                            </select>
                                            @error('role') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Trạng thái</label>
                                            <select wire:model.defer="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                                <option value="active">Hoạt động</option>
                                                <option value="inactive">Khóa tài khoản</option>
                                            </select>
                                            @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Ảnh đại diện (Avatar)</label>
                                        <input type="file" wire:model="newAvatar" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        @error('newAvatar') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        <div wire:loading wire:target="newAvatar" class="text-indigo-600 text-xs mt-1 italic">Đang tải ảnh lên...</div>
                                        @if ($newAvatar)
                                            <img src="{{ $newAvatar->temporaryUrl() }}" class="mt-2 h-16 w-16 object-cover rounded-full border shadow-sm">
                                        @elseif($avatar)
                                            <img src="{{ asset('storage/' . $avatar) }}" class="mt-2 h-16 w-16 object-cover rounded-full border shadow-sm">
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Thêm phân quyền -->
                            <div class="mt-6 border-t border-gray-200 pt-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <!-- Phân quyền Ngôi nhà -->
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-700 flex items-center gap-1 mb-3">
                                            <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                            Phân quyền Ngôi Nhà (Cơ sở)
                                        </h4>
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                            <div class="grid grid-cols-1 gap-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                <label class="flex items-center p-1.5 hover:bg-white rounded cursor-pointer transition-colors border border-transparent hover:border-gray-200">
                                                    <input type="checkbox" name="allowed_houses[]" value="{{ $i }}" wire:model="allowed_houses" class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-500 focus:ring-2">
                                                    <span class="ml-2 text-sm text-gray-700 font-medium">{{ $i == 1 ? 'Dự án Hóc Môn' : ($i == 2 ? 'Dự án Hậu Nghĩa' : ($i == 3 ? 'Dự án Cần Giờ' : ($i == 4 ? 'Dự án Cần Giuộc' : 'Ngôi nhà HR'))) }}</span>
                                                </label>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Phân quyền Module -->
                                    <div>
                                        <h4 class="text-sm font-bold text-gray-700 flex items-center gap-1 mb-3">
                                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                            Phân quyền Module (Tick vào để cấp quyền)
                                        </h4>
                                        <div class="space-y-2 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                            @php
                                                $dbModules = \App\Models\SystemModule::where('is_active', true)->get()->groupBy('group_name');
                                            @endphp
                                            <div class="h-48 overflow-y-auto custom-scrollbar pr-2 space-y-4">
                                                @foreach($dbModules as $groupName => $modules)
                                                <div class="bg-white p-3 rounded border border-gray-200">
                                                    <h5 class="text-sm font-bold text-indigo-700 mb-2 border-b border-gray-100 pb-1">{{ $groupName }}</h5>
                                                    <div class="space-y-1">
                                                        @foreach($modules as $module)
                                                        <label class="flex items-center p-1.5 hover:bg-gray-50 rounded cursor-pointer transition-colors">
                                                            <input type="checkbox" name="permissions[]" value="{{ $module->route_name }}" wire:model="permissions" class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 focus:ring-2">
                                                            <span class="ml-2 text-sm text-gray-700">{{ $module->label }}</span>
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

                            <button type="submit" class="hidden"></button>
                        </form>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 border-t" x-data="{ saved: false }" @user-saved.window="saved = true; setTimeout(() => { saved = false; @this.closeModal() }, 2000)">
                        <button type="button" wire:click="save" class="inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-5 py-2 bg-indigo-600 text-sm font-black text-white hover:bg-indigo-700 focus:outline-none transition-all w-36 relative">
                            <span x-show="!saved" wire:loading.remove wire:target="save">LƯU LẠI</span>
                            <span wire:loading wire:target="save">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </span>
                            <span x-show="saved" style="display: none;" class="flex items-center gap-1">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Đã lưu
                            </span>
                        </button>
                        <button type="button" wire:click="closeModal" class="inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-5 py-2 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none transition-all w-32">
                            HỦY BỎ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
