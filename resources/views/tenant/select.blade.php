<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Ngôi Nhà - ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .house-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .house-card:hover:not(.locked) {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .locked {
            filter: grayscale(100%);
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Modal animations */
        #pinModal {
            transition: opacity 0.3s ease-in-out;
        }
        #pinModalContent {
            transition: transform 0.3s ease-in-out;
            transform: scale(0.95);
        }
        #pinModal.active {
            opacity: 1;
            pointer-events: auto;
        }
        #pinModal.active #pinModalContent {
            transform: scale(1);
        }
    </style>
</head>
<body class="bg-slate-900 min-h-screen text-white overflow-hidden relative font-sans">
    <!-- Animated Background -->
    <div class="absolute inset-0 z-0">
        <div class="absolute w-[500px] h-[500px] bg-blue-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-50 top-[-100px] left-[-100px] animate-pulse"></div>
        <div class="absolute w-[600px] h-[600px] bg-purple-600 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 bottom-[-100px] right-[-100px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center p-6">
        <div class="text-center mb-16">
            <h1 class="text-5xl font-extrabold mb-4 tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">
                Hệ Thống ERP Đa Chi Nhánh
            </h1>
            <p class="text-xl text-slate-300 font-light">Chào mừng, {{ Auth::user()->name }}! Vui lòng chọn chi nhánh làm việc.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-7xl w-full">
            @for ($i = 1; $i <= 4; $i++)
                @php
                    $isAllowed = in_array($i, $allowedHouses);
                @endphp
                
                <div class="house-card glass-panel rounded-3xl p-8 relative overflow-hidden cursor-pointer group {{ $isAllowed ? '' : 'locked' }}"
                     @if($isAllowed) onclick="openPinModal({{ $i }})" @endif>
                    
                    <!-- Decorative gradient for allowed houses -->
                    @if($isAllowed)
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 to-purple-500/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    @endif

                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-24 h-24 rounded-2xl bg-white/10 flex items-center justify-center mb-6 shadow-inner border border-white/10">
                            <span class="text-5xl">
                                {{ ['🏢', '🏪', '🏭', '🏬'][$i-1] }}
                            </span>
                        </div>
                        
                        <h2 class="text-2xl font-bold mb-2">Nhà Số {{ $i }}</h2>
                        
                        @if($isAllowed)
                            <div class="mt-4 px-4 py-1.5 rounded-full bg-green-500/20 border border-green-500/30 text-green-300 text-sm font-medium">
                                Sẵn sàng truy cập
                            </div>
                        @else
                            <div class="mt-4 px-4 py-1.5 rounded-full bg-red-500/20 border border-red-500/30 text-red-300 text-sm font-medium flex items-center gap-2">
                                <span>🔒</span> Không có quyền
                            </div>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
        
        <div class="mt-16 text-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-slate-400 hover:text-white transition-colors text-sm underline underline-offset-4">
                    Đăng xuất tài khoản
                </button>
            </form>
        </div>
    </div>

    <!-- PIN Modal -->
    <div id="pinModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none">
        <div id="pinModalContent" class="glass-panel p-8 rounded-3xl w-full max-w-md border border-white/20 shadow-2xl relative">
            <button onclick="closePinModal()" class="absolute top-4 right-4 text-white/50 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
            
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-full bg-blue-500/20 mx-auto flex items-center justify-center mb-4">
                    <span class="text-2xl">🔐</span>
                </div>
                <h3 class="text-2xl font-bold mb-2">Xác nhận bảo mật</h3>
                <p class="text-slate-300 text-sm">Vui lòng nhập lại mật khẩu để vào <span id="modalHouseName" class="font-bold text-white"></span></p>
            </div>

            <form id="verifyForm" onsubmit="verifyHouse(event)">
                @csrf
                <input type="hidden" id="houseId" name="house_id">
                
                <div class="mb-6">
                    <input type="password" id="password" name="password" required
                           class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-blue-500 text-center tracking-widest text-lg"
                           placeholder="••••••••" autofocus>
                    <p id="errorMessage" class="text-red-400 text-sm mt-2 text-center hidden"></p>
                </div>

                <button type="submit" id="submitBtn"
                        class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-[1.02] active:scale-95 shadow-lg flex justify-center items-center">
                    <span>Mở Khóa Dữ Liệu</span>
                    <svg id="loadingIcon" class="animate-spin ml-2 h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        function openPinModal(houseId) {
            document.getElementById('houseId').value = houseId;
            document.getElementById('modalHouseName').textContent = 'Nhà Số ' + houseId;
            document.getElementById('password').value = '';
            document.getElementById('errorMessage').classList.add('hidden');
            
            const modal = document.getElementById('pinModal');
            modal.classList.add('active');
            
            setTimeout(() => {
                document.getElementById('password').focus();
            }, 100);
        }

        function closePinModal() {
            document.getElementById('pinModal').classList.remove('active');
        }

        async function verifyHouse(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const loader = document.getElementById('loadingIcon');
            const errorMsg = document.getElementById('errorMessage');
            
            // UI state
            btn.disabled = true;
            loader.classList.remove('hidden');
            errorMsg.classList.add('hidden');
            
            const houseId = document.getElementById('houseId').value;
            const password = document.getElementById('password').value;
            
            try {
                const response = await fetch('{{ route("tenant.verify-house") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ house_id: houseId, password: password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    errorMsg.textContent = data.message;
                    errorMsg.classList.remove('hidden');
                    btn.disabled = false;
                    loader.classList.add('hidden');
                }
            } catch (error) {
                errorMsg.textContent = 'Có lỗi xảy ra, vui lòng thử lại.';
                errorMsg.classList.remove('hidden');
                btn.disabled = false;
                loader.classList.add('hidden');
            }
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePinModal();
            }
        });
    </script>
</body>
</html>
