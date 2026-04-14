<?php
$baseUrl = 'http://127.0.0.1:8000/api';

function testEndpoint($method, $url, $data = null, $token = null) {
    echo "\n========================================\n";
    echo "🧪 TESTING: $method $url\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "🎯 HTTP Code: $httpCode\n";
    echo "📦 Response: \n" . json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    return json_decode($response, true);
}

// Boot Laravel thủ công để lấy Token mà không cần đoán mật khẩu
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
if (!$user) {
    echo "❌ Không có user nào trong hệ thống.\n";
    exit;
}

// Xóa Token cũ cho sạch và Test login
$user->password = bcrypt('123456');
$user->save();

$loginRes = testEndpoint('POST', $baseUrl . '/auth/login', [
    'email' => $user->email,
    'password' => '123456'
]);

$token = $loginRes['data']['token'] ?? null;
if (!$token) {
    echo "❌ Không lấy được token. Vui lòng kiểm tra lại thông tin đăng nhập.\n";
    exit;
}

// 2. Test Dashboard
testEndpoint('GET', $baseUrl . '/dashboard/summary', null, $token);

// 3. Test Inventory (Có phân trang 2 kết quả để dễ đọc)
testEndpoint('GET', $baseUrl . '/inventory?limit=2', null, $token);

// 4. Test API Danh sách Nhập kho
testEndpoint('GET', $baseUrl . '/stock-in?limit=2', null, $token);

// 5. Test API Danh sách Xuất kho
testEndpoint('GET', $baseUrl . '/stock-out?limit=2', null, $token);

echo "\n========================================\n";
echo "✅ HOÀN TẤT QUÁ TRÌNH KIỂM TRA!\n";
