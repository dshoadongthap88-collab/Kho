# CẤU HÌNH CHUYỂN KHO (ĐÃ CHỐT)

*Tài liệu này lưu trữ cấu hình cố định của Module Chuyển Kho sau khi đã hoàn thiện lập trình.*

## 1. Thông tin chung
- **Mã phiếu**: Tự động sinh (Định dạng: `TF-YYYYMMDD-XXXX`).
- **Chi nhánh gửi (Từ Chi nhánh)**: Liên kết động với danh sách Dự án (Projects). Tự động lấy Chi nhánh hiện tại mà User đang thao tác.
- **Chi nhánh nhận (Đến Chi nhánh)**: Liên kết động với danh sách Dự án (Projects). Cho phép chọn danh sách các nhà khác với nhà hiện tại.

## 2. Thông tin nhân sự (Liên kết Module HR)
- **Người gửi**: Tự động lấy tên User đang tạo phiếu, kèm theo Số điện thoại.
- **Người nhận**: Lấy từ danh sách User hệ thống (Module HR), tự động hiển thị Số điện thoại tương ứng khi chọn.

## 3. Quản lý chi tiết vật tư
Đã tích hợp **Thanh tìm kiếm nhanh (Global Search)** ngay trên bảng danh sách vật tư.
Khi tìm kiếm sẽ hiển thị cả Tên, Mã, ĐVT và **Số lượng tồn kho (Realtime)** để người dùng chọn nhanh.

Các cột trong bảng chi tiết:
1. **STT**: Sử dụng bộ đếm tự động ($loop->iteration).
2. **Mã & Tên vật tư**: Nhập tay hoặc chọn bằng Datalist (Danh sách xổ xuống thông minh có tích hợp tìm kiếm).
3. **Số lượng tồn**: Tự động truy xuất từ dữ liệu Tồn kho của Chi nhánh gửi ngay khi chọn mặt hàng.
4. **Số lượng xuất**: User nhập (kiểm tra không được vượt quá số lượng tồn).
5. **Vị trí**: Tự động lấy từ vị trí tồn kho (hoặc danh mục) của mặt hàng đó.
6. **Ghi chú mặt hàng**: Ghi chú tự do.
7. **Nút Xóa dòng**: Dành cho giao diện nhập liệu.

## 4. Quy trình nghiệp vụ tự động (2 Bước)

### Bước 1: Gửi đi (Pending)
- **Hành động**: Kho gửi lập phiếu và bấm Hoàn tất.
- **Hệ thống xử lý**:
  - Tự động trừ số lượng tồn kho tại Kho gửi.
  - Sinh lịch sử giao dịch (InventoryTransaction) loại "transfer_out".
  - Phiếu ở trạng thái **Chờ xác nhận**.
  - 🔔 Hệ thống tự động gửi **Chuông thông báo chat** tới thủ kho / người dùng ở Chi nhánh nhận.

### Bước 2: Nhận hàng (Completed)
- **Hành động**: Kho nhận vào màn hình danh sách, kiểm tra và bấm "Xác nhận nhận hàng".
- **Hệ thống xử lý**:
  - Chuyển database connection sang Chi nhánh nhận để xử lý.
  - Tự động sao chép thông tin Vật tư (Product catalog) nếu Chi nhánh nhận chưa từng có mã này.
  - Tự động **Cộng số lượng tồn kho** vào kho của Chi nhánh nhận.
  - Sinh lịch sử giao dịch (InventoryTransaction) loại "transfer_in" tại Chi nhánh nhận.
  - Đổi trạng thái phiếu thành **Hoàn thành**.

## 5. Cấu hình Mẫu In (Phiếu Chuyển Kho Nội Bộ)
Hỗ trợ chức năng in 1 phiếu trực tiếp và chức năng **In hàng loạt** (In nhiều phiếu).
- **Tiêu đề in**: 
  - Tên Công ty: CÔNG TY CP ĐẦU TƯ VÀ HẠ TẦNG V-ALPHA
  - Dự án: [Tên chi nhánh hiện tại]
  - Tiêu đề: PHIẾU CHUYỂN KHO NỘI BỘ
- **Thông tin Header (Layout 2 cột đối xứng ngang nhau)**:
  - (Cột trái) Ngày chuyển  ---  *(Dòng trống để cân bằng)* (Cột phải)
  - (Cột trái) Từ Chi nhánh  ---  Đến Chi nhánh (Cột phải)
  - (Cột trái) Người gửi (+SĐT)  ---  Người nhận (+SĐT) (Cột phải)
- **Chân trang chữ ký**: Bố cục 3 cột cân đối:
  - **Người Gửi** (Khoảng trống ký)
  - **Người Nhận** (Khoảng trống ký)
  - **Người Phê Duyệt** (Khoảng trống ký)
*(Đã loại bỏ chữ "Ký và ghi rõ họ tên" và cột "Thủ kho" theo yêu cầu).*
