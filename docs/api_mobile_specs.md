# Tài liệu Đặc tả API cho Mobile App (Cơ bản)

Tài liệu này mô tả danh sách các API cơ bản cần thiết để kết nối hệ thống Backend (Laravel) với ứng dụng Mobile cho các chức năng: Đăng nhập, Dashboard, Quản lý Tồn kho, Nhập kho và Xuất kho.

## Thiết lập chung (General Settings)

*   **Base URL:** `https://your-domain.com/api/v1`
*   **Authentication:** Sử dụng **Bearer Token** (Laravel Sanctum). Token sẽ được cấp khi gọi API Đăng nhập và phải được đính kèm vào Header `Authorization: Bearer {token}` trong tất cả các API khác (trừ API Đăng nhập).
*   **Content-Type:** `application/json` (cho request) và `Accept: application/json` (cho response).

---

## 1. Authentication (Xác thực & Đăng nhập Admin)

### 1.1. Đăng nhập (Admin Login)
*   **Endpoint:** `POST /auth/login`
*   **Mô tả:** Xác thực quyền truy cập của người dùng (Admin/Thủ kho) và hệ thống sinh ra Token phiên làm việc.
*   **Body Request:**
    ```json
    {
      "email": "admin@example.com",
      "password": "password123"
    }
    ```
*   **Response (200 OK):**
    ```json
    {
      "status": "success",
      "data": {
        "user": {
          "id": 1,
          "name": "Admin Warehouse",
          "role": "admin"
        },
        "token": "1|abc123xyzmnoqprstuvw..."
      }
    }
    ```

### 1.2. Đăng xuất
*   **Endpoint:** `POST /auth/logout`
*   **Mô tả:** Hủy token hiện tại của thiết bị, thực hiện ngắt kết nối an toàn. (Cần gửi kèm Token trong Header).

---

## 2. Dashboard (Tổng quan)

### 2.1. Lấy dữ liệu báo cáo nhanh Dashboard
*   **Endpoint:** `GET /dashboard/summary`
*   **Mô tả:** Lấy các con số tóm tắt để hiển thị biểu đồ hoặc số liệu trên màn hình trang chủ của Mobile App.
*   **Response (200 OK):**
    ```json
    {
      "status": "success",
      "data": {
        "total_import_today": 15000000,
        "total_export_today": 12000000,
        "low_stock_items_count": 8,
        "pending_purchase_orders": 3
      }
    }
    ```

---

## 3. Tồn Kho (Inventory)

### 3.1. Xem danh sách hàng tồn kho
*   **Endpoint:** `GET /inventory`
*   **Mô tả:** Lấy danh sách hàng hoá kèm số lượng tồn kho thực tế. Có hỗ trợ phân trang (pagination) và lọc tìm kiếm (search).
*   **Query Parameters:** `?page=1&search=Banh&category_id=2`
*   **Response (200 OK):**
    ```json
    {
      "status": "success",
      "data": {
        "items": [
          {
             "item_code": "SP001",
             "name": "Nguyên liệu bột mì",
             "unit": "Kg",
             "current_stock": 1500.5
          }
        ],
        "pagination": {
          "current_page": 1,
          "last_page": 5,
          "total": 50
        }
      }
    }
    ```

### 3.2. Xem lịch sử thẻ kho (Transaction History)
*   **Endpoint:** `GET /inventory/{item_code}/history`
*   **Mô tả:** Xem chi tiết biến động (nhập, xuất) của 1 mã vật tư/sản phẩm cụ thể để truy xuất nguồn gốc.

---

## 4. Nhập Kho (Stock In)

### 4.1. Tạo phiếu nhập kho mới
*   **Endpoint:** `POST /stock-in`
*   **Mô tả:** Gửi yêu cầu nhập kho mới từ thao tác trên Mobile (ví dụ: Nhân viên đi siêu thị/nhà cung cấp vừa nhận hàng và quét mã vạch cập nhật kho).
*   **Body Request:**
    ```json
    {
      "supplier_id": 10,
      "note": "Nhập hàng đợt 1 tháng 4",
      "date": "2026-04-14",
      "items": [
        { "item_code": "SP001", "quantity": 100, "price": 50000 },
        { "item_code": "SP002", "quantity": 50, "price": 120000 }
      ]
    }
    ```
*   **Response (201 Created):**
    ```json
    {
       "status": "success",
       "message": "Tạo phiếu nhập kho thành công",
       "data": { "receipt_code": "PN-20260414-01" }
    }
    ```

### 4.2. Danh sách các phiếu nhập
*   **Endpoint:** `GET /stock-in`
*   **Mô tả:** Lấy danh sách lịch sử các phiếu chứng từ nhập kho đã thao tác.

---

## 5. Xuất Kho (Stock Out)

### 5.1. Tạo phiếu xuất kho mới
*   **Endpoint:** `POST /stock-out`
*   **Mô tả:** Tạo phiếu xuất kho. Hỗ trợ xuất theo lệnh sản xuất (BOM) hoặc xuất bán tiêu hao 일반.
*   **Body Request:**
    ```json
    {
      "production_order_code": "PO-123", 
      "purpose": "Xuất chạy dây chuyền sản xuất số 1",
      "date": "2026-04-14",
      "items": [
        { "item_code": "NVL001", "quantity": 500 },
        { "item_code": "NVL005", "quantity": 30 }
      ]
    }
    ```
*   **Response (201 Created):**
    ```json
    {
       "status": "success",
       "message": "Tạo phiếu xuất kho thành công",
       "data": { "receipt_code": "PX-20260414-05" }
    }
    ```

### 5.2. Danh sách các phiếu xuất
*   **Endpoint:** `GET /stock-out`
*   **Mô tả:** Lấy danh sách lịch sử các phiếu xuất phục vụ tra cứu số liệu.
