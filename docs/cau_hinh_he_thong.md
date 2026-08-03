# CẤU HÌNH HỆ THỐNG CHUẨN (MASTER CONFIGURATION)
*Được chốt và chuẩn hóa: Tuyệt đối không thay đổi cấu trúc này*

## I. CẤU TRÚC 6 MODULE LÕI (TRONG ĐÓ CÓ 7 IN 1)
Toàn bộ hệ thống quản lý ERP Kho & Thiết bị của tất cả các dự án (Ngôi nhà) bắt buộc phải tuân theo cấu trúc điều hướng (Navigation) và tính năng gồm đúng 6 Module sau:

1. **NCC/KH** (Quản lý Nhà cung cấp và Khách hàng)
2. **KHO** (Quản lý nhập, xuất, tồn, chuyển kho, kiểm kê, thu hồi)
   - *Bao gồm cả DANH MỤC VẬT TƯ và DANH MỤC THIẾT BỊ*
3. **THEO DÕI BẢO DƯỠNG**
   - *Đây là Trung tâm điều hành thiết bị (Trang chủ tổng hợp 7 in 1)*
   - Menu Dropdown (Cố định tuyệt đối không thay đổi):
     1. TRANG CHỦ TỔNG HỢP (7 IN 1)
     2. Cập nhật giờ ODO hàng ngày
     3. Định mức bảo dưỡng (BOM)
     4. Phiếu bảo dưỡng & Lịch
     5. Giao ca / Nhật ký
4. **KẾ HOẠCH & MUA HÀNG** (Lập kế hoạch mua hàng, Đề xuất vật tư)
5. **BÁO CÁO** (Báo cáo chi tiết giao dịch, Báo cáo tồn kho tổng hợp)
6. **CHAT KHO** (Trao đổi nội bộ hệ thống)

> **Lưu ý QA**: Bất kỳ màn hình hay tính năng nào làm sai lệch số lượng hoặc thứ tự 6 module này trên thanh Menu chính (Nav bar) đều bị coi là vi phạm thiết kế chuẩn.

## II. KIẾN TRÚC DỮ LIỆU & BẢO MẬT (MULTI-TENANCY)
- **Database**: Sử dụng chung 1 CSDL duy nhất (`laravel_5`).
- **Phân tách dữ liệu**: Mọi bảng dữ liệu giao dịch và danh mục (trừ bảng users và cài đặt hệ thống lõi) đều BẮT BUỘC có trường `house_id`.
- **Cơ chế lọc tự động (Global Scope)**: Hệ thống sử dụng `CheckHouseContext` middleware và Trait `BelongsToHouse` để tự động lọc dữ liệu. User thuộc dự án nào thì chỉ nhìn thấy, chỉnh sửa và tạo mới dữ liệu gắn với `house_id` của dự án đó.
- **Master Admin (Ngôi nhà HR)**: Dự án trung tâm dành cho Quản trị viên cấp cao nhất để cấu hình phân quyền (Roles/Permissions) và quản lý danh sách các Ngôi nhà (Dự án). Ngôi nhà HR không tham gia vào các nghiệp vụ xuất nhập tồn của 7 module chuẩn.

## III. QUY TẮC PHÁT TRIỂN & BẢO TRÌ MÃ NGUỒN (QA)
1. **Mọi thay đổi giao diện**: Áp dụng chung cho toàn bộ các House. Không được code cứng (hard-code) logic riêng biệt cho một House cụ thể nào trong views hoặc controller.
2. **Quản lý BOM (Định mức)**: Cấu trúc BOM của thiết bị được lưu trữ động trong cột `bom_details` (JSON) của bảng `assets` để đảm bảo có thể thêm không giới hạn các loại dầu và bộ lọc.
3. **Null-Safety**: Khi gọi các relation trong Blade views (VD: `$item->product->name`), bắt buộc phải dùng null-safe operator (`$item->product?->name` hoặc `$item->product->name ?? 'N/A'`) để tránh lỗi 500 khi vật tư bị xóa.
4. **Định tuyến (Routing)**: Các định tuyến của hệ thống kho phải nằm trong file `routes/warehouse.php` và được bảo vệ bằng middleware kiểm tra quyền hạn chặt chẽ. Đảm bảo tên route được prefix bằng `warehouse.` ở những nơi cấu hình nhóm.
