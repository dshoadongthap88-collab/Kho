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

## 3. Luồng nghiệp vụ
1. Chọn Nhà cung cấp.
2. Thêm mới dòng sản phẩm.
3. **Tìm kiếm sản phẩm**: Gõ mã (ví dụ `p002`) hoặc tên. Hệ thống tự nhận diện kể cả khi gõ chữ thường.
4. **Tự động điền (Autofill)**: Ngay khi chọn sản phẩm, hệ thống tự điền Số lô, Hạn dùng, Vị trí và ĐVT.
5. Kiểm tra và chỉnh sửa lại (nếu thông tin thực tế khác với mặc định).
6. Nhấn "Lưu phiếu".
6. Hệ thống tạo giao dịch kho (`InventoryTransaction`) và cập nhật số lượng tồn kho.
