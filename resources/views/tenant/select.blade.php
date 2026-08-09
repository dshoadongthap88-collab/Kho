<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chọn Ngôi Nhà - ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass-panel {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }
        
        .house-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            width: 220px; /* Reduced width to fit 5 cards easily */
        }
        
        .house-card:hover:not(.locked) {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(56, 189, 248, 0.2);
            border-color: rgba(56, 189, 248, 0.5);
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
<body class="bg-sky-50 min-h-screen text-slate-800 relative font-sans overflow-x-hidden">
    <!-- Animated Background -->
    <div class="absolute inset-0 z-0">
        <div class="absolute w-[500px] h-[500px] bg-blue-200 rounded-full mix-blend-multiply filter blur-[128px] opacity-70 top-[-100px] left-[-100px] animate-pulse"></div>
        <div class="absolute w-[600px] h-[600px] bg-teal-200 rounded-full mix-blend-multiply filter blur-[128px] opacity-60 bottom-[-100px] right-[-100px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center p-2">
        <div class="text-center mb-8 absolute top-8 left-0 right-0">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-indigo-600 uppercase">
                Hệ Thống ERP Đa Chi Nhánh
            </h1>
        </div>

        <div class="text-center mb-8 mt-20">
            <p class="text-lg text-slate-600 font-medium">Chào mừng, <span class="font-bold text-sky-700">{{ Auth::user()->name }}</span>! Vui lòng chọn chi nhánh làm việc.</p>
        </div>

        <div class="flex flex-wrap justify-center gap-5 max-w-7xl w-full">
            @foreach ($projects as $project)
                @php
                    $isAllowed = in_array($project->id, $allowedHouses);
                    $icon = ['🏢', '🏪', '🏭', '🏬', '🏰', '🏠', '🏨'][$project->id % 7];
                    $isHR = $project->id == 5; // Highlight HR house
                @endphp
                
                <div class="house-card glass-panel rounded-2xl p-5 relative overflow-hidden cursor-pointer group {{ $isAllowed ? '' : 'locked' }}"
                     @if($isAllowed) onclick="openPinModal({{ $project->id }}, '{{ addslashes($project->name) }}')" @endif>
                    
                    <!-- Decorative gradient for allowed houses -->
                    @if($isAllowed)
                        <div class="absolute inset-0 bg-gradient-to-br {{ $isHR ? 'from-purple-500/10 to-pink-500/10' : 'from-sky-500/10 to-blue-500/10' }} opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    @endif

                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-16 h-16 rounded-2xl {{ $isHR ? 'bg-purple-100 text-purple-600' : 'bg-sky-100 text-sky-600' }} flex items-center justify-center mb-4 shadow-inner border border-white">
                            <span class="text-3xl">
                                {{ $isHR ? '⭐' : $icon }}
                            </span>
                        </div>
                        
                        <h2 class="text-lg font-bold mb-2 text-center text-slate-800 leading-tight">{{ $project->name }}</h2>
                        
                        @if($isAllowed)
                            <div class="mt-2 px-3 py-1 rounded-full {{ $isHR ? 'bg-purple-100 border-purple-200 text-purple-700' : 'bg-emerald-100 border-emerald-200 text-emerald-700' }} border text-xs font-bold">
                                Sẵn sàng truy cập
                            </div>
                        @else
                            <div class="mt-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-500 text-xs font-medium flex items-center gap-1">
                                <span>🔒</span> Không có quyền
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-12 text-center">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-slate-500 hover:text-sky-600 font-medium transition-colors text-sm underline underline-offset-4">
                    Đăng xuất tài khoản
                </button>
            </form>
        </div>
    </div>

    <!-- PIN Modal -->
    <div id="pinModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm opacity-0 pointer-events-none">
        <div id="pinModalContent" class="bg-white p-8 rounded-3xl w-full max-w-md border border-slate-100 shadow-2xl relative">
            <button onclick="closePinModal()" class="absolute top-2 right-4 text-slate-400 hover:text-slate-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
            
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-full bg-sky-100 mx-auto flex items-center justify-center mb-4">
                    <span class="text-2xl">🔐</span>
                </div>
                <h3 class="text-2xl font-bold mb-2 text-slate-800">Xác nhận bảo mật</h3>
                <p class="text-slate-500 text-sm">Vui lòng nhập lại mật khẩu để vào <span id="modalHouseName" class="font-bold text-sky-700"></span></p>
            </div>

            <form id="verifyForm" onsubmit="verifyHouse(event)" autocomplete="off">
                @csrf
                <input type="hidden" id="houseId" name="house_id">
                
                <div class="mb-6">
                    <input type="text" id="password" name="password" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 text-center tracking-widest text-lg font-bold"
                       style="-webkit-text-security: disc; text-security: disc;"
                       autocomplete="new-password"
                       placeholder="••••••••" autofocus>
                    <p id="errorMessage" class="text-red-500 font-medium text-sm mt-2 text-center hidden"></p>
                </div>

                <button type="submit" id="submitBtn"
                        class="w-full bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-[1.02] active:scale-95 shadow-lg flex justify-center items-center">
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
        function openPinModal(houseId, houseName) {
            document.getElementById('houseId').value = houseId;
            document.getElementById('modalHouseName').textContent = houseName;
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
