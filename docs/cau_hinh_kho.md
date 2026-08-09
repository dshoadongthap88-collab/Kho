# Cấu hình Module Tồn Kho

Module Tồn Kho (Inventory) quản lý số lượng, vị trí và thông tin chi tiết của các vật tư/sản phẩm đang có trong kho.

## 1. Thông tin Tồn kho (Inventory)
- **Danh sách tồn kho**: Hiển thị chi tiết từng sản phẩm, mã, vị trí, số lượng khả dụng, số lượng đang giữ (reserved) và cảnh báo tồn kho tối thiểu.
- **Lọc và sắp xếp**: Có thể lọc theo trạng thái tồn kho (Đủ, Cảnh báo, Nguy hiểm), Thương hiệu, Vị trí kho và sắp xếp theo các cột.
- **CRUD cơ bản**: 
  - Đọc (Read): Danh sách và tìm kiếm thông minh.
  - Thêm/Sửa (Update/Create): Chỉnh sửa thông tin vật tư trực tiếp từ danh sách.
  - Xóa (Delete): Xóa bản ghi tồn kho khi cần thiết.

## 2. Khởi tạo Tồn kho thông minh bằng Excel (Smart Inventory Initialization)
Hệ thống cho phép người dùng khởi tạo dữ liệu tồn kho hàng loạt bằng cách tải lên file Excel/CSV:
- **Tự động nhận diện cột (Smart Column Mapping)**: Khi nhập từ Excel lên, hệ thống sẽ tự động quét và bóc tách các cột dữ liệu dựa trên từ khóa linh hoạt như *Mã SP, Tên SP, ĐVT, Hãng SX, Vị trí, Số lượng, Số lô, Hạn dùng...* mà không cần bắt buộc file mẫu phải cố định thứ tự các cột.
- **Tự động thêm mã vật tư mới (Auto Create Product)**:
  - Khi hệ thống quét thấy một mã vật tư hoàn toàn mới (Ví dụ: `VAP...`) chưa có sẵn trên hệ thống, nó sẽ tự động lưu lại vào **Danh Mục Vật Tư** kèm theo Tên, Số lượng, Vị trí và hiển thị ngay trên danh sách Tồn kho.
  - Quá trình này giúp QA/Thủ kho giảm thiểu thao tác thêm tay từng mã mới khi kiểm kê kho hoặc chuyển đổi dữ liệu từ hệ thống cũ.

## 3. Quy trình QA và Backend
- Logic backend tại `InventoryImport` đã thực thi kiểm tra và xử lý toàn bộ quá trình tự động tạo mới/cập nhật thông tin.
- Hệ thống hỗ trợ báo lỗi và xác nhận trực quan trên giao diện sau khi nạp file hoàn tất, đảm bảo QA kiểm tra an toàn dữ liệu đầu vào.
