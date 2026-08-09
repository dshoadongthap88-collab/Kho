<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Phân quyền Truy cập Ngôi nhà</h1>
        <p class="text-sm text-slate-500">Cấp quyền truy cập các dự án và vai trò cho nhân sự</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-2 bg-emerald-100 text-emerald-800 rounded-xl font-bold border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-2 border-b border-slate-200 bg-slate-50">
            <input wire:model.live="search" type="text" placeholder="Tìm kiếm nhân sự..." class="w-full max-w-md px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-100 text-slate-600 text-sm">
                    <th class="p-2 font-bold border-b border-slate-200">Mã NV / Điện thoại</th>
                    <th class="p-2 font-bold border-b border-slate-200">Họ và Tên</th>
                    <th class="p-2 font-bold border-b border-slate-200">Vai trò</th>
                    <th class="p-2 font-bold border-b border-slate-200">Ngôi nhà được phép truy cập</th>
                    <th class="p-2 font-bold border-b border-slate-200 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="p-2 text-slate-600 font-medium">
                        <div>{{ $user->code ?? '-' }}</div>
                        <div class="text-xs text-slate-400">{{ $user->phone }}</div>
                    </td>
                    <td class="p-2 font-bold text-slate-800">{{ $user->name }}</td>
                    <td class="p-2">
                        @if($user->role === 'admin')
                            <span class="px-1.5 py-1 text-[11px] bg-purple-100 text-purple-700 text-xs font-bold rounded-full border border-purple-200">Quản trị (Admin)</span>
                        @else
                            <span class="px-1.5 py-1 text-[11px] bg-blue-100 text-blue-700 text-xs font-bold rounded-full border border-blue-200">Nhân viên</span>
                        @endif
                    </td>
                    <td class="p-2 text-sm">
                        <div class="flex flex-wrap gap-1">
                            @php
                                $userHouses = is_array($user->allowed_houses) ? $user->allowed_houses : [];
                            @endphp
                            @forelse($projects->whereIn('id', $userHouses) as $p)
                                <span class="px-1.5 py-1 text-[11px] bg-slate-100 text-slate-700 rounded text-xs">{{ $p->name }}</span>
                            @empty
                                <span class="text-slate-400 italic text-xs">Chưa phân quyền</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="p-2 text-right">
                        <button wire:click="edit({{ $user->id }})" class="px-1.5 py-1 text-[11px].5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg font-bold text-xs transition-colors border border-indigo-200">Phân quyền</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-500">Không tìm thấy nhân viên.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-2 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-2">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800">Phân quyền: {{ $userName }}</h2>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            
            <div class="p-2 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Vai trò Hệ thống</label>
                    <select wire:model="role" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="staff">Nhân viên (Chỉ xem/thao tác Ngôi nhà được cấp)</option>
                        <option value="admin">Quản trị viên (Quyền Admin Ngôi nhà HR)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-3">Ngôi nhà (Dự án) được phép truy cập</label>
                    <div class="space-y-3 max-h-64 overflow-y-auto p-2 bg-slate-50 border border-slate-200 rounded-xl">
                        @foreach($projects as $project)
                        <label class="flex items-center p-2 rounded hover:bg-white transition-colors cursor-pointer border border-transparent hover:border-slate-200">
                            <input wire:model="selectedHouses" type="checkbox" value="{{ $project->id }}" class="w-5 h-5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-slate-800">{{ $project->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $project->code ?? 'N/A' }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-500 mt-2 italic">* Chỉ những ngôi nhà được chọn mới xuất hiện ở màn hình Đăng nhập của nhân viên này.</p>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 font-medium">Hủy</button>
                <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold shadow-sm">Lưu phân quyền</button>
            </div>
        </div>
    </div>
    @endif
</div>
