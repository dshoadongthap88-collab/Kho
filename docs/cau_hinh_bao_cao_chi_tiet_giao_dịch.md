# Cấu hình Báo Cáo Chi Tiết Giao Dịch

Bản in báo cáo chi tiết giao dịch (Transaction Detail Print) được thiết kế để hiển thị thông tin cụ thể các giao dịch xuất/nhập/chuyển kho.

## 1. Thông tin Phiếu & Người thực hiện (Nhóm theo Phiếu)
Báo cáo sẽ tự động nhóm các giao dịch theo từng **Số Phiếu ĐNSC/BD**. Với mỗi phiếu, hệ thống sẽ tách thành một ngăn riêng rẽ bao gồm:
- **Số Phiếu ĐNSC/BD** & **Nhân viên sửa chữa**: Hiển thị trên cùng của mỗi ngăn.
- **Thống kê nhanh của riêng Phiếu đó**: Hiển thị tổng số lượng mã tài sản, mã vật tư và số lượng giao dịch thuộc phiếu đó, giúp người dùng dễ dàng nắm bắt thông tin phân bổ trong ngày.

## 2. Cấu trúc Bảng dữ liệu (Mỗi Phiếu 1 bảng)
Mỗi ngăn Phiếu sẽ có một bảng dữ liệu chi tiết, các cột được sắp xếp theo chuẩn:
1. **STT** (Số thứ tự)
2. **MÃ TÀI SẢN**
3. **MÃ VẬT TƯ**
4. **SỐ LƯỢNG** (Bao gồm giá trị và đơn vị tính)
5. **BP SỬ DỤNG** (Bộ phận sử dụng)
6. **GHI CHÚ**