# Cấu hình Module HR (Trung tâm Quản trị)

Module HR hiện tại được thiết lập để đóng vai trò là **Trung tâm Điều khiển (Admin Center)** cao nhất trong hệ thống, với khả năng quản lý toàn bộ các "Ngôi nhà" (Dự án/Chi nhánh) khác.

## 1. Cơ chế Multi-Tenant và Tự động hóa

- **Mô hình Dữ liệu**: Hệ thống sử dụng kiến trúc Multi-Database. 
  - Database gốc (thường là `laravel` hoặc `mysql`) chứa thông tin chung: Users, Projects (Danh sách Ngôi nhà), Phân quyền.
  - Mỗi Ngôi nhà (trừ Ngôi nhà HR) sẽ có một Database riêng biệt biệt lập hoàn toàn (ví dụ: `laravel_1`, `laravel_2`, `laravel_9`).
  - Toàn bộ dữ liệu được lưu chung trên một server cơ sở dữ liệu ("chung ổ").
- **Tự động hóa Khởi tạo**: Khi Admin tạo mới một "Ngôi nhà" trong module Quản lý Dự án của HR, hệ thống tự động:
  1. Chạy lệnh SQL tạo Database mới `laravel_{id}`.
  2. Tự động chạy tiến trình `migrate` vào Database mới đó để tạo toàn bộ cấu trúc bảng (Vật tư, Kho, BOM, v.v.) giống 100% so với cấu hình chuẩn.

## 2. Phân chia Giao diện (Frontend)

- **Ngôi nhà HR (House ID = 5)**:
  - Chỉ hiển thị Menu Quản trị: Bảng điều khiển, Quản lý dự án, Phân quyền hệ thống, Báo cáo tổng hợp.
  - Ẩn hoàn toàn các Menu nghiệp vụ (Kho, Sản xuất, Theo dõi bảo dưỡng) để tránh thao tác nhầm hoặc lỗi truy xuất dữ liệu không tồn tại.
- **Các Ngôi nhà Nghiệp vụ (Khác 5)**:
  - Ẩn Menu Quản trị HR.
  - Hiển thị đầy đủ chức năng quản lý Kho, Vật tư, Máy móc. Dữ liệu các chức năng này hoàn toàn được trỏ vào Database độc lập của Ngôi nhà đang chọn.

## 3. Module Mua Hàng (Trung Tâm Mua Hàng)

- **Vị trí**: Nằm trên thanh công cụ (navbar), **phía sau** menu "⭐ NGÔI NHÀ HR (TRUNG TÂM ADMIN)", cùng hàng ngang. Biểu tượng: 🛒 TRUNG TÂM MUA HÀNG.
- **Quyền truy cập**: Chỉ hiển thị khi đăng nhập với quyền Admin và đang ở Ngôi nhà HR (House ID = 5).

### 3.1 Chức năng chính

1. **Phân tích Top Vật Tư Sử Dụng Nhiều Nhất (60 ngày)**
   - Truy vấn dữ liệu xuất kho (`stock_out_items`) trong 60 ngày gần nhất.
   - **Tổng hợp số liệu từ tất cả các kho dự án** (cross-house).
   - Hiển thị bảng Top vật tư, bao gồm: Mã vật tư, Tên, Đơn vị, Tổng số lượng đã xuất, Dự án xuất nhiều nhất.

2. **Cảnh báo Vật Tư Tồn Dưới Mức Tối Thiểu (min_stock)**
   - Quét tất cả `Product` có `inventory.quantity` < `min_stock` từ mọi kho dự án.
   - Hiển thị bảng cảnh báo: Mã vật tư, Tên, Tồn hiện tại, Mức tối thiểu, Thiếu bao nhiêu, Thuộc dự án nào.

3. **Tạo Đề Xuất Mua Hàng**
   - Cho phép Admin tạo đề xuất mua hàng trực tiếp từ màn hình phân tích.
   - **Đơn mua được phân bổ về từng dự án cụ thể** (chọn dự án đích khi tạo đề xuất).
   - Form tạo đề xuất gồm: Chọn dự án, Chọn mã vật tư, Số lượng đề xuất, Ngày giao dự kiến, Ghi chú.

### 3.2 Cấu trúc kỹ thuật

- **Route**: `hr.purchase-center` → `App\Livewire\Hr\PurchaseCenter`
- **Layout**: Sử dụng `layouts.app` (giống các component HR khác).
- **Truy vấn cross-house**: Bỏ qua Global Scope `BelongsToHouse` bằng `withoutGlobalScope('house')` để lấy dữ liệu từ tất cả các dự án.
- **Bảng dữ liệu**: Sử dụng bảng `purchase_plans` hiện có, thêm thông tin `house_id` để gắn đề xuất về đúng dự án.

## 4. Khuyến nghị QA & Vận hành

- Không tạo dữ liệu nghiệp vụ (VD: Vật tư) trong Ngôi nhà HR.
- Các model dùng chung (User, Project) phải luôn được khai báo `protected $connection = 'mysql';`.
- Mật khẩu đăng nhập vào mỗi Ngôi nhà (PIN) được cấp cho từng User thông qua màn hình Phân Quyền.
