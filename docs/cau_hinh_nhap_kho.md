# Cấu hình Module Nhập Kho

Module quản lý việc nhập hàng từ nhà cung cấp hoặc khách hàng trả hàng vào hệ thống kho.

## 1. Thông tin chung (Header)
- **Mã phiếu nhập**: Tự động sinh `SI-YYYYMMDD-XXXX`.
- **Nhà cung cấp/Khách hàng**: Chọn từ danh sách hoặc nhập mới.
- **Ngày nhập**: Mặc định ngày hiện tại.
- **Ghi chú**: Thông tin bổ sung cho toàn bộ phiếu.

## 2. Chi tiết danh sách sản phẩm (Items)
Mỗi dòng nhập kho bao gồm các thông tin sau:

| Trường dữ liệu | Mô tả | Tính năng / Gợi ý |
| :--- | :--- | :--- |
| **Sản phẩm** | Mã hoặc tên sản phẩm | Tìm kiếm thông minh (không phân biệt hoa thường), tự chọn từ Datalist. |
| **ĐVT / Hãng** | Đơn vị tính và Hãng SX | **Tự động hiển thị**: Ưu tiên Unit -> Quy cách đóng gói (QC Hộp). |
| **Số lô (Batch)** | Mã số hiệu của lô hàng | **Tự động điền**: Lấy từ thông tin mặc định trong Danh mục sản phẩm. |
| **Hạn dùng (Expiry)** | Ngày hết hạn sản phẩm | **Tự động điền**: Lấy từ danh mục sản phẩm. |
| **Vị trí (Location)** | Vị trí lưu kho cụ thể | **Tự động điền**: Lấy từ vị trí mặc định của sản phẩm. |
| **Số lượng** | Lượng hàng nhập vào | Người dùng nhập tay. |

## 3. Nhập kho thông minh bằng Excel (Smart Import)
Hệ thống hỗ trợ tính năng tải lên tệp Excel/CSV linh hoạt mà không cần phải tuân thủ nghiêm ngặt theo một mẫu file cố định nào:
- **Tự động nhận diện cột (Dynamic Column Mapping)**: Hệ thống sẽ tự động quét dòng tiêu đề (trong 15 dòng đầu tiên) để tự nhận diện các cột dữ liệu dựa trên từ khóa như: *Mã vật tư, Tên vật tư, Số lượng, Đơn giá, Số lô, Hạn dùng, Vị trí kho...* ngay cả khi các cột này bị xáo trộn thứ tự.
- **Tự động lưu mã mới (Auto Create Product)**: Khi hệ thống quét thấy một mã vật tư mới trong file Excel (ví dụ: `VAP...`) chưa từng tồn tại trong **Danh Mục Vật Tư**, hệ thống sẽ:
  - Tự động nhận diện đây là vật tư mới.
  - Tự động lưu và thêm mới mã vật tư (kèm theo Tên, ĐVT, Đơn giá, Vị trí kho...) vào Danh mục Hệ thống ngay trong quá trình xử lý nhập kho.
  - Hiển thị ngay sản phẩm mới này trong Danh mục mà không cần thao tác thêm tay.

## 4. Luồng nghiệp vụ
1. Chọn Nhà cung cấp.
2. Thêm mới dòng sản phẩm.
3. **Tìm kiếm sản phẩm**: Gõ mã (ví dụ `p002`) hoặc tên. Hệ thống tự nhận diện kể cả khi gõ chữ thường.
4. **Tự động điền (Autofill)**: Ngay khi chọn sản phẩm, hệ thống tự điền Số lô, Hạn dùng, Vị trí và ĐVT.
5. Kiểm tra và chỉnh sửa lại (nếu thông tin thực tế khác với mặc định).
6. Nhấn "Lưu phiếu".
6. Hệ thống tạo giao dịch kho (`InventoryTransaction`) và cập nhật số lượng tồn kho.
