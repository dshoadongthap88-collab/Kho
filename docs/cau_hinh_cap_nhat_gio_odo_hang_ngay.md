# Cấu Hình và Cập Nhật Giờ Odo Hàng Ngày

## Tổng Quan

Module **Quản lý số giờ Odo** (Odo Manager) cho phép theo dõi số giờ vận hành của tài sản (máy móc, thiết bị) hàng ngày. Hệ thống ghi nhận số giờ hiện tại (current hours) của từng tài sản, theo dõi tình trạng bảo dưỡng và lưu trữ lịch sử đọc.

## Cấu Hình

### Yêu Cầu Hệ Thống

- Laravel 10+ với Livewire
- Database: MySQL/PostgreSQL
- Package: `maatwebsite/excel` cho tính năng import Excel
- Permission: Đảm bảo route `/warehouse/odo-manager` được bảo vệ phù hợp

### Cấu Hinh Permission

Thêm permission `odo-manager` vào hệ thống phân quyền (nếu sử dụng Laravel Permission):

```php
// Trong DatabaseSeeder hoặc PermissionSeeder
Permission::create(['name' => 'warehouse.odo-manager']);
```

### Route Truy Cập

Module có sẵn tại route:

```
GET /warehouse/odo-manager
```

Route này sử dụng Livewire component `App\Livewire\Warehouse\OdoManager` và layout `warehouse-layout`.

## Cách Sử Dụng

### 1. Nhập Thủ Công

**Bước 1:** Click button **"➕ NHẬP THỦ CÔNG"** trên giao diện.

**Bước 2:** Điền thông tin trong form:
- **Mã tài sản:** Chọn từ dropdown (chỉ hiển thị sản phẩm có type = `product_produced` hoặc `product_purchased` và status = `active`)
- **Ngày đọc:** Chọn ngày (mặc định là ngày hiện tại)
- **Số giờ:** Nhập số giờ hiện tại của tài sản (≥ 0)
- **Người vận hành:** Tên người thực hiện đọc (không bắt buộc)
- **Tình trạng:** Chọn một trong ba trạng thái:
  - `Bình thường` (normal)
  - `Cần bảo dưỡng` (maintenance_required)
  - `Đã bảo dưỡng` (maintenance_done)
- **Ghi chú:** Mô tả thêm (không bắt buộc)

**Bước 3:** Click **"Lưu"** để hoàn thành.

**Lưu ý:** Hệ thống kiểm tra trùng lặp. Nếu đã có bản ghi số giờ cho tài sản trong ngày này, hệ thống sẽ thông báo và yêu cầu chỉnh sửa thay vì thêm mới.

### 2. Import Từ Excel

**Định dạng File Excel:**

Tạo file Excel (.xlsx, .xls, hoặc .csv) với các cột sau:

| Tên cột (đầu vào) | Bắt buộc | Mô tả | Giá trị hợp lệ |
|-------------------|----------|-------|----------------|
| `ma_tai_san` / `ma` / `code` | ✅ | Mã tài sản (phải tồn tại trong bảng products) | Chuỗi, ví dụ: `AS-001` |
| `so_gio` / `gio` / `current_hours` | ✅ | Số giờ hiện tại | Số ≥ 0, ví dụ: `1250.50` |
| `ngay_doc` / `ngay` / `reading_date` | ✅ | Ngày đọc | Định dạng: `Y-m-d`, `d/m/Y`, `m/d/Y`, `d-m-Y` |
| `nguon_van_hanh` / `operator` / `van_hanh` | ❌ | Người vận hành | Chuỗi, tối đa 100 ký tự |
| `tinh_trang` / `status` | ❌ | Tình trạng | `maintenance_required`, `maintenance_done`, `normal` hoặc tiếng Việt tương đương |
| `ghi_chu` / `notes` | ❌ | Ghi chú | Văn bản tự do |

**Các trạng thái tiếng Việt được hỗ trợ:**
- `cần bảo dưỡng`, `can bao duong` → `maintenance_required`
- `đã bảo dưỡng`, `da bao duong` → `maintenance_done`
- `bình thường`, `binh thuong` → `normal`

**Bước Import:**

1. Click button **"📥 IMPORT EXCEL"**
2. Chọn file Excel (max 10MB)
3. Hệ thống tự động xử lý:
   - Tìm tài sản theo mã
   - Parse ngày tháng từ nhiều định dạng
   - Kiểm tra trùng lặp và cập nhật nếu đã tồn tại
   - Ghi log lỗi cho các dòng không hợp lệ
4. Nhận thông báo kết quả

### 3. Tìm Kiếm và Lọc

**Tìm kiếm:** Nhập mã hoặc tên tài sản vào ô tìm kiếm. Kết quả lọc theo thời gian thực.

**Lọc theo ngày:** Sử dụng các trường "Từ ngày" và "Đến ngày" để lọc theo khoảng thời gian.

**Lọc theo trạng thái:** Click một trong các nút:
- `Tất cả` - hiển thị tất cả
- `Cần bảo dưỡng` - chỉ bản ghi có status = maintenance_required
- `Đã bảo dưỡng` - chỉ bản ghi có status = maintenance_done
- `Bình thường` - chỉ bản ghi có status = normal

Các bộ lọc có thể kết hợp với nhau.

### 4. Chỉnh Sửa

Click icon **chỉnh sửa** (✏️) ở cột thao tác của bản ghi cần sửa. Form sẽ mở với dữ liệu hiện tại, cho phép thay đổi và lưu lại.

### 5. Xóa

**Xóa một bản ghi:** Click icon **xóa** (🗑️) ở cột thao tác, xác nhận khi được hỏi.

**Xóa nhiều bản ghi:**
1. Chọn các bản ghi bằng checkbox ở cột đầu tiên
2. Số lượng đã chọn sẽ hiển thị
3. Click button **"🗑️ XÓA"** để xóa tất cả các bản ghi đã chọn

## Cấu Trúc Database

**Table:** `asset_odo_readings`

```sql
CREATE TABLE asset_odo_readings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL,
    reading_date DATE NOT NULL,
    current_hours DECIMAL(10,2) NOT NULL,
    operator VARCHAR(100) NULL,
    status ENUM('maintenance_required', 'maintenance_done', 'normal') DEFAULT 'normal',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_date (product_id, reading_date),
    INDEX idx_product_status (product_id, status),
    INDEX idx_reading_date (reading_date)
);
```

**Ràng buộc:**
- Mỗi tài sản chỉ có 1 bản ghi số giờ cho mỗi ngày (`unique_product_date`)
- Xóa tài sản sẽ tự động xóa tất cả bản ghi odo liên quan (`onDelete cascade`)

## Validation Rules

**Form nhập thủ công:**
```php
[
    'selectedAssetId' => 'required|exists:products,id',
    'readingDate'     => 'required|date',
    'currentHours'    => 'required|numeric|min:0',
    'operator'        => 'nullable|string|max:100',
    'status'          => 'required|in:maintenance_required,maintenance_done,normal',
    'notes'           => 'nullable|string',
]
```

**File Excel import:**
```php
[
    '*.ma_tai_san'  => 'required|exists:products,code',
    '*.so_gio'      => 'required|numeric|min:0',
    '*.ngay_doc'    => 'required|date',
]
```

**Custom validation messages (tiếng Việt):**
- `Mã tài sản là bắt buộc`
- `Mã tài sản không tồn tại trong hệ thống`
- `Số giờ là bắt buộc`
- `Số giờ phải là số`
- `Ngày đọc là bắt buộc`
- `Ngày đọc không hợp lệ`

## Xử Lý Lỗi Import

Khi import Excel, hệ thống:
- **Bỏ qua** các dòng không tìm thấy tài sản
- **Cập nhật** bản ghi hiện có nếu đã có reading cho ngày đó
- **Tạo mới** nếu chưa có bản ghi
- **Báo lỗi** và dừng nếu file không hợp lệ hoặc có lỗi hệ thống

Để debug lỗi import, kiểm tra:
1. File Excel có đúng định dạng và kích thước (< 10MB)
2. Mã tài sản tồn tại và có type là `product_produced` hoặc `product_purchased`
3. Cột ngày có định dạng hợp lệ

## Best Practices

1. **Nhập liệu hàng ngày:** Nên cập nhật số giờ odo cho tất cả tài sản mỗi ngày để có dữ liệu chính xác
2. **Kiểm tra trùng lặp:** Hệ thống tự chống trùng lặp theo ngày, nhưng nên kiểm tra trước khi import
3. **Backup dữ liệu:** Luôn backup database trước khi import số lượng lớn
4. **Phân loại tài sản:** Chỉ sử dụng module này cho tài sản thuộc type `product_produced` hoặc `product_purchased`
5. **Ghi chú rõ ràng:** Khi tình trạng là `maintenance_required`, nên ghi chú lý do

## Troubleshooting

| Vấn đề | Nguyên nhân có thể | Giải pháp |
|--------|-------------------|-----------|
| Không thấy tài sản trong dropdown | Tài sản không thuộc type `product_produced`/`product_purchased` hoặc status != `active` | Kiểm tra bảng products, cập nhật type và status |
| Import thất bại, báo "Mã tài sản không tồn tại" | Mã trong Excel không khớp với bất kỳ product.code nào | Kiểm tra và sửa mã tài sản trong file Excel |
| Import không tạo bản ghi mới | Đã có bản ghi cho tài sản+ngày đó | Hệ thống sẽ cập nhật bản ghi hiện có; dùng tính năng chỉnh sửa để thay đổi |
| Không import được file .csv | File có encoding hoặc delimiter không đúng | Dùng file .xlsx hoặc .xls; nếu dùng .csv, đảm bảo dùng dấu phẩy và UTF-8 |
| Không thấy route /warehouse/odo-manager | Route chưa được đăng ký hoặc thiếu permission | Kiểm tra routes/warehouse.php và cấp quyền truy cập |

## Liên Hết

- **Livewire Component:** `app/Livewire/Warehouse/OdoManager.php`
- **Model:** `app/Models/AssetOdoReading.php`
- **Import Class:** `app/Imports/AssetOdoReadingsImport.php`
- **Migration:** `database/migrations/2026_05_22_000000_create_asset_odo_readings_table.php`
- **View:** `resources/views/livewire/warehouse/odo-manager.blade.php`
- **Route:** `routes/warehouse.php` (dòng 85)
