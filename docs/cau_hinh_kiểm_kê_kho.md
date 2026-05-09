# Cấu hình Kiểm kê kho

## 1. Kiểm kê hàng ngày (Daily Inventory)
- **Mục tiêu:** Kiểm tra chéo một nhóm nhỏ vật tư mỗi ngày để duy trì tính chính xác.
- **Quy trình:**
    - Hệ thống tự động chọn 10 mã vật tư.
    - Tiêu chí chọn: Cùng vị trí hoặc vị trí gần nhau (sắp xếp theo cột `location`).
    - Quy tắc chống trùng lặp: Không chọn lại các mã đã được kiểm kê trong vòng 7 ngày qua.
- **Giao diện:**
    - Hiển thị danh sách 10 vật tư: Mã, Tên, Vị trí, Tồn hệ thống, Số lượng kiểm kê (ô nhập).
- **Xử lý dữ liệu:**
    - Sau khi nhập số lượng thực tế, hệ thống cho phép xác nhận để cập nhật tồn kho: `Tồn hệ thống = Số lượng kiểm kê`.
    - Ghi nhận vào lịch sử kiểm kê (`StockCount`) và giao dịch (`InventoryTransaction`).

## 2. Kiểm kê định kỳ (Periodic Inventory)
- **Mục tiêu:** Kiểm kê tổng thể toàn bộ kho định kỳ (tháng/quý/năm).
- **Quy trình:**
    - **Xuất Excel:** Xuất toàn bộ hoặc theo bộ lọc danh sách vật tư hiện có (Mã, Tên, Vị trí, Tồn hệ thống, Số lượng thực tế - để trống).
    - **Nhập Excel:** Người dùng nhập số lượng thực tế vào file Excel rồi tải lên hệ thống.
- **Xử lý dữ liệu:**
    - Hệ thống đối chiếu dữ liệu từ Excel.
    - Cập nhật tồn kho: `Tồn hệ thống = Số lượng thực tế` từ Excel.
    - Tự động tạo các bản ghi điều chỉnh (`adjust`) trong lịch sử giao dịch.
