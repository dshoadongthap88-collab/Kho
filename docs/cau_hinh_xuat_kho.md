# Cấu hình Module Xuất Kho

Module quản lý việc xuất sản phẩm từ kho cho sản xuất hoặc khách hàng. Module này có logic thông minh để ưu tiên xuất hàng theo lô tồn kho.

## 1. Thông tin chung (Header)
- **Mã phiếu xuất**: Tự động sinh `SO-YYYYMMDD-XXXX`.
- **Khách hàng / Bộ phận**: Chọn từ danh sách đối tác hoặc bộ phận nhận.
- **Loại xuất**: Sản xuất (Production) hoặc Khách hàng (Customer).
- **Ngày xuất**: Mặc định ngày hiện tại.

## 2. Chi tiết danh sách sản phẩm (Items)

| Trường dữ liệu | Mô tả | Logic Tự động hóa |
| :--- | :--- | :--- |
| **Sản phẩm** | Mã/tên sản phẩm | Tìm kiếm **Live** (không phân biệt hoa thường). |
| **ĐVT / Hãng** | Quy cách sản phẩm | **Tự động**: Ưu tiên Đơn vị -> QC Hộp -> QC Thùng. |
| **Số lô (Batch)** | Lô hàng cụ thể | **Thông minh**: Tự động lấy lô có tồn kho; hiện bảng chọn nếu có >1 lô. |
| **Hạn dùng (Expiry)** | Ngày hết hạn | **Tự động**: Điền theo lô hàng được chọn. |
| **Vị trí (Location)** | Vị trí thực tế | **Tự động**: Điền theo vị trí thực tế của lô hàng trong kho. |
| **Số lượng** | Lượng xuất | Người dùng nhập tay. |

## 3. Luồng nghiệp vụ Thông minh
1. **Tìm kiếm sản phẩm**: Gõ nhanh mã (ví dụ `p001`). Hệ thống nhận diện ngay lập tức.
2. **Xử lý tồn kho theo lô**:
    - **Trường hợp 1 (Chỉ có 1 lô)**: Hệ thống tự điền ngay Số lô, Hạn dùng và Vị trí của lô đó.
    - **Trường hợp 2 (Nhiều lô hàng)**: Một cửa sổ (Modal) sẽ hiện ra, liệt kê các lô đang có sẵn kèm số lượng tồn. Người dùng chọn lô muốn xuất.
    - **Trường hợp 3 (Hết hàng)**: Nếu kho không còn, hệ thống sẽ lấy thông tin mặc định từ Danh mục sản phẩm làm gợi ý.
3. **In ấn**: Cho phép chọn/bỏ chọn mặt hàng cần in trên phiếu bàn giao.
4. **Lưu phiếu**: Hệ thống trừ tồn kho thực tế và tạo lịch sử giao dịch.

## 4. Cấu hình Chữ ký (Khi in Phiếu xuất kho)
- Giao diện In ấn hiển thị **5 chữ ký** trên cùng một hàng theo đúng thứ tự:
  1. **Nhân viên vận hành**
  2. **Tổ trưởng/ trưởng ca QLTB / vận hành** (Mặc định gợi ý, VD: LÊ HOÀNG NAM)
  3. **Nhân viên KTSC** (Chọn lọc từ danh sách nhân viên trên hệ thống)
  4. **Thủ kho** (Mặc định gợi ý từ lần nhập trước, VD: ĐẶNG HỮU HÒA)
  5. **Tổ trưởng / trưởng ca** (Mặc định gợi ý, VD: LÊ ĐÌNH HƯƠNG)
- **Trường nhập liệu**: Các ô nhập tên người ký được cung cấp trong form xuất kho, tự động lưu lại vào `Session` để tự động điền trong các lần xuất kho tiếp theo. Riêng ô *Nhân viên KTSC* hỗ trợ lọc từ danh sách người dùng hệ thống.
