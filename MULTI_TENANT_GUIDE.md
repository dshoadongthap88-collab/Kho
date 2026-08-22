# Hướng Dẫn Hệ Thống Multi-Tenant - Phân Quyền Theo Dự Án

## 📋 Tổng Quan

Hệ thống đã được nâng cấp với phân quyền nghiêm ngặt theo dự án (Multi-tenant). Mỗi user thuộc về một dự án cụ thể và chỉ được truy cập dữ liệu của dự án đó.

### Các Thay Đổi Chính

1. **Phân quyền theo dự án**: User thường chỉ xem được users và dữ liệu cùng dự án
2. **Sửa lỗi đăng nhập**: Middleware không còn can thiệp vào trang login
3. **Global Scope tự động**: Queries tự động lọc theo dự án
4. **Form quản lý user**: Thêm dropdown chọn dự án khi tạo/sửa user

---

## 🚀 Hướng Dẫn Triển Khai

### Bước 1: Chạy Migration

```bash
php artisan migrate
```

Migration `2026_08_22_000001_add_project_id_to_users_table.php` sẽ:
- Thêm cột `project_id` vào bảng `users`
- Tạo foreign key constraint tới bảng `projects`
- Tạo index để tối ưu performance

### Bước 2: Chạy Seeder

```bash
php artisan db:seed --class=AssignProjectToUsersSeeder
```

Seeder này sẽ:
- Gán `project_id` cho tất cả users hiện có
- Ưu tiên sử dụng `current_house_id` của user
- Fallback về `allowed_houses[0]` nếu không có `current_house_id`
- Hiển thị thống kê phân bố users theo dự án

**Lưu ý:** Seeder an toàn, có thể chạy nhiều lần mà không gây lỗi.

### Bước 3: Clear Cache

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## ✅ Kịch Bản Test và Verify

### Test 1: Kiểm Tra Đăng Nhập

**Mục đích:** Đảm bảo nhân viên có thể đăng nhập thành công

**Các bước:**
1. Mở trang login: `/login`
2. Nhập username/phone/email và password của một user
3. Click "Đăng nhập"

**Kết quả mong đợi:**
- ✅ Đăng nhập thành công
- ✅ Redirect đến trang chọn dự án `/select-house`
- ❌ KHÔNG bị lỗi middleware

**Nếu gặp lỗi:**
- Kiểm tra `app/Http/Kernel.php`: `CheckHouseContext` phải là middleware alias `house.context`, không nằm trong global web middleware
- Kiểm tra `routes/web.php`: route `/login` không có middleware `house.context`

---

### Test 2: Kiểm Tra Phân Quyền Chọn Dự Án

**Mục đích:** User chỉ được chọn dự án của mình

**Chuẩn bị:**
- Tạo 2 users: User A (project_id = 1), User B (project_id = 2)
- Tạo 1 admin (role = 'admin')

**Test với User A:**
1. Đăng nhập bằng User A
2. Tại trang `/select-house`, chỉ thấy dự án có ID = 1
3. Thử chọn dự án 1 và nhập password
4. Xác nhận truy cập thành công

**Test với Admin:**
1. Đăng nhập bằng Admin
2. Tại trang `/select-house`, thấy tất cả các dự án
3. Admin có thể chọn bất kỳ dự án nào

**Kết quả mong đợi:**
- ✅ User thường chỉ thấy dự án của mình
- ✅ Admin thấy tất cả dự án
- ✅ Thông báo lỗi rõ ràng nếu user cố chọn dự án không có quyền

---

### Test 3: Kiểm Tra Phân Quyền Xem Danh Sách Users

**Mục đích:** User chỉ xem được users cùng dự án

**Chuẩn bị:**
- User A, User B thuộc Dự án 1 (project_id = 1)
- User C, User D thuộc Dự án 2 (project_id = 2)
- Admin

**Test với User A:**
1. Đăng nhập User A (project_id = 1)
2. Vào trang `/hr/users` (nếu có quyền) hoặc gọi API `/api/hrm/employees`
3. Kiểm tra danh sách users

**Kết quả mong đợi:**
- ✅ User A chỉ thấy User A và User B (cùng project_id = 1)
- ❌ User A KHÔNG thấy User C và User D
- ✅ Admin thấy tất cả users

**Kiểm tra trong code:**
```php
// Trong HrmService::getEmployees()
$users = User::all(); // ProjectScope tự động lọc
```

---

### Test 4: Kiểm Tra Form Tạo/Sửa User

**Mục đích:** Admin có thể gán dự án cho user

**Các bước:**
1. Đăng nhập bằng Admin
2. Vào `/hr/users`
3. Click "Thêm nhân viên mới"
4. Điền thông tin và chọn dự án từ dropdown
5. Lưu lại

**Kết quả mong đợi:**
- ✅ Dropdown "Dự án" hiển thị tất cả dự án active
- ✅ Có thông báo: "Nhân viên chỉ được truy cập và xem dữ liệu của dự án này"
- ✅ User mới có `project_id` được gán đúng
- ✅ Khi sửa user, project_id hiện tại được chọn sẵn

**Test chỉnh sửa:**
1. Click "Sửa" một user
2. Thay đổi dự án
3. Lưu lại
4. Kiểm tra `project_id` trong database đã thay đổi

---

### Test 5: Kiểm Tra API HRM

**Mục đích:** API tuân thủ phân quyền dự án

**Test GET /api/hrm/employees:**
```bash
# Với User thuộc project_id = 1
curl -H "Authorization: Bearer <token>" http://localhost:8000/api/hrm/employees
```

**Kết quả mong đợi:**
- ✅ Chỉ trả về users có cùng project_id
- ✅ Admin thấy tất cả users

**Test GET /api/hrm/employees/{id}:**
```bash
# User A (project_id=1) cố xem User C (project_id=2)
curl -H "Authorization: Bearer <token>" http://localhost:8000/api/hrm/employees/C_ID
```

**Kết quả mong đợi:**
- ❌ Trả về 403 Forbidden
- ✅ Message: "Bạn không có quyền xem thông tin nhân viên này"

**Test PUT /api/hrm/employees/{id}:**
```bash
# User A cố cập nhật User C
curl -X PUT -H "Authorization: Bearer <token>" \
  -d '{"name":"New Name"}' \
  http://localhost:8000/api/hrm/employees/C_ID
```

**Kết quả mong đợi:**
- ❌ Trả về 403 Forbidden
- ✅ Message từ Exception

---

### Test 6: Kiểm Tra Livewire Components

**Test StockTransferForm:**
1. Đăng nhập User A (project_id = 1)
2. Vào trang tạo phiếu chuyển kho
3. Kiểm tra dropdown "Người nhận"

**Kết quả mong đợi:**
- ✅ Dropdown chỉ hiển thị users cùng project_id = 1

**Test NotificationManager:**
1. Đăng nhập Admin
2. Vào `/hr/notifications`
3. Tạo thông báo, chọn "Gửi cho tất cả"
4. Kiểm tra trong database

**Kết quả mong đợi:**
- ✅ Thông báo được gửi cho TẤT CẢ users (bypass ProjectScope với `withoutProjectScope()`)

---

### Test 7: Kiểm Tra Cô Lập Dữ Liệu

**Mục đích:** Đảm bảo users không thể truy cập dữ liệu dự án khác

**Kịch bản:**
1. User A (Dự án 1) tạo một sản phẩm/phiếu xuất kho
2. User C (Dự án 2) cố truy cập dữ liệu đó

**Kết quả mong đợi:**
- ✅ User C không thấy dữ liệu của User A
- ✅ BelongsToHouse trait tự động lọc theo `house_id`

**Lưu ý:** 
- `project_id` trong users khác với `house_id` trong products/inventories
- `project_id`: Xác định user thuộc dự án nào
- `house_id`: Xác định dữ liệu (sản phẩm, kho) thuộc dự án nào

---

## 🔧 Công Cụ Debug

### Kiểm Tra project_id Của User

```php
$user = User::find($userId);
echo "User: {$user->name}\n";
echo "Project ID: {$user->project_id}\n";
echo "Project Name: " . ($user->project ? $user->project->name : 'N/A') . "\n";
```

### Kiểm Tra ProjectScope Có Áp Dụng Không

```php
// Query thông thường - ProjectScope tự động áp dụng
$users = User::all();
echo "Count with scope: " . $users->count() . "\n";

// Query không có scope - thấy tất cả
$allUsers = User::withoutProjectScope()->get();
echo "Count without scope: " . $allUsers->count() . "\n";
```

### Kiểm Tra Phân Quyền Helper Methods

```php
$userA = User::find(1);
$userB = User::find(2);

// Kiểm tra có cùng dự án không
if ($userA->canViewUser($userB)) {
    echo "User A có quyền xem User B\n";
} else {
    echo "User A KHÔNG có quyền xem User B\n";
}

// Kiểm tra thuộc dự án cụ thể
if ($userA->belongsToProject(1)) {
    echo "User A thuộc dự án 1\n";
}

// Lấy các dự án user có quyền
$projects = $userA->getAccessibleProjects();
echo "User A có quyền truy cập: " . $projects->pluck('name')->join(', ') . "\n";
```

---

## 📊 Kiểm Tra Database

### Query Kiểm Tra Users Chưa Có project_id

```sql
SELECT id, name, email, project_id, current_house_id, allowed_houses
FROM users
WHERE project_id IS NULL;
```

**Kết quả mong đợi:** Không có record nào (sau khi chạy seeder)

### Query Thống Kê Users Theo Dự Án

```sql
SELECT 
    p.id as project_id,
    p.name as project_name,
    COUNT(u.id) as user_count
FROM projects p
LEFT JOIN users u ON p.id = u.project_id
GROUP BY p.id, p.name
ORDER BY p.id;
```

### Query Kiểm Tra Foreign Key

```sql
SHOW CREATE TABLE users;
```

Tìm dòng chứa:
```
CONSTRAINT `users_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL
```

---

## 🐛 Troubleshooting

### Lỗi: "Bạn không có quyền truy cập vào Dự án này"

**Nguyên nhân:** User chưa có `project_id` hoặc `project_id` không khớp

**Giải pháp:**
1. Chạy lại seeder: `php artisan db:seed --class=AssignProjectToUsersSeeder`
2. Hoặc cập nhật thủ công:
```sql
UPDATE users SET project_id = 1 WHERE id = <user_id>;
```

### Lỗi: "Column 'project_id' not found"

**Nguyên nhân:** Chưa chạy migration

**Giải pháp:**
```bash
php artisan migrate
```

### Lỗi: User không thể đăng nhập

**Nguyên nhân:** Middleware `CheckHouseContext` vẫn chạy cho route login

**Giải pháp:**
- Kiểm tra `app/Http/Kernel.php`
- Kiểm tra `routes/web.php`

### Lỗi: Query trả về tất cả users thay vì chỉ users cùng dự án

**Nguyên nhân:** ProjectScope không được áp dụng

**Kiểm tra:**
1. User model có `protected static function booted()` với `addGlobalScope(new ProjectScope())`?
2. User hiện tại đã authenticated chưa?
3. Có dùng `withoutProjectScope()` ở đâu không?

---

## 📝 Checklist Hoàn Thành

- [x] Migration `add_project_id_to_users_table` đã chạy
- [x] Seeder `AssignProjectToUsersSeeder` đã chạy
- [x] Tất cả users có `project_id`
- [x] Đăng nhập thành công (không lỗi middleware)
- [x] User thường chỉ thấy users cùng dự án
- [x] Admin thấy tất cả users
- [x] Form tạo/sửa user có dropdown chọn dự án
- [x] API HRM tuân thủ phân quyền
- [x] Livewire components lọc users đúng
- [x] TenantController kiểm tra project_id

---

## 🔐 Chính Sách Phân Quyền

### Admin (role = 'admin')
- ✅ Truy cập TẤT CẢ dự án
- ✅ Xem TẤT CẢ users
- ✅ Tạo/sửa/xóa users ở bất kỳ dự án nào
- ✅ Bypass ProjectScope

### User Thường
- ✅ Chỉ truy cập dự án của mình (project_id)
- ✅ Chỉ xem users cùng dự án
- ❌ KHÔNG xem được users dự án khác
- ❌ KHÔNG truy cập dữ liệu dự án khác
- ✅ ProjectScope tự động áp dụng

---

## 📚 Tham Khảo Code

### Files Quan Trọng

1. **Migration:** `database/migrations/2026_08_22_000001_add_project_id_to_users_table.php`
2. **Seeder:** `database/seeders/AssignProjectToUsersSeeder.php`
3. **Model:** `app/Models/User.php`
4. **Scope:** `app/Scopes/ProjectScope.php`
5. **Controller:** `app/Http/Controllers/TenantController.php`
6. **Service:** `app/Services/HrmService.php`
7. **API:** `app/Http/Controllers/Api/HrmController.php`
8. **Livewire:** `app/Livewire/Hr/UserManager.php`
9. **View:** `resources/views/livewire/hr/user-manager.blade.php`
10. **Middleware:** `app/Http/Middleware/CheckHouseContext.php`
11. **Kernel:** `app/Http/Kernel.php`
12. **Routes:** `routes/web.php`

---

## 💡 Best Practices

1. **Luôn sử dụng ProjectScope:** Query users qua `User::all()` hoặc `User::where()` - scope tự động áp dụng
2. **Admin bypass scope:** Khi cần xem tất cả users, logic kiểm tra `role === 'admin'` trong scope
3. **Explicit bypass:** Dùng `withoutProjectScope()` khi cần query tất cả users (ví dụ: gửi thông báo toàn hệ thống)
4. **Validation:** Luôn validate `project_id` khi tạo/sửa user
5. **Consistent data:** Đảm bảo mọi user mới đều có `project_id`

---

**Phiên bản:** 1.0  
**Ngày cập nhật:** 22/08/2026  
**Tác giả:** Development Team
