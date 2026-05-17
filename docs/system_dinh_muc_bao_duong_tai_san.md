# HỆ THỐNG ĐỊNH MỨC BẢO DƯỠNG & BẢNG BOM CHO MÃ TÀI SẢN

Tài liệu này thiết lập mô hình tính toán, quy tắc chuyên môn và bảng định mức nguyên vật liệu (BOM) bảo dưỡng tự động áp dụng cho các thiết bị, máy móc công trình và xe máy thi công trong hệ thống quản lý tài sản của doanh nghiệp.

---

## 1. MÔ HÌNH TOÁN HỌC & QUY TẮC TÍNH TOÁN TỰ ĐỘNG

Hệ thống tính toán định mức và chu kỳ bảo dưỡng tự động dựa trên các tham số đầu vào và quy tắc hiệu chỉnh đặc thù:

### A. Công thức hiệu chỉnh chu kỳ bảo dưỡng do Điều kiện vận hành ($F_{cond}$)
* **Nhẹ (Light):** $F_{cond} = 1.0$ (Giữ nguyên chu kỳ tiêu chuẩn)
* **Trung bình (Medium):** $F_{cond} = 1.0$ (Giữ nguyên chu kỳ tiêu chuẩn)
* **Nặng (Heavy):** $F_{cond} = 0.8$ (Giảm 20% chu kỳ, yêu cầu bảo dưỡng sớm hơn)

$$\text{Chu kỳ hiệu chỉnh } (C_{adj}) = C_{std} \times F_{cond}$$

### B. Công thức tính mốc bảo dưỡng tiếp theo ($T_{next}$)
* Dựa trên số giờ/số km hoạt động hiện tại ($T_{curr}$):
$$T_{next} = \left( \lfloor \frac{T_{curr}}{C_{adj}} \rfloor + 1 \right) \times C_{adj}$$
* Khoảng cách còn lại đến kỳ bảo dưỡng ($T_{rem}$):
$$T_{rem} = T_{next} - T_{curr}$$

### C. Quy tắc kiểm tra tăng cường đối với thiết bị cũ (> 7 năm)
* **Năm hiện tại:** 2026.
* **Độ tuổi thiết bị ($Age$):** $Age = 2026 - \text{Năm sản xuất}$.
* **Quy tắc:** Nếu $Age > 7$, hệ thống tự động chèn cảnh báo kiểm tra tăng cường: đo khe hở piston, kiểm tra độ mài mòn bơm thủy lực, nội soi buồng đốt và đo áp suất nén nắp quy-lát.

### D. Quy tắc áp dụng tiêu chuẩn ngành khi khuyết thiếu dữ liệu hãng
* **Chu kỳ dầu động cơ tiêu chuẩn ngành:** 250 giờ hoạt động hoặc 5.000 km.
* **Chu kỳ dầu thủy lực tiêu chuẩn ngành:** 2.000 giờ hoạt động.

---

## 2. DỮ LIỆU ĐẦU VÀO MẪU (3 THIẾT BỊ ĐẠI DIỆN)

| Tham số đầu vào | Thiết bị 1 (TS-EXC-001) | Thiết bị 2 (TS-TRK-002) | Thiết bị 3 (TS-GEN-003) |
| :--- | :--- | :--- | :--- |
| **Mã tài sản** | `TS-EXC-001` | `TS-TRK-002` | `TS-GEN-003` |
| **Loại thiết bị** | Máy xúc xích Komatsu | Xe ben tải nặng Howo | Máy phát điện Cummins |
| **Hãng sản xuất** | Komatsu | Sinotruk | Cummins |
| **Model** | PC200-8 | Howo 371 | C150D5 |
| **Năm sản xuất** | 2017 (Tuổi: 9 năm) | 2020 (Tuổi: 6 năm) | 2014 (Tuổi: 12 năm) |
| **Đơn vị đo lường** | Giờ (Hours) | Kilomet (Km) | Giờ (Hours) |
| **Số giờ/km hiện tại** | 12,450 giờ | 185,000 km | 3,200 giờ |
| **Chu kỳ tiêu chuẩn hãng**| Dầu máy: 250h / Dầu thủy lực: 1000h | Dầu máy: 10,000km / Dầu thủy lực: 40,000km | *Bị mất dữ liệu hãng* |
| **Công suất động cơ** | 110 kW | 273 kW (371 HP) | 120 kW |
| **Dung tích dầu động cơ**| 28 Lít | 32 Lít | 20 Lít |
| **Dung tích dầu thủy lực**| 250 Lít | 40 Lít | 0 Lít (Không sử dụng) |
| **Điều kiện vận hành** | **Nặng (Heavy)** | Trung bình (Medium) | Nhẹ (Light) |

---

## 3. BẢNG BOM VÀ ĐỊNH MỨC BẢO DƯỠNG CHI TIẾT

Dưới đây là bảng phân rã BOM vật tư kỹ thuật và định mức tiêu hao dầu nhờn cho từng chu kỳ bảo dưỡng của các mã tài sản:

| Mã tài sản | Loại bảo dưỡng | Chu kỳ hiệu chỉnh | Vật tư sử dụng | Mã vật tư | Định mức sử dụng | Đơn vị | Ghi chú / Cảnh báo vận hành |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **TS-EXC-001** | Thay dầu động cơ | 200 giờ *(Giảm 20% do ĐK Nặng)* | Dầu động cơ 15W40 | `OIL-ENG-15W40` | 28.0 | Lít | Thay định kỳ mỗi 200 giờ. |
| **TS-EXC-001** | Thay dầu động cơ | 200 giờ *(Giảm 20% do ĐK Nặng)* | Lọc dầu động cơ | `FLT-ENG-PC200` | 1.0 | Cái | Thay kèm mỗi lần thay dầu. |
| **TS-EXC-001** | Thay dầu động cơ | 200 giờ *(Giảm 20% do ĐK Nặng)* | Lọc nhiên liệu tinh | `FLT-FUEL-PC200`| 1.0 | Cái | Thay kèm mỗi lần thay dầu. |
| **TS-EXC-001** | Thay dầu động cơ | 400 giờ *(2 kỳ dầu động cơ)* | Lọc gió động cơ | `FLT-AIR-PC200` | 1.0 | Bộ | Thay sau mỗi 400 giờ hoạt động. |
| **TS-EXC-001** | Thay dầu thủy lực | 800 giờ *(Giảm 20% do ĐK Nặng)* | Dầu thủy lực AW68 | `OIL-HYD-AW68` | 250.0 | Lít | Chu kỳ hiệu chỉnh từ 1.000h xuống 800h. |
| **TS-EXC-001** | Thay dầu thủy lực | 800 giờ *(Giảm 20% do ĐK Nặng)* | Lọc thủy lực hồi | `FLT-HYD-RET` | 1.0 | Cái | Thay thế cùng dầu thủy lực. |
| **TS-EXC-001** | Thay dầu thủy lực | 800 giờ *(Giảm 20% do ĐK Nặng)* | Lọc thủy lực hút | `FLT-HYD-SUC` | 1.0 | Cái | Thay thế cùng dầu thủy lực. |
| **TS-TRK-002** | Thay dầu động cơ | 10,000 km | Dầu động cơ 20W50 | `OIL-ENG-20W50` | 32.0 | Lít | Thay định kỳ mỗi 10,000 km. |
| **TS-TRK-002** | Thay dầu động cơ | 10,000 km | Lọc dầu động cơ HOWO| `FLT-ENG-HOWO`  | 1.0 | Cái | Thay kèm mỗi lần thay dầu động cơ. |
| **TS-TRK-002** | Thay dầu động cơ | 10,000 km | Lọc nhiên liệu thô/tinh| `FLT-FUEL-HOWO`  | 2.0 | Cái | Bộ 2 lọc (Lọc tách nước + Lọc tinh). |
| **TS-TRK-002** | Thay dầu động cơ | 20,000 km | Lọc gió động cơ HOWO | `FLT-AIR-HOWO`  | 1.0 | Bộ | Thay sau mỗi 2 kỳ bảo dưỡng dầu máy. |
| **TS-TRK-002** | Thay dầu thủy lực | 40,000 km | Dầu trợ lực & ben AW46| `OIL-HYD-AW46` | 40.0 | Lít | Hệ thống dầu trợ lực lái và ben tự đổ. |
| **TS-TRK-002** | Thay dầu thủy lực | 40,000 km | Lọc đường dầu ben | `FLT-BEN-HOWO`  | 1.0 | Cái | Thay kèm dầu thủy lực ben xe. |
| **TS-GEN-003** | Thay dầu động cơ | 250 giờ *(Áp dụng chuẩn ngành)*| Dầu động cơ 15W40 | `OIL-ENG-15W40` | 20.0 | Lít | *Không có data hãng -> dùng chuẩn ngành.* |
| **TS-GEN-003** | Thay dầu động cơ | 250 giờ *(Áp dụng chuẩn ngành)*| Lọc dầu động cơ Cummins| `FLT-ENG-CUMMIN` | 1.0 | Cái | Thay kèm mỗi lần bảo dưỡng. |
| **TS-GEN-003** | Thay dầu động cơ | 250 giờ *(Áp dụng chuẩn ngành)*| Lọc nhiên liệu Cummins | `FLT-FUEL-CUMMIN`| 1.0 | Cái | Thay kèm mỗi lần bảo dưỡng. |
| **TS-GEN-003** | Thay dầu động cơ | 500 giờ *(2 kỳ dầu động cơ)* | Lọc gió máy phát | `FLT-AIR-CUMMIN` | 1.0 | Cái | Thay sau mỗi 500 giờ hoạt động. |

> [!WARNING]
> **ĐỀ XUẤT KIỂM TRA TĂNG CƯỜNG (THIẾT BỊ > 7 NĂM):**
> * **TS-EXC-001 (9 năm sử dụng):** Đề xuất thực hiện đo áp suất nén buồng đốt động cơ Komatsu, kiểm tra độ rơ của bạc/ắc gầu xúc và kiểm tra rò rỉ xi lanh lực cánh tay xúc.
> * **TS-GEN-003 (12 năm sử dụng):** Đề xuất kiểm tra cách điện cuộn dây stator/rotor, đo điện trở nối đất vỏ máy phát điện, kiểm tra độ rơ của bạc đạn trục quay động cơ Cummins.

---

## 4. TÓM TẮT ĐỊNH MỨC TIÊU HAO THEO TỪNG MÃ TÀI SẢN

### 1. Mã tài sản: `TS-EXC-001` (Komatsu PC200-8)
* **Định mức Dầu động cơ tiêu hao:** 28 Lít dầu `15W40` cho mỗi chu kỳ 200 giờ hoạt động.
* **Định mức Dầu thủy lực tiêu hao:** 250 Lít dầu `AW68` cho mỗi chu kỳ 800 giờ hoạt động.
* **Lọc động cơ tiêu hao:** 1 Lọc dầu (`FLT-ENG-PC200`), 1 Lọc nhiên liệu (`FLT-FUEL-PC200`) mỗi 200 giờ; 1 Lọc gió (`FLT-AIR-PC200`) mỗi 400 giờ.
* **Lọc thủy lực tiêu hao:** 1 Lọc hồi (`FLT-HYD-RET`), 1 Lọc hút (`FLT-HYD-SUC`) mỗi 800 giờ.

### 2. Mã tài sản: `TS-TRK-002` (Howo 371)
* **Định mức Dầu động cơ tiêu hao:** 32 Lít dầu `20W50` cho mỗi chu kỳ 10,000 km.
* **Định mức Dầu thủy lực/ben tiêu hao:** 40 Lít dầu `AW46` cho mỗi chu kỳ 40,000 km.
* **Lọc động cơ tiêu hao:** 1 Lọc dầu (`FLT-ENG-HOWO`), 2 Lọc nhiên liệu (`FLT-FUEL-HOWO`) mỗi 10,000 km; 1 Lọc gió (`FLT-AIR-HOWO`) mỗi 20,000 km.
* **Lọc thủy lực tiêu hao:** 1 Lọc dầu ben (`FLT-BEN-HOWO`) mỗi 40,000 km.

### 3. Mã tài sản: `TS-GEN-003` (Cummins 150kVA)
* **Định mức Dầu động cơ tiêu hao:** 20 Lít dầu `15W40` cho mỗi chu kỳ 250 giờ hoạt động.
* **Lọc động cơ tiêu hao:** 1 Lọc dầu (`FLT-ENG-CUMMIN`), 1 Lọc nhiên liệu (`FLT-FUEL-CUMMIN`) mỗi 250 giờ; 1 Lọc gió (`FLT-AIR-CUMMIN`) mỗi 500 giờ.
* **Đầu thủy lực tiêu hao:** 0 Lít (Không áp dụng).

---

## 5. ĐỀ XUẤT KẾ HOẠCH BẢO DƯỠNG CHI TIẾT (ĐẾN MỐC TIẾP THEO)

Dựa trên kết quả tính toán tự động từ hệ thống, dưới đây là kế hoạch bảo dưỡng chủ động cho các thiết bị nhằm ngăn ngừa sự cố đột xuất:

### 📋 Kế hoạch hành động cụ thể cho từng thiết bị:

#### 1. Máy xúc `TS-EXC-001` (Komatsu PC200-8)
* **Số giờ hiện tại:** 12,450 giờ.
* **Kế hoạch bảo dưỡng Động cơ:** 
  * Mốc bảo dưỡng tiếp theo: **12,600 giờ**.
  * Số giờ còn lại: **150 giờ** hoạt động (Dự kiến trong vòng 15 - 20 ngày tới tùy tần suất làm việc).
  * Công việc: Thay 28 Lít dầu `OIL-ENG-15W40`, thay Lọc dầu `FLT-ENG-PC200` và Lọc nhiên liệu `FLT-FUEL-PC200`.
* **Kế hoạch bảo dưỡng Hệ thống Thủy lực:**
  * Mốc bảo dưỡng tiếp theo: **12,800 giờ**.
  * Số giờ còn lại: **350 giờ** hoạt động.
  * Công việc: Thay 250 Lít dầu thủy lực `OIL-HYD-AW68`, thay Lọc hồi `FLT-HYD-RET` và Lọc hút `FLT-HYD-SUC`.
* **Cảnh báo khẩn cấp:** Thiết bị đã sử dụng **9 năm** và làm việc trong **điều kiện nặng**, cần chuẩn bị vật tư bảo dưỡng thủy lực sớm tại kho để chủ động thay thế ngay khi máy đạt mốc 12,600 giờ (thực hiện gộp bảo dưỡng động cơ và thủy lực sớm để giảm thời gian dừng máy của công trình).

#### 2. Xe ben `TS-TRK-002` (Howo 371)
* **Số km hiện tại:** 185,000 km.
* **Kế hoạch bảo dưỡng Động cơ:**
  * Mốc bảo dưỡng tiếp theo: **190,000 km**.
  * Số km còn lại: **5,000 km** (Dự kiến thực hiện trong vòng 20 - 25 ngày tới).
  * Công việc: Thay 32 Lít dầu động cơ `OIL-ENG-20W50`, thay Lọc dầu máy và bộ 2 Lọc nhiên liệu.
* **Kế hoạch bảo dưỡng Thủy lực & Ben:**
  * Mốc bảo dưỡng tiếp theo: **200,000 km**.
  * Số km còn lại: **15,000 km**.
  * Công việc: Thay 40 Lít dầu thủy lực ben `OIL-HYD-AW46`, thay thế Lọc đường dầu ben xe.

#### 3. Máy phát điện dự phòng `TS-GEN-003` (Cummins 150kVA)
* **Số giờ hiện tại:** 3,200 giờ.
* **Kế hoạch bảo dưỡng Động cơ:**
  * Mốc bảo dưỡng tiếp theo: **3,250 giờ**.
  * Số giờ còn lại: **Cực kỳ khẩn cấp - chỉ còn 50 giờ hoạt động!** (Đề xuất phát phiếu yêu cầu bảo dưỡng ngay lập tức).
  * Công việc: Thay thế 20 Lít dầu `15W40`, thay Lọc dầu máy phát và Lọc nhiên liệu Cummins.
  * **Cảnh báo tuổi thọ:** Máy phát điện đã sử dụng **12 năm**, yêu cầu kỹ thuật viên đo đạc mức độ cách điện của cuộn dây phát trước khi chạy thử tải sau bảo dưỡng.
