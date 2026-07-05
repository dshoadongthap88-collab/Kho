# Cấu hình Admin

## Ngôi nhà HR
- **Tên đăng nhập:** 0708091050
- **Mật khẩu:** 101088

## Quản lý 7 Module Tiêu Chuẩn Toàn Hệ Thống

Toàn bộ hệ thống quản lý kho, tài sản và bảo dưỡng cho mọi dự án (các Ngôi nhà) được đồng bộ cấu trúc thành 7 Module chức năng thống nhất.

1. **NCC/KH** (Nhà cung cấp / Khách hàng)
2. **KHO** (Quản lý Nhập, Xuất, Tồn kho, Chuyển kho)
3. **DANH MỤC VẬT TƯ** (Phân loại, Vật tư, Máy móc, Thiết bị)
4. **THEO DÕI BẢO DƯỠNG** (Hệ thống ERP Bảo dưỡng thiết bị toàn diện)
5. **KẾ HOẠCH & MUA HÀNG** (Kế hoạch mua, Đề xuất vật tư)
6. **BÁO CÁO** (Báo cáo tổng hợp xuất nhập tồn, Báo cáo giao dịch)
7. **CHAT KHO** (Trao đổi nội bộ giữa các bộ phận trong kho)

### Nguyên tắc Đồng bộ & Dữ liệu
- **Cấu trúc 100% giống nhau**: Các Dự án (House) dùng chung bộ mã nguồn, giao diện, quy trình duyệt (Workflow) và quyền hạn chức năng.
- **Dữ liệu độc lập**: Các bảng dữ liệu đều được phân tách tự động theo cơ chế `house_id`. Dự án nào chỉ thấy và tương tác với dữ liệu nhập xuất, danh mục của riêng dự án đó (Multi-tenancy).
- **Phân quyền (Role/Permissions)**: Toàn bộ 7 module được quản lý chặt chẽ dựa trên chức năng, nhiệm vụ đã phân quyền từ Ngôi nhà HR (Master Admin).
- **Lưu trữ chung**: Cùng lưu trữ trên 1 database duy nhất (`laravel_5`), tối ưu tốc độ và tránh lỗi mất đồng bộ cấu trúc DB.
