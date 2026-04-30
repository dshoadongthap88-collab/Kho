<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhân Viên - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 h-screen w-screen overflow-hidden flex flex-col font-sans" x-data="employeeManagement()">

    <!-- Header Overlay -->
    <div class="bg-indigo-900 text-white shadow relative z-20 flex justify-between items-center px-6 py-4">
        <div class="flex items-center gap-3">
            <span class="text-2xl">👥</span>
            <h1 class="text-xl font-bold tracking-wide">QUẢN LÝ NHÂN VIÊN & PHÂN QUYỀN</h1>
        </div>
        <!-- Nút Đóng (X) -->
        <a href="{{ route('warehouse.inventory') }}" class="w-10 h-10 rounded-full hover:bg-red-500 bg-indigo-800 flex items-center justify-center transition-colors shadow focus:outline-none" title="Đóng">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </a>
    </div>

    @if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 absolute top-20 right-4 z-50 shadow-md transform transition-all duration-300 rounded" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
        <p>{{ session('success') }}</p>
    </div>
    @endif
    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 absolute top-20 right-4 z-50 shadow-md transform transition-all duration-300 rounded" x-data="{ show: true }" x-show="show">
        <div class="flex justify-between items-start">
            <ul class="list-disc pl-4 text-sm mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button @click="show = false" class="text-red-700 hover:text-red-900 ml-4">&times;</button>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden relative z-10 px-4 py-4 gap-6">
        
        <!-- Left: Data List (Table) -->
        <div class="w-2/3 bg-white rounded-xl shadow-lg border border-gray-200 flex flex-col overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h2 class="text-lg font-bold text-gray-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                    Danh sách nhân viên ({{ count($users) }})
                </h2>
                <button @click="openCreate()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Thêm mới
                </button>
            </div>
            
            <div class="flex-1 overflow-auto p-0">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase sticky top-0 shadow-sm z-10">
                        <tr>
                            <th class="px-4 py-3 font-semibold border-b">Mã NV</th>
                            <th class="px-4 py-3 font-semibold border-b">Tên Nhân Viên</th>
                            <th class="px-4 py-3 font-semibold border-b">Chức Vụ</th>
                            <th class="px-4 py-3 font-semibold border-b">Phòng Ban</th>
                            <th class="px-4 py-3 font-semibold border-b">Tên Đăng Nhập</th>
                            <th class="px-4 py-3 font-semibold border-b">Mật khẩu</th>
                            <th class="px-4 py-3 font-semibold border-b text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @foreach ($users as $user)
                        <tr class="hover:bg-indigo-50/50 transition-colors cursor-pointer {{ session('edited_user_id') == $user->id ? 'bg-indigo-50' : '' }}" 
                            @click="editUser({{ $user->toJson() }})">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $user->code }}</td>
                            <td class="px-4 py-3 text-gray-800 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase">{{ substr($user->name, 0, 1) }}</div>
                                <span>{{ $user->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->role === 'admin' ? 'Quản trị viên' : ($user->role === 'staff' ? 'Nhân viên' : 'Xem') }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->department ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->username ?? $user->email ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xl leading-none tracking-widest mt-1 inline-block">******</td>
                            <td class="px-4 py-3 text-center" @click.stop>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition-colors" title="Xóa" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @if(count($users) === 0)
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">Chưa có dữ liệu nhân viên.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Form (Sửa / Thêm) -->
        <div class="w-1/3 bg-white rounded-xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden transition-all duration-300 relative mb-4" 
             x-show="isFormOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0"
             style="display: none; max-height: calc(100% - 10px);">
            
            <div class="p-4 bg-indigo-50 border-b border-indigo-100 flex justify-between items-center shrink-0">
                <h3 class="text-lg font-bold text-indigo-900" x-text="isEdit ? 'Chỉnh sửa nhân viên' : 'Thêm nhân viên mới'"></h3>
                <button @click="closeForm()" class="text-indigo-400 hover:text-indigo-700 bg-white rounded-full p-1 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="p-5 overflow-auto flex-1 custom-scrollbar">
                <form :action="formAction" method="POST" id="userForm">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    <div class="space-y-4">
                        <!-- Thông tin cơ bản -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Mã NV <span class="text-red-500">*</span></label>
                                <input type="text" name="code" x-model="formData.code" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Họ Tên <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="formData.name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Chức vụ <span class="text-red-500">*</span></label>
                                <select name="role" x-model="formData.role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-sm bg-white">
                                    <option value="staff">Nhân viên</option>
                                    <option value="admin">Quản trị viên</option>
                                    <option value="viewer">Người xem</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Phòng ban</label>
                                <input type="text" name="department" x-model="formData.department" placeholder="VD: Kho, Kế toán..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-sm">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100"></div>

                        <!-- Tài khoản -->
                        <h4 class="text-sm font-bold text-gray-700 flex items-center gap-1 mb-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Tài khoản đăng nhập
                        </h4>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Tên đăng nhập (Email / SĐT)</label>
                                <input type="text" name="username" x-model="formData.username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-sm" placeholder="Không bắt buộc...">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Mật khẩu</label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" name="password" x-model="formData.password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition text-sm pr-10" :placeholder="isEdit ? 'Để trống nếu không đổi' : 'Mặc định: 123456...'">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-indigo-600">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                        <svg x-show="showPassword" style="display: none;" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.543 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100"></div>

                        <!-- Phân quyền Ngôi nhà -->
                        <h4 class="text-sm font-bold text-gray-700 flex items-center gap-1 mb-3">
                            <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            Phân quyền Ngôi Nhà (Cơ sở)
                        </h4>
                        
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 mb-4">
                            <div class="grid grid-cols-2 gap-2">
                                @for($i = 1; $i <= 4; $i++)
                                <label class="flex items-center p-1.5 hover:bg-white rounded cursor-pointer transition-colors border border-transparent hover:border-gray-200">
                                    <input type="checkbox" name="allowed_houses[]" value="{{ $i }}" x-model="formData.allowed_houses" class="w-4 h-4 text-green-600 bg-white border-gray-300 rounded focus:ring-green-500 focus:ring-2">
                                    <span class="ml-2 text-sm text-gray-700 font-medium">Nhà Số {{ $i }}</span>
                                </label>
                                @endfor
                            </div>
                        </div>

                        <!-- Phân quyền Module -->
                        <h4 class="text-sm font-bold text-gray-700 flex items-center gap-1 mb-3">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            Phân quyền Module (Tick vào để cấp quyền)
                        </h4>
                        
                        <div class="space-y-2 bg-gray-50 p-3 rounded-lg border border-gray-100">
                            @php
                                $moduleGroups = [
                                    '1. Thông tin NCC/KH' => [
                                        'contacts' => 'Quản lý thông tin Đối tác/Khách hàng'
                                    ],
                                    '2. Kho' => [
                                        'stock-in' => 'Nhập kho',
                                        'stock-out' => 'Xuất kho',
                                        'inventory' => 'Tồn kho',
                                        'stock-count' => 'Kiểm kê kho'
                                    ],
                                    '3. Sản phẩm & BOM' => [
                                        'product-catalog' => 'Danh mục sản phẩm',
                                        'material-names' => 'Danh mục Tên NVL',
                                        'bom' => 'BOM / Định mức'
                                    ],
                                    '4. Tổng hợp' => [
                                        'purchase-request' => 'Phiếu đề xuất mua hàng',
                                        'delivery-note' => 'Biên bản giao nhận',
                                        'reports_transaction' => 'Báo cáo chi tiết giao dịch',
                                        'reports_stock' => 'Báo cáo kho'
                                    ],
                                    '5. Giao hàng' => [
                                        'customer-debt' => 'Công nợ khách hàng',
                                        'delivery-report' => 'Báo cáo giao hàng'
                                    ]
                                ];
                            @endphp
                            
                            <div class="h-64 overflow-y-auto custom-scrollbar pr-2 space-y-4">
                                @foreach($moduleGroups as $groupName => $permissions)
                                <div class="bg-white p-3 rounded border border-gray-200">
                                    <h5 class="text-sm font-bold text-indigo-700 mb-2 border-b border-gray-100 pb-1">{{ $groupName }}</h5>
                                    <div class="space-y-1">
                                        @foreach($permissions as $key => $label)
                                        <label class="flex items-center p-1.5 hover:bg-gray-50 rounded cursor-pointer transition-colors">
                                            <input type="checkbox" name="permissions[]" value="{{ $key }}" x-model="formData.permissions" class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 focus:ring-2">
                                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="p-6 border-t border-gray-100 bg-gray-50 shrink-0 flex justify-end gap-3 shadow-[0_-4px_10px_rgba(0,0,0,0.03)] z-10 pb-8">
                <button type="button" @click="closeForm()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium focus:outline-none focus:ring-2 focus:ring-gray-200" :disabled="isSubmitting">Hủy</button>
                <button type="button" @click="submitForm()" 
                        class="px-6 py-2 rounded-lg shadow-lg transition-all duration-300 text-sm font-bold flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-offset-1 min-w-[140px] justify-center"
                        :class="isSubmitting ? 'bg-green-500 text-white cursor-default' : 'bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500'"
                        :disabled="isSubmitting">
                    
                    <template x-if="!isSubmitting">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <span x-text="isEdit ? 'Lưu thay đổi' : 'Tạo mới'"></span>
                        </div>
                    </template>
                    
                    <template x-if="isSubmitting">
                        <div class="flex items-center gap-2 animate-bounce">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>ĐÃ XÁC NHẬN</span>
                        </div>
                    </template>
                </button>
            </div>
            
            <!-- Success Overlay Effect -->
            <div x-show="isSubmitting" 
                 x-transition:enter="transition opacity ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center z-50"
                 style="display: none;">
                <div class="bg-white p-6 rounded-2xl shadow-2xl border border-green-100 flex flex-col items-center gap-4 transform scale-110">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center shadow-inner">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-gray-800">Thành công!</h4>
                        <p class="text-sm text-gray-500">Đang lưu dữ liệu...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c7c7cc; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a1a1aa; }
    </style>

    <script>
        function employeeManagement() {
            return {
                isFormOpen: false,
                isEdit: false,
                isSubmitting: false,
                showPassword: false,
                formAction: '',
                formData: {
                    id: '',
                    code: '',
                    name: '',
                    role: 'staff',
                    department: '',
                    username: '',
                    password: '',
                    permissions: [],
                    allowed_houses: [1]
                },
                openCreate() {
                    this.isEdit = false;
                    this.showPassword = false;
                    this.formAction = '{{ route('admin.users.store') }}';
                    this.formData = {
                        id: '',
                        code: '',
                        name: '',
                        role: 'staff',
                        department: '',
                        username: '',
                        password: '',
                        permissions: [],
                        allowed_houses: [1]
                    };
                    this.isFormOpen = true;
                },
                editUser(user) {
                    this.isEdit = true;
                    this.showPassword = false;
                    this.formAction = `/admin/users/${user.id}`;
                    this.formData = {
                        id: user.id,
                        code: user.code || '',
                        name: user.name || '',
                        role: user.role || 'staff',
                        department: user.department || '',
                        username: user.username || user.email || '',
                        password: '', // Khi edit mặc định để trống
                        permissions: Array.isArray(user.permissions) ? user.permissions : [],
                        allowed_houses: Array.isArray(user.allowed_houses) ? user.allowed_houses : (user.allowed_houses ? JSON.parse(user.allowed_houses) : [])
                    };
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    this.isFormOpen = true;
                },
                closeForm() {
                    this.isFormOpen = false;
                    this.isSubmitting = false;
                },
                submitForm() {
                    this.isSubmitting = true;
                    setTimeout(() => {
                        document.getElementById('userForm').submit();
                    }, 2000);
                }
            }
        }
    </script>
</body>
</html>
