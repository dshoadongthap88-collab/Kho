# Cấu hình Kiểm kê kho

## 1. Nguyên tắc thiết kế & Tối ưu hóa UI/UX
- **Sắp xếp theo Vị trí (A-B-C...):** Để hỗ trợ tối đa việc kiểm kê thực tế tại kho, danh sách kiểm kê luôn được sắp xếp theo thứ tự bảng chữ cái của vị trí (`location`) của vật tư. Nhân viên kiểm kho sẽ đi tuần tự từ kệ A, kệ B, kệ C... giúp tăng 200% hiệu suất kiểm đếm và tránh nhảy cóc.
- **Hiển thị Vị trí làm Trọng tâm:** Trên giao diện và bản in, cột "Mã Vật Tư" cũ được thay thế hoàn toàn bằng cột **"Vị trí"** (để nhân viên biết ngay kệ cần kiểm). Đồng thời, thông tin **"Mã Vật Tư"** được gom gọn nằm ngay dưới **"TÊN VẬT TƯ"** để hiển thị tường minh và gọn gàng nhất.
- **Ngăn chặn trùng lặp tuyệt đối:** Quy trình truy vấn tự động lọc bỏ các mã vật tư trùng lặp và tên vật tư trùng lặp, đảm bảo mỗi loại vật tư chỉ xuất hiện duy nhất một lần trên phiếu kiểm kê.

## 2. Kiểm kê hàng ngày (Daily Inventory)
- **Mục tiêu:** Kiểm tra chéo một nhóm nhỏ vật tư mỗi ngày để duy trì tính chính xác.
- **Quy trình:**
    - Hệ thống tự động chọn 10 mã vật tư.
    - Tiêu chí chọn: Sắp xếp theo thứ tự bảng chữ cái của vị trí (`location` A-B-C...).
    - Quy tắc chống trùng lặp: Không chọn lại các mã đã được kiểm kê trong vòng 7 ngày qua. Lọc bỏ trùng tên hoặc trùng mã vật tư.
- **Giao diện:**
    - Hiển thị danh sách 10 vật tư: Vị trí, TÊN VẬT TƯ & Mã Vật Tư, Tồn hệ thống, Số lượng kiểm kê (ô nhập).
- **Xử lý dữ liệu:**
    - Sau khi nhập số lượng thực tế, hệ thống cho phép xác nhận để cập nhật tồn kho: `Tồn hệ thống = Số lượng kiểm kê`.
    - Ghi nhận vào lịch sử kiểm kê (`StockCount`) và giao dịch (`InventoryTransaction`).

## 3. Kiểm kê định kỳ (Periodic Inventory)
- **Mục tiêu:** Kiểm kê tổng thể toàn bộ kho định kỳ (tháng/quý/năm).
- **Quy trình:**
    - **Xuất Excel:** Xuất toàn bộ danh sách vật tư sắp xếp theo vị trí A-B-C, không trùng lặp tên/mã (Vị trí, TÊN VẬT TƯ & Mã Vật Tư, Tồn hệ thống, Số lượng thực tế - để trống).
    - **Nhập Excel:** Người dùng nhập số lượng thực tế vào file Excel rồi tải lên hệ thống.
- **Xử lý dữ liệu:**
    - Hệ thống đối chiếu dữ liệu từ Excel.
    - Cập nhật tồn kho: `Tồn hệ thống = Số lượng thực tế` từ Excel.
    - Tự động tạo các bản ghi điều chỉnh (`adjust`) trong lịch sử giao dịch.
