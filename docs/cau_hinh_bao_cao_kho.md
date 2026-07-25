# Cấu hình Báo Cáo & Phân Tích Tổng Hợp Kho

Tài liệu này quy định các yêu cầu và loại biểu đồ cần sử dụng trong module **Báo Cáo Tổng Hợp Kho** nhằm mang lại cái nhìn trực quan và chính xác nhất về tình trạng hàng hóa.

## 1. Biểu đồ Bar Chart (Cột)
- **Mục đích**: So sánh trực quan sản lượng **Nhập - Xuất - Tồn** của các nhóm sản phẩm hoặc từng sản phẩm cụ thể.
- **Yêu cầu dữ liệu**:
  - Trục X: Tên sản phẩm / Mã sản phẩm / Khung thời gian (tháng/quý).
  - Trục Y: Số lượng.
  - Thể hiện 3 cột đứng cạnh nhau trên cùng một trục X tương ứng với 3 chỉ số: Tổng số lượng Nhập, Tổng số lượng Xuất, và Số lượng Tồn hiện tại.

## 2. Biểu đồ Pie Chart (Tròn)
- **Mục đích**: Phân tích cơ cấu **Tồn kho theo Danh mục** (Category).
- **Yêu cầu dữ liệu**:
  - Hiển thị tỷ trọng (phần trăm %) của từng danh mục hàng hóa so với tổng trữ lượng tồn kho hoặc tổng giá trị tồn kho.
  - Giúp quản lý nắm bắt được loại nguyên vật liệu hoặc thành phẩm nào đang chiếm không gian hoặc vốn nhiều nhất.

## 3. Biểu đồ Pareto (80/20)
- **Mục đích**: Xác định nhóm sản phẩm có tác động lớn nhất đến kho (những mặt hàng chiếm phần lớn giá trị xuất kho hoặc sinh ra doanh thu/chi phí tồn lớn nhất).
- **Yêu cầu dữ liệu**:
  - Trục X: Các mặt hàng được sắp xếp theo thứ tự giá trị giảm dần.
  - Trục Y chính (Cột): Giá trị / Số lượng của từng mặt hàng.
  - Trục Y phụ (Đường Line): Tỷ lệ luỹ kế (từ 0% đến 100%).
  - Ứng dụng để lọc ra nhóm 20% hàng hoá quan trọng nhất tạo ra 80% luân chuyển kho, từ đó có chiến lược quản trị Tồn an toàn khắt khe hơn.

## 4. Biểu đồ HeatMap
- **Mục đích**: Kiểm tra tình trạng sức khoẻ tồn kho, cụ thể là **hạn sử dụng (Expiry Date)** và **hàng tồn lâu / chậm luân chuyển (Dead Stock)**.
- **Yêu cầu dữ liệu**:
  - Trục X / Trục Y có thể là: Thời gian lưu kho (30 ngày, 60 ngày, >90 ngày) vs Vị trí kho / Danh mục.
  - **Màu sắc**:
    - Xanh lá: Hàng xài ổn định, xuất nhập liên tục, hạn dùng còn dài.
    - Vàng/Cam: Hàng đang tiến tới mức cận date hoặc lưu kho khá lâu so với chu kỳ.
    - Đỏ: Hàng quá hạn (Expired) hoặc hàng tồn đọng mốc (Dead stock).
  - Cung cấp hành động tức thời: Click vào ô màu Đỏ/Cam để hiển thị danh sách các Lô hàng (Batch) cần thanh lý hoặc xuất tiêu huỷ / xuất trước (FIFO).

## 5. Thống kê Nhân viên lãnh hàng nhiều nhất
- **Mục đích**: Cảnh báo hoặc thống kê top nhân viên nhận số lượng vật tư nhiều nhất trong kỳ, giúp rà soát định mức và mục đích sử dụng.
- **Yêu cầu dữ liệu**:
  - Dữ liệu được trích xuất từ **Phiếu xuất kho**.
  - **Thông tin tên nhân viên**: Sử dụng trực tiếp từ cột **"Người nhận" (receiver_contact)** trên phiếu xuất kho.

## 6. Báo Cáo Ngày (Daily Report)
- **Mục đích**: Cung cấp bức tranh toàn cảnh về các giao dịch kho diễn ra trong một ngày cụ thể.
- **Nội dung báo cáo**:
  - **Tổng hợp giao dịch vật tư**: Tổng số lượng mã vật tư đã thực hiện giao dịch trong ngày, bao gồm số lượng mã đã **Nhập kho**, **Xuất kho**, **Chuyển kho**, và **Thu hồi**.
  - **Thống kê đơn hàng**: Tổng số đơn xuất kho trong 1 ngày.
  - **Phân loại xuất kho**: Thống kê chi tiết đã xuất bao nhiêu mã **Tài sản** và bao nhiêu mã **Vật tư** cho dự án.
- **Yêu cầu Phiếu in (Print/Export)**:
  - Khi người dùng in báo cáo, **Phiếu in phải thể hiện rõ tên nhân viên đang đăng nhập** (người xuất/in báo cáo) đối với dự án hiện tại, để đảm bảo minh bạch thông tin người lập báo cáo.
