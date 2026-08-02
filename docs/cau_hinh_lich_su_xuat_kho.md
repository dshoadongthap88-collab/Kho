# Cấu hình Lịch Sử Phiếu Xuất Kho

Màn hình **Lịch Sử Phiếu Xuất Kho** (tab "list") hiển thị toàn bộ các phiếu xuất đã tạo, hỗ trợ tìm kiếm, lọc, xuất báo cáo và in ấn.

## 1. Thanh Tìm Kiếm & Lọc
- **Từ ngày / Đến ngày**: Lọc phiếu xuất theo khoảng thời gian tạo.
- **Ô tìm kiếm**: Tìm theo Mã phiếu hoặc Tên khách hàng / Bộ phận.

## 2. Cột Dữ liệu
| Cột | Mô tả |
|---|---|
| Mã phiếu | Mã tự động (VD: SO-20260802-0001) |
| Ngày tạo | Ngày giờ tạo phiếu |
| Khách hàng / Bộ phận | Tên bộ phận nhận hàng |
| Người liên hệ / Mã TS | Tên người nhận và mã tài sản liên quan |
| Loại xuất | Sửa chữa / Giao hàng / Hủy |
| Tổng tiền | Tổng giá trị phiếu |
| Ghi chú | Ghi chú của phiếu |
| Thao tác | Các nút hành động |

## 3. Cột Thao Tác
Mỗi dòng phiếu có 3 nút thao tác:
1. **🖨️ In phiếu**: In phiếu xuất kho đơn lẻ.
2. **✏️ Sửa phiếu** (màu cam): Mở phiếu lên form để chỉnh sửa. Sau khi sửa, nhấn **"Lưu Thay Đổi"** để cập nhật. Hệ thống sẽ tự động:
   - Hoàn trả tồn kho theo số lượng xuất cũ.
   - Xóa giao dịch kho cũ.
   - Tạo lại giao dịch kho mới theo dữ liệu sửa.
   - Giữ nguyên Mã phiếu (không đổi mã).
3. **🗑️ Xóa phiếu**: Xóa phiếu và tự động hoàn trả tồn kho.

## 4. Thao Tác Hàng Loạt
- **Chọn checkbox** trên nhiều dòng để kích hoạt thanh hành động hàng loạt.
- **IN GHÉP**: In nhiều phiếu được chọn trên cùng một lần in.
- **XÓA**: Xóa nhiều phiếu cùng lúc và hoàn trả tồn kho.

## 5. Xuất Báo Cáo
- **EXCEL**: Xuất toàn bộ danh sách theo bộ lọc hiện tại sang file Excel.
- **IN PDF**: In bản in danh sách phiếu xuất.
