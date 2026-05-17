# 🚚 CẤU HÌNH CHUYỂN KHO (STOCK TRANSFER)

Tài liệu hướng dẫn nghiệp vụ và đặc tả tính năng của phân hệ **Chuyển kho giữa các Dự án/Chi nhánh** trong hệ thống Quản lý Kho.

---

## 📌 1. Yêu cầu Nghiệp vụ
* **Mục tiêu:** Hỗ trợ điều chuyển vật tư, thiết bị giữa các địa điểm dự án (ví dụ: Dự án Hóc Môn, Dự án Hậu Nghĩa, Dự án Cần Giờ...) một cách nhanh chóng, chính xác.
* **Quy trình hoạt động:**
  1. Thủ kho tại Nhà kho nguồn (ví dụ: Hóc Môn) lập **Phiếu chuyển kho**.
  2. Chọn Nhà kho đích cần chuyển đến.
  3. Chọn các vật tư cần chuyển đi từ **Danh sách vật tư hiện đang có tồn kho thực tế** tại kho nguồn.
  4. Sau khi hoàn tất, hệ thống tự động:
     * **Trừ tồn kho** tại kho nguồn (Hóc Môn) và ghi nhận giao dịch `transfer_out`.
     * **Cộng tồn kho** tương ứng tại kho đích (Hậu Nghĩa/Cần Giờ...) và ghi nhận giao dịch `transfer_in`. Nếu kho đích chưa có mã vật tư này, hệ thống sẽ tự động đồng bộ và khởi tạo mới danh mục vật tư ở kho đích để đảm bảo tính toàn vẹn.

---

## ⚙️ 2. Đặc tả Kỹ thuật của Ô Chọn Vật Tư Thông Minh
Để hạn chế tối đa thao tác nhập tay và tránh sai sót mã vật tư, mục **"Mã vật tư / sản phẩm"** trong bảng chi tiết chuyển kho được cấu hình như sau:

### 🔹 Nguồn Dữ Liệu Tồn Kho (Data Source)
* Chỉ truy vấn và hiển thị các vật tư có số lượng tồn kho thực tế lớn hơn `0` tại nhà kho hiện tại (`quantity > 0`).
* **Truy vấn tối ưu:**
  ```php
  $products = Product::whereHas('inventory', function ($q) {
      $q->where('quantity', '>', 0);
  })->get(['code', 'name', 'unit']);
  ```

### 🔹 Trải Nghiệm Người Dùng (UI/UX)
* Sử dụng thẻ `<datalist>` HTML5 kết hợp Livewire đem lại khả năng phản hồi cực nhanh, hỗ trợ cả thiết bị di động và máy tính:
  * **Tìm kiếm song song:** Cho phép người dùng gõ tìm kiếm theo **Mã vật tư** hoặc **Tên vật tư** đều được.
  * **Định dạng hiển thị:** `[Mã Vật Tư] - [TÊN VẬT TƯ]` (ví dụ: `VAP01035 - Kích thủy lực dùng hơi khí nén 50T`).
  * **Tự động tách mã:** Khi người dùng click chọn một vật tư từ danh sách gợi ý, hệ thống sẽ tự động phân tách phần chuỗi và chỉ lưu trữ đúng **Mã Vật Tư** (`code`) vào ô nhập liệu để hoàn tất quy trình lưu trữ chuẩn xác.

---

## 🛠️ 3. Danh sách các Tệp nguồn liên quan
* **Livewire Component:** [StockTransferForm.php](file:///d:/Project/app/Livewire/Warehouse/StockTransferForm.php)
* **Blade View:** [stock-transfer-form.blade.php](file:///d:/Project/resources/views/livewire/warehouse/stock-transfer-form.blade.php)
* **Tài liệu đặc tả:** [cau_hinh_chuyen_kho.md](file:///d:/Project/docs/cau_hinh_chuyen_kho.md)
