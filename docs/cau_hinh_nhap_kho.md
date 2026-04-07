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
| **Sản phẩm** | Mã sản phẩm hoặc tên sản phẩm | Dropdown tìm kiếm từ danh mục sản phẩm (Active). |
| **Hãng sản xuất** | Tên hãng của sản phẩm | Tự động hiển thị khi chọn sản phẩm (Read-only). |
| **Số lô (Batch)** | Mã số hiệu của lô hàng | Nhập tay. |
| **Hạn dùng (Expiry)** | Ngày hết hạn sản phẩm | Chọn từ lịch (Không cảnh báo khi nhập). |
| **Vị trí (Location)** | Vị trí lưu kho cụ thể | Gợi ý vị trí cũ trong tồn kho; cho phép nhập tay. |
| **Số lượng** | Lượng hàng nhập vào | Số nguyên hoặc thập phân (tùy cấu hình UOM). |

## 3. Luồng nghiệp vụ
1. Chọn Nhà cung cấp.
2. Thêm mới dòng sản phẩm.
3. Chọn sản phẩm -> Hệ thống tự điền Mã/Tên/Hãng và **Gợi ý vị trí**.
4. Nhập Số lô, Hạn dùng và Số lượng thực tế.
5. Kiểm tra và nhấn "Lưu phiếu".
6. Hệ thống tạo giao dịch kho (`InventoryTransaction`) và cập nhật số lượng tồn kho.
