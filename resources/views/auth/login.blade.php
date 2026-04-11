<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - ERP Warehouse</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-900 via-indigo-800 to-indigo-900 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-indigo-100 p-3 rounded-lg mb-4">
                    <span class="text-3xl">📦</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">ERP KHO</h1>
                <p class="text-gray-600 text-sm mt-2">Hệ thống quản lý kho tổng hợp</p>
            </div>

            <!-- Messages -->
            @if ($errors->has('login_error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ $errors->first('login_error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Identifier (Phone or Email) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Số điện thoại hoặc Email <span class="text-gray-400 font-normal">(không bắt buộc)</span>
                    </label>
                    <input 
                        type="text" 
                        name="identifier" 
                        value="{{ old('identifier') }}"
                        placeholder="0123456789 hoặc user@example.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                    @error('identifier')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mật khẩu
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="Nhập mật khẩu"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                    @error('password')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        id="remember"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Ghi nhớ tôi
                    </label>
                </div>

                <!-- Submit -->
                <button 
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition duration-200 transform hover:scale-105"
                >
                    Đăng nhập
                </button>
            </form>

            <!-- Info -->
            <div class="mt-8 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                <p class="text-sm text-gray-700 mb-2">
                    <strong>💡 Thông tin đăng nhập test:</strong>
                </p>
                <div class="text-xs text-gray-600 space-y-1">
                    <p>👤 Admin: <span class="font-mono bg-white px-2 py-1 rounded">0123456789</span> | Mật khẩu: <span class="font-mono bg-white px-2 py-1 rounded">123456</span></p>
                    <p>👥 Staff: <span class="font-mono bg-white px-2 py-1 rounded">0987654321</span> | Mật khẩu: <span class="font-mono bg-white px-2 py-1 rounded">123456</span></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
