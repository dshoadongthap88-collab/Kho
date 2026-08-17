<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductsImport implements ToCollection
{
    private function findValue($row, $keywords)
    {
        foreach ($row as $key => $value) {
            if ($value === null || $value === '') continue;
            // Dùng Str::slug để tự động bỏ dấu tiếng Việt, viết thường, và bỏ khoảng trắng (vd: "Mã SP" -> "masp")
            $normalizedKey = Str::slug((string)$key, '');
            foreach ($keywords as $kw) {
                if (str_contains($normalizedKey, $kw)) {
                    return $value;
                }
            }
        }
        return null;
    }

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
        // 1. Tìm Mã sản phẩm (Bắt buộc)
        $productCode = $this->findValue($row, ['masp', 'masanpham', 'code', 'mahang', 'mavt', 'mavattu', 'ma', 'id']);
        if (!$productCode) return;

        $productCode = strtoupper(trim((string)$productCode));

        // 2. Tìm hoặc tạo Sản phẩm
        $product = Product::withoutGlobalScope('house')->where('code', $productCode)->first();
        
        $productName = $this->findValue($row, ['tensp', 'tensanpham', 'name', 'tenhang', 'tenvattu', 'ten']);
        $unit = $this->findValue($row, ['dvt', 'donvitinh', 'unit']);
        $brand = $this->findValue($row, ['hangsx', 'thuonghieu', 'brand']);
        $batch = $this->findValue($row, ['solo', 'batch', 'lo', 'codencc']);
        $expiry = $this->findValue($row, ['handung', 'hsd', 'expiry', 'hansudung']);
        $minStock = $this->findValue($row, ['tontoithieu', 'minstock']);
        $location = $this->findValue($row, ['vitri', 'kho', 'location']);
        $quantity = $this->findValue($row, ['soluong', 'sl', 'qty', 'quantity', 'tonkho']);
        $desc = $this->findValue($row, ['ghichu', 'mota', 'description', 'desc']);
        $boxSpec = $this->findValue($row, ['qchop', 'quycachhop']);
        $cartonSpec = $this->findValue($row, ['qcthung', 'quycachthung']);

        if (!$product) {
            // Tạo mới nếu chưa có
            $type = 'material'; // Luôn cho vào danh mục vật tư
            $product = Product::create([
                'code' => strtoupper((string)$productCode),
                'name' => $productName ?: 'Vật tư ' . $productCode,
                'unit' => $unit ?: 'Cái',
                'status' => 'active',
                'type' => $type,
            ]);
        }

        // Cập nhật thông tin sản phẩm
        $productData = [];
        $productData['type'] = 'material'; // Đảm bảo tự động cập nhật vào module Danh mục vật tư
        if ($productName) $productData['name'] = $productName;
        if ($unit) $productData['unit'] = $unit;
        if ($brand) $productData['brand'] = $brand;
        if ($batch) $productData['batch_number'] = $batch;
        if ($minStock !== null) $productData['min_stock'] = floatval($minStock);
        if ($location) $productData['location'] = $location;
        if ($desc) $productData['description'] = $desc;
        if ($boxSpec) $productData['box_spec'] = $boxSpec;
        if ($cartonSpec) $productData['carton_spec'] = $cartonSpec;

        if ($expiry) {
            if (is_numeric($expiry)) {
                try {
                    $productData['expiry_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expiry)->format('Y-m-d');
                } catch (\Exception $e) {}
            } else {
                try {
                    $productData['expiry_date'] = Carbon::parse(str_replace('/', '-', $expiry))->format('Y-m-d');
                } catch (\Exception $e) {}
            }
        }

        if (!empty($productData)) {
            $product->update($productData);
        }


    }
}
