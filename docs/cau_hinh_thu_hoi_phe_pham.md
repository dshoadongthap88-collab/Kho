# Cấu hình Module: Thu hồi phế phẩm

## 1. Mục đích
Ghi nhận và quản lý các vật tư, hàng công cụ bị hư hỏng hoặc phế phẩm cần thu hồi sau quá trình sử dụng (đã qua xuất kho).

## 2. Nguồn lấy dữ liệu và Tự động hóa
Dữ liệu được kế thừa hoàn toàn từ **Module Xuất kho** (thuộc Module 2: Kho). 
Khi người dùng tạo phiếu xuất kho và điền vào ô "Số lượng thu hồi" > 0, hệ thống sẽ **tự động** tạo các phiếu thu hồi phế phẩm tương ứng, không cần nhập thủ công lại.

## 3. Thông tin hiển thị và thu thập
Hệ thống sẽ tự động trích xuất các thông tin sau từ phiếu xuất kho sang phiếu thu hồi phế phẩm:
- **Tên mã vật tư / Tên vật tư**
- **Mã vật tư**
- **Số lượng thu hồi** (Nhập tại lúc xuất kho)
- **Ngày thu hồi** (Lấy theo ngày xuất kho)
- **Mã tài sản** (Kế thừa từ mã tài sản trên phiếu xuất kho)

## 4. Tính năng Báo cáo Phế phẩm (Hàng công cụ)
Cung cấp công cụ báo cáo thống kê tình hình thu hồi phế phẩm đặc thù cho nhóm **hàng công cụ**.
- **Bộ lọc**:
  - Từ ngày ... 
  - Đến ngày ...
- **Nội dung báo cáo**:
  - **Tổng số lượng**: Tổng cộng có bao nhiêu phế phẩm được thu hồi trong khoảng thời gian đã chọn.
  - **Chi tiết mã vật tư**: Bao gồm danh sách các **Mã vật tư** nào cấu thành nên tổng số phế phẩm đó, cùng với số lượng tương ứng cho từng mã.

## 5. Quy trình thực hiện
1. **Thực hiện tại Xuất Kho**: Khi vào giao diện Xuất Kho, điền đầy đủ các thông tin phiếu xuất. Tại danh sách vật tư xuất, nếu có phế phẩm cần thu hồi, điền số lượng vào cột "SL thu hồi" (> 0).
2. **Lưu Phiếu Xuất**: Bấm "Lưu phiếu", hệ thống xử lý xuất kho đồng thời tự động sinh phiếu **Thu hồi phế phẩm**.
3. **Xem Báo Cáo**: Truy cập chức năng **Kho -> Thu hồi phế phẩm**, chọn khoảng thời gian (Từ ngày - Đến ngày) để xem báo cáo thống kê tổng hợp số lượng phế phẩm, chi tiết từng mã vật tư và các mã tài sản liên quan.


## 6. Lưu ý quan trọng về Logic Tồn Kho
- **KHÔNG cộng lại vào Tồn kho chính**: Phế phẩm được thu hồi thông qua chức năng này được hiểu là hàng loại, không còn giá trị sử dụng nguyên bản. Hệ thống **tuyệt đối không cộng dồn số lượng này vào tồn kho** hay tự động tạo phiếu nhập kho.
- Dữ liệu thu hồi chỉ lưu độc lập, mang tính chất ghi nhận và phục vụ mục đích xuất báo cáo thống kê hao hụt/phế phẩm của hàng công cụ.
