# Hướng dẫn Cấu hình và Tính toán Nhu cầu Nguyên vật liệu (MRP)

Màn hình Nhu cầu NVL giúp người dùng lập kế hoạch sản xuất để dự trù và chuẩn bị đầy đủ lượng Nguyên vật liệu (NVL) cần thiết trước khi bắt đầu tạo lệnh sản xuất.

Hệ thống hoạt động dựa trên cơ sở Công thức chế tạo / Định mức tiêu hao (BOM).

## Các bước Tính toán

1. **Thêm Thành phẩm vào Kế hoạch:**
   - Tại mục "Mục tiêu Sản xuất" (Bên trái), chọn Thành phẩm mà bạn dự tính muốn sản xuất (Lưu ý: Chỉ những thành phẩm đã được khai báo Định mức BOM tại module **Quản lý BOM** mới hiện lên).
   - Nhập số lượng dự kiến muốn làm.
   - Nhấn **➕ Thêm vào Kế hoạch**. Món hàng này sẽ hiện ra ở List danh sách phía dưới. Bạn có thể thêm không hạn chế số lượng nhiều loại Thành phẩm khác nhau. Hệ thống sẽ tự động gộp các Nguyên vật liệu trùng lặp.

2. **Theo dõi Bảng phân tích nhu cầu BOM:**
   Hệ thống sẽ ngay lập tức tính toán và liệt kê ở màn hình bên phải:
   - **Tên NVL / Mã / ĐVT.**
   - **Cần Dùng:** Tổng số lượng NVL được nhân lên từ định mức.
   - **Tồn Kho:** Số lượng thực tế đang có sẵn ở các Vị trí kho (Lấy tự động theo thời gian thực).
   - **Tình trạng:** Sáng đèn **[ĐỦ HÀNG]** (Màu xanh) hoặc chớp nháy cảnh báo **[THIẾU SỐ LƯỢNG]** (Màu đỏ).

## Chuyển tiếp Tự động (Giải pháp liền mạch)

- **🖨️ In Yêu Cầu (Trình Kế hoạch):** Nút này sẽ tự xuất ra bản báo cáo Danh Sách Nhu Cầu NVL theo form văn bản A4 đơn giản. Giám đốc/Quản lý có thể xem để phê duyệt Kế hoạch dự phòng.
- **📤 Trình mua hàng:** Khi Kế hoạch được duyệt (hoặc khi nhân viên thấy thiếu NVL cần mua), thay vì phải gõ lại từng mã NVL bị thiếu, chỉ cần gõ nút này. Toàn bộ các NVL **bị thiếu hụt** (màu Đỏ) sẽ tự động được gửi sang Form `Tạo Mới` của Màn hình **Phiếu Đề xuất Mua hàng**, tích hợp đầy đủ số lượng và đơn giá ngay trong giỏ hàng. Màn hình Nhu cầu NVL này sẽ trở lại trạng thái Trống để chuẩn bị cho kế hoạch mới tiếp theo của công ty.
