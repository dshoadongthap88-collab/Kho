# Cấu hình Mô hình Hệ thống (Các Ngôi Nhà)

Hệ thống được thiết kế theo mô hình các "Ngôi Nhà" (Tương ứng với các Module / Dự án độc lập). Trong đó, **Ngôi nhà HR** sẽ đóng vai trò là trung tâm đầu não, kiểm soát và điều phối toàn bộ hoạt động của các ngôi nhà vệ tinh khác.

## 1. Ngôi nhà HR (Quản trị Trung tâm & Phân quyền)
Ngôi nhà quản lý cấp cao nhất, nơi thực hiện việc tổ chức và kiểm soát hệ thống.
- **Quản lý Dự án (Các Ngôi nhà khác)**: Có quyền khởi tạo (Thêm mới), chỉnh sửa tên, và Xóa các dự án/ngôi nhà trong hệ thống.
- **Phân quyền người dùng**: Gán quyền truy cập, quy định rõ nhân sự nào được phép sử dụng những tính năng gì, ở trong ngôi nhà nào.
- **Nhận Báo cáo Tổng hợp**: Là nơi quy tụ dữ liệu. Nhận báo cáo tổng quan, số liệu thống kê xuyên suốt từ tất cả các ngôi nhà khác để ban lãnh đạo nắm bắt tình hình.

## 2. Ngôi nhà Thông tin NCC/KH
- **Mô tả**: Quản lý danh bạ đối tác liên quan đến dòng tiền và hàng hóa của kho.
- **Tính năng (CRUD)**:
  - Thêm mới đối tác (Mã đối tác, Tên, Số điện thoại, Email, Địa chỉ, Phân loại: NCC/KH).
  - Tìm kiếm và xem lịch sử giao dịch.
  - Cập nhật thông tin đối tác.

## 3. Ngôi nhà Kho (Quản lý Vận hành)
Quản lý các hoạt động trực tiếp tại kho hàng với cơ chế **Tự động hóa (Autofill)**:
- **Nhập kho**: Quản lý phiếu nhập. [Chi tiết cấu hình](docs/cau_hinh_nhap_kho.md)
  - Tìm kiếm thông minh (không phân biệt hoa/thường).
  - Tự động điền thông tin mặc định (Lô, Hạn dùng, Vị trí) từ danh mục.
- **Xuất kho**: Quản lý phiếu xuất. [Chi tiết cấu hình](docs/cau_hinh_xuat_kho.md)
  - Chọn lô hàng thực tế (Batch Selection): Tự động hiển thị bảng chọn lô.
  - Tự động điền dữ liệu tồn kho thực tế.
- **Tồn kho**: Theo dõi số lượng thực tế theo thời gian thực và vị trí.
- **Thu hồi phế phẩm**: Ghi nhận và quản lý phế phẩm, vật liệu thu hồi từ quá trình sản xuất hoặc xuất kho.
- **Kiểm kê kho**: Tạo phiếu kiểm kê, đối soát thực tế và máy, cân bằng kho tự động.

## 4. Ngôi nhà Sản phẩm & BOM
Quản lý danh mục hàng hóa và định mức sản xuất.
- **Sản phẩm & Vật tư**: Quản lý Mã SP, Tên, Đơn vị tính, Nhóm hàng.
- **Tên NVL (Nguyên vật liệu)**: Danh mục các loại nguyên liệu đầu vào.
- **BOM (Định mức NVL)**: Xây dựng công thức cấu thành thành phẩm từ NVL.

## 5. Ngôi nhà Tổng hợp
Các nghiệp vụ hỗ trợ và báo cáo quản trị cục bộ.
- **Mua hàng (Phiếu đề xuất)**: Lập yêu cầu mua vật tư dựa trên nhu cầu tồn kho/sản xuất.
- **Biên bản giao nhận hàng**: Ghi nhận việc bàn giao hàng hóa thực tế.
- **Báo cáo cục bộ**: Nhập - Xuất - Tồn, Báo cáo chi tiết giao dịch (Dữ liệu này sẽ được đồng bộ lên báo cáo tổng hợp của Ngôi nhà HR).

## 6. Ngôi nhà Giao hàng
Quản lý đầu ra và công nợ liên quan đến khách hàng.
- **Công nợ khách hàng**: Theo dõi số dư, hạn thanh toán và đối trừ công nợ sau khi giao hàng.
- **Báo cáo giao hàng**: Thống kê kết quả giao hàng theo ngày/khách hàng/đơn vị vận chuyển.
