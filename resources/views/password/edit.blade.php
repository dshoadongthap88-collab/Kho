<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - ERP Warehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-indigo-900 text-white shadow-xl">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('warehouse.inventory') }}" class="flex items-center gap-2 text-xl font-bold">
                <span class="bg-white text-indigo-900 p-1 rounded">📦</span>
                <span>ERP KHO</span>
            </a>
            
            <div class="flex items-center gap-4">
                <span class="text-sm">{{ Auth::user()->name }}</span>
            </div>
        </div>
    </nav>

    <main class="max-w-md mx-auto mt-8 px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">🔐 Đổi mật khẩu</h1>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Current Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mật khẩu hiện tại
                    </label>
                    <input 
                        type="password" 
                        name="current_password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                    @error('current_password')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mật khẩu mới
                    </label>
                    <input 
                        type="password" 
                        name="new_password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                    @error('new_password')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Xác nhận mật khẩu
                    </label>
                    <input 
                        type="password" 
                        name="new_password_confirmation"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                </div>

                <div class="flex gap-3">
                    <button 
                        type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg transition"
                    >
                        Cập nhật mật khẩu
                    </button>
                    <a 
                        href="{{ route('warehouse.inventory') }}"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 rounded-lg text-center transition"
                    >
                        Quay lại
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
