# CẤU HÌNH BÁO CÁO CHI TIẾT GIAO DỊCH NGÀY

## Mục đích
Hỗ trợ việc theo dõi và in ấn báo cáo chi tiết các giao dịch trong ngày (hoặc một khoảng thời gian), tập trung vào các thông số xuất kho bảo dưỡng/sửa chữa.

## Chức năng
1. **Lọc dữ liệu**:
   - Từ ngày ... đến ngày.
   - Tìm kiếm sản phẩm, loại giao dịch, người thực hiện.
2. **Xuất Excel**:
   - Xuất toàn bộ dữ liệu đang được lọc ra file Excel.
3. **In Báo Cáo PDF**:
   - Chỉ in các dòng giao dịch đã được tick chọn (checkbox).
   - Route in: `/reports/transaction-detail/print`
   - File template: `resources/views/warehouse/transaction-detail-print.blade.php`
4. **Nội dung phiếu in**:
   - **Header**: Công ty Cổ phần Đầu tư và Thi công Hạ tầng V- ALPHA. Báo cáo chi tiết giao dịch ngày.
   - **Chữ ký**: Bố trí 4 phần ký nhận ở cuối phiếu in với khoảng trống để ký:
     - THỦ KHO
     - QUẢN LÝ KHO
     - TT.KTSC
     - P.QLTB
   - **Tổng hợp**:
     - Tổng mã tài sản đã sử dụng.
     - Tổng mã vật tư đã sử dụng.
   - **Bảng dữ liệu (6 cột)**:
     - STT
     - MÃ TÀI SẢN
     - MÃ VẬT TƯ
     - SỐ LƯỢNG
     - BP SỬ DỤNG
     - GHI CHÚ
