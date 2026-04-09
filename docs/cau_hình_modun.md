# Cấu hình Module Hệ thống Kho (5 Modules)

Hệ thống được cấu trúc thành 5 module chính để tối ưu hóa quy trình quản lý.

## 1. Module: Thông tin NCC/KH
- **Mô tả**: Quản lý danh bạ đối tác liên quan đến dòng tiền và hàng hóa của kho.
- **Tính năng (CRUD)**:
  - Thêm mới đối tác (Mã đối tác, Tên, Số điện thoại, Email, Địa chỉ, Phân loại: NCC/KH).
  - Tìm kiếm và xem lịch sử giao dịch.
  - Cập nhật thông tin đối tác.

## 2. Module: Kho
Quản lý các hoạt động trực tiếp tại kho hàng.
- **Nhập kho**: Quản lý phiếu nhập (chọn NCC, vật tư, số lượng, vị trí lưu kho).
- **Xuất kho**: Quản lý phiếu xuất (chọn KH/Bộ phận, vật tư, số lượng).
- **Tồn kho**: Theo dõi số lượng thực tế theo thời gian thực và vị trí.
- **Kiểm kê kho**: Tạo phiếu kiểm kê, đối soát thực tế và máy, cân bằng kho tự động.

## 3. Module: Sản phẩm & BOM
Quản lý danh mục hàng hóa và định mức sản xuất.
- **Sản phẩm & Vật tư**: Quản lý Mã SP, Tên, Đơn vị tính, Nhóm hàng.
- **Tên NVL (Nguyên vật liệu)**: Danh mục các loại nguyên liệu đầu vào.
- **BOM (Định mức NVL)**: Xây dựng công thức cấu thành thành phẩm từ NVL.

## 4. Module: Tổng hợp
Các nghiệp vụ hỗ trợ và báo cáo quản trị.
- **Mua hàng (Phiếu đề xuất)**: Lập yêu cầu mua vật tư dựa trên nhu cầu tồn kho/sản xuất.
- **Biên bản giao nhận hàng**: Ghi nhận việc bàn giao hàng hóa thực tế.
- **Báo cáo tổng hợp**: Nhập - Xuất - Tồn, Báo cáo chi tiết giao dịch, Báo cáo hàng chậm luân chuyển.

## 5. Module: Giao hàng
Quản lý đầu ra và công nợ liên quan đến khách hàng.
- **Công nợ khách hàng**: Theo dõi số dư, hạn thanh toán và đối trừ công nợ sau khi giao hàng.
- **Báo cáo giao hàng**: Thống kê kết quả giao hàng theo ngày/khách hàng/đơn vị vận chuyển.

