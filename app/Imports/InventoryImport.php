<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InventoryImport implements ToCollection
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

        // 1. Tìm dòng tiêu đề (Dòng có chứa cột Mã SP)
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
            throw new \Exception("Không tìm thấy dòng tiêu đề chứa cột Mã Sản Phẩm trong file Excel. Vui lòng đảm bảo có 1 cột tên là 'Mã SP', 'Mã hàng' hoặc 'Mã vật tư'.");
        }

        $headers = $rows[$headerRowIndex];
        
        // 2. Xử lý từng dòng dữ liệu phía dưới dòng tiêu đề
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            
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
        $productCode = $this->findValue($row, ['masp', 'masanpham', 'code', 'mahang']);
        if (!$productCode) return;

        // 2. Tìm hoặc tạo Sản phẩm
        $product = Product::where('code', $productCode)->first();
        
        $productName = $this->findValue($row, ['tensp', 'tensanpham', 'name', 'tenhang']);
        $unit = $this->findValue($row, ['dvt', 'donvitinh', 'unit']);
        $brand = $this->findValue($row, ['hangsx', 'thuonghieu', 'brand']);
        $batch = $this->findValue($row, ['solo', 'batch', 'lo']);
        $expiry = $this->findValue($row, ['handung', 'hsd', 'expiry', 'hansudung']);
        $minStock = $this->findValue($row, ['tontoithieu', 'minstock']);
        $location = $this->findValue($row, ['vitri', 'kho', 'location']);
        $quantity = $this->findValue($row, ['soluong', 'sl', 'qty', 'quantity', 'tonkho']);

        if (!$product) {
            // Tạo mới nếu chưa có
            $type = str_starts_with(strtoupper((string)$productCode), 'NVL') ? 'material' : 'product_produced';
            $product = Product::create([
                'code' => strtoupper((string)$productCode),
                'name' => $productName ?: 'Sản phẩm ' . $productCode,
                'unit' => $unit ?: 'Cái',
                'status' => 'active',
                'type' => $type,
            ]);
        }

        // Cập nhật thông tin sản phẩm
        $productData = [];
        if ($productName) $productData['name'] = $productName;
        if ($unit) $productData['unit'] = $unit;
        if ($brand) $productData['brand'] = $brand;
        if ($batch) $productData['batch_number'] = $batch;
        if ($minStock !== null) $productData['min_stock'] = floatval($minStock);
        if ($location) $productData['location'] = $location;

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

        // 3. Cập nhật Tồn kho
        if ($quantity !== null || $location !== null) {
            $inventoryData = [];
            if ($quantity !== null) $inventoryData['quantity'] = floatval($quantity);
            if ($location !== null) $inventoryData['warehouse_location'] = $location;

            $inventory = Inventory::where('product_id', $product->id)->first();
            if ($inventory) {
                $inventory->update($inventoryData);
            } else {
                $inventoryData['product_id'] = $product->id;
                $inventoryData['quantity'] = $inventoryData['quantity'] ?? 0;
                Inventory::create($inventoryData);
            }
        }
    }
}
