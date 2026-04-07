# Cấu hình Module Kho

Module Quản lý Kho bao gồm các chức năng cốt lõi sau để đảm bảo quy trình quản lý vật tư, hàng hóa được chặt chẽ. Dưới đây là các chức năng yêu cầu (Mức độ ưu tiên: CRUD - Create, Read, Update, Delete).

## 1. Mua hàng / Nhập kho
- **Chức năng chính**: Quản lý các phiếu nhập kho (phiếu mua hàng, nhập trả lại, nhập khác).
- **Tính năng ưu tiên (CRUD)**:
  - **Create**: Tạo mới phiếu nhập kho (chọn Nhà cung cấp, chọn vật tư/hàng hóa, số lượng, giá nhập, vị trí lưu kho).
  - **Read**: Xem danh sách và chi tiết phiếu nhập (lọc theo khoảng thời gian, trạng thái, nhà cung cấp).
  - **Update**: Sửa thông tin phiếu nhập (chỉ áp dụng khi phiếu chưa được xác nhận/chốt sổ).
  - **Delete**: Xóa/Hủy phiếu nhập kho (khi có sai sót và chưa ghi nhận tăng tồn kho).

## 2. Bán hàng / Xuất kho
- **Chức năng chính**: Quản lý các phiếu xuất kho (xuất bán hàng, xuất sản xuất, xuất trả NCC, xuất khác).
- **Tính năng ưu tiên (CRUD)**:
  - **Create**: Tạo mới phiếu xuất kho (chọn Khách hàng/Bộ phận nhận, chọn vật tư/hàng hóa, số lượng từ vị trí nào).
  - **Read**: Xem danh sách và chi tiết phiếu xuất (lọc theo ngày, trạng thái, khách hàng/bộ phận).
  - **Update**: Sửa thông tin phiếu xuất (kỉ áp dụng khi chưa xác nhận xuất).
  - **Delete**: Xóa/Hủy phiếu xuất kho.

## 3. Quản lý Đối tác (Nhà cung cấp / Khách hàng)
- **Chức năng chính**: Quản lý danh bạ đối tác liên quan đến dòng tiền và hàng hóa của kho.
- **Tính năng ưu tiên (CRUD)**:
  - **Create**: Thêm mới đối tác (Mã đối tác, Tên, Số điện thoại, Email, Địa chỉ, Mã số thuế, Phân loại: NCC/KH/Cả hai).
  - **Read**: Xem danh sách và tìm kiếm chi tiết đối tác, lịch sử giao dịch.
  - **Update**: Cập nhật thông tin đối tác.
  - **Delete**: Xóa/Ẩn đối tác khỏi hệ thống (Chỉ cho phép ẩn/deactivate nếu đã phát sinh dữ liệu giao dịch).

## 4. Quản lý Tồn kho
- **Chức năng chính**: Theo dõi số lượng, tình trạng và giá trị hàng hóa đang có trong kho theo thời gian thực.
- **Tính năng ưu tiên (CRUD/Logic)**:
  - **Read**: Xem danh sách tồn kho hiện tại (Mã hàng, Tên hàng, Đơn vị tính, Số lượng tồn thực tế, Vị trí lưu kho).
  - **Update (Cấu hình)**: Thiết lập/Sửa đổi cảnh báo tồn kho tối thiểu / tối đa cho từng mặt hàng.
  - Theo dõi Thẻ kho (Inventory Ledger): Liệt kê biến động In - Out - Balance của từng sản phẩm.

## 5. Báo cáo kho
- **Chức năng chính**: Cung cấp các công cụ báo cáo, thống kê phục vụ phân tích quyết định.
- **Các báo cáo cốt lõi**:
  - Báo cáo Tổng hợp Nhập - Xuất - Tồn theo kỳ (Khoảng thời gian Tùy chọn).
  - Báo cáo chi tiết giao dịch kho.
  - Báo cáo hàng chậm luân chuyển.
  - Báo cáo danh sách hàng tồn kho dưới mức tối thiểu.
- Cho phép xuất khẩu (Export) các báo cáo ra format Excel / PDF.

## 6. BOM (Định mức nguyên vật liệu - NVL)
- **Chức năng chính**: Quản lý công thức sản xuất, cấu thành thành phẩm từ nhiều nguyên vật liệu.
- **Tính năng ưu tiên (CRUD)**:
  - **Create**: Lập danh mục BOM (Chọn thành phẩm, thêm danh sách chi tiết các NVL cấu thành và tỷ lệ, định mức số lượng).
  - **Read**: Xem danh sách BOM và chi tiết thành phần của mỗi BOM.
  - **Update**: Chỉnh sửa cấu hình BOM, nâng cấp version BOM (Nếu cần thay đổi định mức NVL).
  - **Delete**: Xóa báo cáo / công thức BOM (Nếu cấu hình sai và BOM này chưa từng được dùng để sản xuất).

## 7. Kiểm kê kho
- **Chức năng chính**: Hỗ trợ quy trình kiểm kê định kỳ để xử lý chênh lệch giữa thực tế và phần mềm.
- **Tính năng ưu tiên (CRUD)**:
  - **Create**: Tạo "Phiếu Yêu Cầu Kiểm Kê" (chọn kho cần kiểm, nhóm hàng cần kiểm hoặc kiểm toàn bộ).
  - **Update**: Cập nhật số lượng đếm được thực tế vào hệ thống phần mềm (Duyệt theo từng dòng sản phẩm).
  - **Read**: Xem và phân tích bảng chênh lệch (Số lượng trên máy vs Số lượng thực tế -> Thừa / Thiếu).
  - Cân bằng kho: Hệ thống tự động phát sinh phiếu "Nhập kho bù thừa" hoặc "Xuất kho bù thiếu" sau khi chốt phiếu kiểm kê.
  - **Delete**: Hủy biên bản kiểm kê khi chưa xử lý cân bằng.

## 8. Quản lý Sản phẩm / Vật tư (Hàng hóa)
- **Chức năng chính**: Quản lý danh mục và cấu hình chi tiết cho các sản phẩm, nguyên vật liệu trong hệ thống.
- **Tính năng ưu tiên (CRUD)**:
  - **Create**: Thêm mới Sản phẩm/Vật tư (Mã SP định danh, Tên sản phẩm, Đơn vị tính, Nhóm hàng/Danh mục, Loại: Thành phẩm hay NVL, Mô tả, Hình ảnh, Giá vốn tham khảo, Giá bán định mức).
  - **Read**: Xem danh sách hàng hóa (Lọc theo danh mục, trạng thái kinh doanh, tìm kiếm theo tên/mã). Khung nhìn chi tiết thông tin và thẻ kho đi kèm.
  - **Update**: Cập nhật thông tin cơ bản (Đổi ĐVT, cập nhật giá, điều chỉnh danh mục).
  - **Delete**: Xóa hệ thống (nếu sản phẩm chưa phát sinh bất kỳ giao dịch kho nào) hoặc Ẩn/Ngừng hoạt động (Deactivate) đối với sản phẩm đã có dữ liệu để bảo toàn lịch sử.
- **Tính năng mở rộng**: Quản lý cây Danh mục sản phẩm đa cấp, Quản lý các đơn vị quy đổi (nếu có, ví dụ: 1 Thùng = 10 Hộp = 100 Cái).
