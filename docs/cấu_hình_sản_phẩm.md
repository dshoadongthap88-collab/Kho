# Cấu hình Danh mục Sản phẩm / Vật tư

Module quản lý Sản phẩm (Hàng hóa, Nguyên vật liệu) là hồ sơ cơ sở cho toàn bộ các giao dịch nhập/xuất kho. Dưới đây là các yêu cầu và trường thông tin quan trọng để quản lý một sản phẩm.

## 1. Các trường thông tin bắt buộc

Theo yêu cầu nghiệp vụ, một Sản phẩm cần lưu trữ các thông tin chi tiết sau:

- **Mã sản phẩm (Mã SP / SKU)**: 
  - Mã định danh riêng biệt (Hệ thống có thể tự động sinh hoặc người dùng tự tùy chỉnh nhập tay).
  - *Đặc tính*: Bắt buộc, Không được phép trùng lặp (Unique).

- **Tên sản phẩm**: 
  - Tên gọi rõ ràng của mặt hàng (Ví dụ: Paracetamol 500mg, Vỏ hộp A...).
  - *Đặc tính*: Bắt buộc nhập.

- **Đơn vị tính (Unit of Measure - UOM)**:
  - Đơn vị cơ bản dùng để đo lường, giao dịch và kiểm kê tính toán giá trị hàng hóa (Ví dụ: Cái, Hộp, Thùng, Chai, Viên, Kg...).
  - *Đặc tính*: Bắt buộc. Mọi thẻ kho và giao dịch đều ghi nhận dựa trên đơn vị cơ sở này.

- **Quy cách (Specification / Packaging)**:
  - Mô tả rõ cách đóng gói, định dạng hoặc các thông số kỹ thuật bổ trợ (Ví dụ: 1 Thùng x 24 Lon, 1 Hộp x 10 Vỉ, Kích thước 40x60cm...).
  - *Mục đích*: Giúp phân biệt các dạng đóng gói, hỗ trợ nhân viên kho bốc xếp chuẩn xác.

- **Hãng sản xuất (Brand / Manufacturer)**:
  - Nguồn gốc thương hiệu hoặc tên nhà cung ứng, nhà sản xuất ra sản phẩm.
  - *Mục đích*: Hỗ trợ lọc danh sách, kiểm kê theo hãng, hỗ trợ lên báo cáo phân bổ.

- **Vị trí kho (Bin / Rack Location)**:
  - Vị trí cụ thể của sản phẩm nằm ở dãy nào, kệ nào, tầng nào trong kho vật lý (Ví dụ: `Kệ A-Tầng 2-Ngăn 5`).
  - *Mục đích*: Điều phối viên và thủ kho có thể tìm kiếm và lấy hàng nhanh chóng dựa trên hướng dẫn vị trí, rút ngắn thời gian xếp và dỡ hàng hoá.

- **Tình trạng (Status)**:
  - Theo dõi vòng đời lưu thông của hàng hóa trong hệ thống:
    - **Đang kinh doanh (Active)**: Sản phẩm được phép tìm kiếm và giao dịch (bán ra, mua vào, cấu thành BOM).
    - **Ngừng kinh doanh (Inactive/Archived)**: Sản phẩm bị khóa, không được phép thêm vào các phiếu Nhập/Xuất mới nhưng vẫn giữ nguyên vẹn dữ liệu trong các phiếu giao dịch lịch sử và Thẻ kho.

## 2. Giao diện chức năng ưu tiên (CRUD)

- **Create (Thêm mới)**: Form nhập liệu bao gồm 7 trường cấu hình ở trên (Mã, Tên, ĐVT, Quy cách, Hãng, Vị trí kho, Tình trạng), trong đó mặc định trạng thái ban đầu là *Đang kinh doanh*.
- **Read (Danh sách)**: Bảng dữ liệu sản phẩm.
  - Cho phép tìm kiếm bằng Mã sản phẩm, Tên sản phẩm.
  - Cho phép lọc dữ liệu theo bộ lọc: *Hãng sản xuất* và *Tình trạng*.
  - Hiển thị *Vị trí kho* ngay trên danh sách tổng quan.
- **Update (Chỉnh sửa)**: Cập nhật mọi trường trừ dữ liệu "Mã sản phẩm" trong trường hợp mã cũ đã có giao dịch. Chuyển đổi trạng thái Đang kinh doanh sang Ngừng kinh doanh tại form này.
- **Delete (Xóa/Ẩn)**: Kiểm tra ràng buộc chặt chẽ. Nếu mặt hàng chưa có Thẻ kho/phiếu nào -> Xóa vĩnh viễn (Hard delete). Nếu đã có -> Chuyển trạng thái sang *Ngừng kinh doanh*.
