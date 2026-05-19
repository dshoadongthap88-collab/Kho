# Cấu hình tính năng Báo Cáo Tồn Kho Thu Hồi (Nằm trong Module: Tổng Hợp)

## 1. Mục tiêu
Tạo tính năng lập Báo Cáo Tồn Kho Thu Hồi (Stock Recovery Report), giúp người dùng tổng hợp và quản lý việc thu hồi nguyên vật liệu (NVL) đã được đề xuất mua từ Phiếu Đề Xuất Mua Hàng.

## 2. Thông tin chính trên Báo Cáo Tồn Kho Thu Hồi
Mỗi mặt hàng trên báo cáo tồn kho thu hồi cần bao gồm các trường thông tin sau:
- **Mã NVL**: Mã định danh của nguyên vật liệu.
- **Tên NVL**: Tên gọi đầy đủ của nguyên vật liệu.
- **Số lượng**: Số lượng đã thu hồi thực tế.
- **Hãng SX**: Hãng sản xuất hoặc nhà cung cấp tương ứng.
- **ĐVT**: Đơn vị tính (kg, chiếc, mét, hộp, v.v.).

## 3. Logic thu hồi và liên kết Purchase Order (Thông minh)
Tính năng hỗ trợ tìm kiếm và thêm nguyên vật liệu vào báo cáo thu hồi được thiết kế thông minh để giảm thiểu sai sót:

- **Cơ chế gợi ý**: Khi gõ tên sản phẩm hoặc số PO, hệ thống tự động lọc và gợi ý các NVL đã được đề xuất mua và đã về kho.
- **Phân loại trạng thái**: Dựa theo trạng thái PO và tình trạng nhập kho:
  - Các NVL đang chờ thu hồi (đã duyệt mua nhưng chưa thu hồi).
  - Các NVL đã thu hồi một phần hoặc toàn bộ.
- **Bộ lọc thông minh**: Hiển thị các NVL có thể thu hồi từ các PO đã được duyệt để tránh thiếu sót thu hồi.

## 4. Các bước xây dựng dự kiến
1. Cấu hình bảng dữ liệu, Model và Migration cho tính năng Stock Recovery (Stock Recoveries & Stock Recovery Details).
2. Xây dựng Controller hoặc Livewire Component để load NVL từ các PO đã duyệt.
3. Thiết kế giao diện Form Báo Cáo Tồn Kho Thu Hồi trực quan:
   - Giao diện dạng bảng để nhập mã, số lượng thu hồi...
   - Tích hợp autocomplete/dropdown filter cho ô `Tên NVL` và `Số PO`.
4. Tích hợp liên kết với Phiếu Đề Xuất Mua Hàng để tự động cập nhật trạng thái thu hồi.
