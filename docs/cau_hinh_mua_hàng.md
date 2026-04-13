# Cấu hình tính năng Phiếu Đề Xuất Mua Hàng (Nằm trong Module: Tổng Hợp)

## 1. Mục tiêu
Tạo tính năng lập Phiếu Đề Xuất Mua Hàng (Purchase Request), giúp người dùng tổng hợp và đề xuất mua thêm nguyên vật liệu (NVL) dựa trên danh sách các nguyên vật liệu đang ở dưới mức tồn kho tối thiểu hoặc đã thiếu hụt trong hoạt động sản xuất.

## 2. Thông tin chính trên Phiếu Mua Hàng
Mỗi mặt hàng trên phiếu mua hàng cần bao gồm các trường thông tin sau:
- **Mã NVL**: Mã định danh của nguyên vật liệu.
- **Tên NVL**: Tên gọi đầy đủ của nguyên vật liệu.
- **Số lượng**: Số lượng cần mua bổ sung.
- **Hãng SX**: Hãng sản xuất hoặc nhà cung cấp tương ứng.
- **ĐVT**: Đơn vị tính (kg, chiếc, mét, hộp, v.v.).

## 3. Logic đề xuất tự động (Thông minh)
Tính năng hỗ trợ tìm kiếm và thêm nguyên vật liệu vào phiếu được thiết kế thông minh để giảm thiểu sai sót:

- **Cơ chế gợi ý**: Khi gõ tên sản phẩm, hệ thống tự động lọc và gợi ý các danh mục nguyên vật liệu đang trong tình trạng cảnh báo (cần đặt thêm).
- **Phân loại cảnh báo**: Dựa theo định mức tồn kho, hệ thống phân loại:
  - Các NVL sắp hết hàng (dưới mức cảnh báo an toàn).
  - Các NVL đã hết hoặc thiếu hàng so với tiến độ sản xuất hiện tại.
- **Bộ lọc loại trừ**: **Tuyệt đối không hiển thị** (hoặc ẩn khỏi gợi ý mua mới) những nguyên vật liệu đang có "đủ tồn kho" để tránh việc mua dư thừa, tăng chi phí lưu kho.

## 4. Các bước xây dựng dự kiến
1. Cấu hình bảng dữ liệu, Model và Migration cho tính năng Purchase (Purchase Orders & Purchase Order Details).
2. Xây dựng Controller hoặc Livewire Component để load NVL dưới mức quy định.
3. Thiết kế giao diện Form Phiếu Mua Hàng trực quan:
   - Giao diện dạng bảng để nhập mã, số lượng...
   - Tích hợp autocomplete/dropdown filter cho ô `Tên NVL` kết nối API cảnh báo tồn kho.
4. Tích hợp cảnh báo mức tồn kho tối thiểu (Min Stock) vào màn hình mua hàng để làm tiêu chí cho query SQL gợi ý danh mục nguyên vật liệu.
