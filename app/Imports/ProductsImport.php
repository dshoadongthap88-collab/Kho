<?php

namespace App\Imports;

use App\Models\Product;
use App\Traits\ExcelColumnMapper;
use App\Traits\ResolvesHouseScopedRecords;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;

class ProductsImport implements ToCollection
{
    use ExcelColumnMapper, ResolvesHouseScopedRecords;

    public function collection(Collection $rows)
    {
        $headerRowIndex = -1;

        // 1. Tìm dòng tiêu đề (Dòng có chứa cột Mã vật tư)
        foreach ($rows as $index => $row) {
            foreach ($row as $cellValue) {
                if ($cellValue === null) continue;
                $valStr = Str::slug((string)$cellValue, '');
                if (
                    str_contains($valStr, 'masp') || 
                    str_contains($valStr, 'masanpham') || 
                    str_contains($valStr, 'mahang') || 
                    str_contains($valStr, 'mavt') || 
                    str_contains($valStr, 'mavattu') || 
                    $valStr === 'ma' || 
                    $valStr === 'code' || 
                    $valStr === 'id'
                ) {
                    $headerRowIndex = $index;
                    break 2;
                }
            }
        }

        if ($headerRowIndex === -1) {
            throw new \Exception("Không tìm thấy dòng tiêu đề chứa cột Mã vật tư trong file Excel. Vui lòng đảm bảo có 1 cột mang ý nghĩa là Mã vật tư (ví dụ: 'Mã SP', 'Mã hàng', 'Mã VT').");
        }

        $headers = $rows[$headerRowIndex];
        
        // 2. Xử lý từng dòng dữ liệu phía dưới dòng tiêu đề
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
            // Lọc bỏ dòng hoàn toàn trống
            $isEmpty = true;
            foreach ($row as $cell) {
                if ($cell !== null && $cell !== '') {
                    $isEmpty = false;
                    break;
                }
            }
            if ($isEmpty) continue;

            // Map dữ liệu cột theo header
            $mappedRow = [];
            foreach ($headers as $colIndex => $headerName) {
                if ($headerName) {
                    $mappedRow[(string)$headerName] = $row[$colIndex] ?? null;
                }
            }

            $this->processRow($mappedRow);
        }
    }

    private function processRow(array $row)
    {
        // 1. Tìm Mã vật tư (Bắt buộc). Dòng thiếu mã thì bỏ qua, không đoán sang
        // cột khác — đó là nguyên nhân sinh ra các vật tư rác có mã là con số.
        $productCode = $this->findCode($row);
        if (!$productCode) return;

        $productCode = strtoupper(trim((string)$productCode));
        if ($productCode === '') return;

        // Chỉ đọc: mã, tên, ĐVT, vị trí (không đụng tồn kho)
        $productName = $this->findName($row);
        $unit        = $this->findUnit($row);
        $location    = $this->findLocation($row);

        // 2. Tìm vật tư TRONG dự án đang đứng (không đụng dữ liệu dự án khác)
        $product = $this->findProductForCurrentHouse($productCode);

        if (!$product) {
            // Tạo mới nếu chưa có — tồn kho = 0 (sẽ cập nhật qua phiếu nhập)
            $product = Product::create([
                'code'     => $productCode,
                'name'     => $productName ?: 'Vật tư ' . $productCode,
                'unit'     => $unit ?: 'Cái',
                'status'   => 'active',
                'type'     => 'material',
                'location' => $location ?: null,
            ]);

            // Tạo record Inventory trống để vật tư hiển thị trong màn hình Tồn Kho
            \App\Models\Inventory::firstOrCreate(
                ['product_id' => $product->id],
                ['quantity' => 0, 'warehouse_location' => $location]
            );

            return;
        }

        // Cập nhật thông tin danh mục — KHÔNG thay đổi số lượng tồn kho
        $productData = ['type' => 'material'];
        if ($productName) $productData['name']     = $productName;
        if ($unit)        $productData['unit']     = $unit;
        if ($location)    $productData['location'] = $location;

        $product->update($productData);

        // Chỉ cập nhật warehouse_location của tồn kho THUỘC DỰ ÁN HIỆN TẠI
        if ($location) {
            $inventory = $this->findInventoryForCurrentHouse($product->id);
            if ($inventory) {
                $inventory->update(['warehouse_location' => $location]);
            } else {
                \App\Models\Inventory::create([
                    'product_id'         => $product->id,
                    'quantity'           => 0,
                    'warehouse_location' => $location,
                ]);
            }
        }
    }
}
