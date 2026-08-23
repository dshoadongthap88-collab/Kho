<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use App\Traits\ExcelColumnMapper;
use App\Traits\ResolvesHouseScopedRecords;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InventoryImport implements ToCollection
{
    use ExcelColumnMapper, ResolvesHouseScopedRecords;

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
        // 1. Tìm Mã sản phẩm (Bắt buộc). Dòng thiếu mã thì bỏ qua hẳn.
        $productCode = $this->findCode($row);
        if (!$productCode) return;

        $productCode = strtoupper(trim((string)$productCode));
        if ($productCode === '') return;

        $productName = $this->findName($row);
        $unit        = $this->findUnit($row);
        $brand       = $this->findBrand($row);
        $batch       = $this->findBatch($row);
        $expiry      = $this->findExpiry($row);
        $minStock    = $this->findMinStock($row);
        $location    = $this->findLocation($row);
        $quantity    = $this->findQuantity($row);

        // 2. Tìm vật tư TRONG dự án đang đứng (không đụng dữ liệu dự án khác)
        $product = $this->findProductForCurrentHouse($productCode);

        if (!$product) {
            // Tạo mới nếu chưa có
            $product = Product::create([
                'code' => $productCode,
                'name' => $productName ?: 'Sản phẩm ' . $productCode,
                'unit' => $unit ?: 'Cái',
                'status' => 'active',
                'type' => 'material', // Luôn cho vào danh mục vật tư
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

        if ($quantity !== null || $location !== null) {
            $inventoryData = [];
            if ($quantity !== null) {
                // Chuẩn hóa số lượng
                $val = trim((string)$quantity);
                if ($val !== '' && $val !== '-') {
                    $val = preg_replace('/[^\d.,]/', '', $val);
                    if (str_contains($val, ',') && str_contains($val, '.')) {
                        $lastComma = strrpos($val, ',');
                        $lastDot = strrpos($val, '.');
                        if ($lastComma > $lastDot) {
                            $val = str_replace('.', '', $val);
                            $val = str_replace(',', '.', $val);
                        } else {
                            $val = str_replace(',', '', $val);
                        }
                    } elseif (str_contains($val, ',')) {
                        $parts = explode(',', $val);
                        if (count($parts) == 2 && (strlen($parts[1]) == 1 || strlen($parts[1]) == 2)) {
                            $val = str_replace(',', '.', $val);
                        } else {
                            $val = str_replace(',', '', $val);
                        }
                    }
                    $inventoryData['quantity'] = floatval($val);
                }
            }
            if ($location !== null) $inventoryData['warehouse_location'] = $location;

            if (!empty($inventoryData)) {
                // Tồn kho của ĐÚNG dự án đang đứng
                $inventory = $this->findInventoryForCurrentHouse($product->id);

                if ($inventory) {
                    // GHI ĐÈ tồn kho = giá trị mới (không cộng thêm vào tồn cũ)
                    $inventory->update($inventoryData);
                } else {
                    $inventoryData['product_id'] = $product->id;
                    $inventoryData['quantity'] = $inventoryData['quantity'] ?? 0;
                    Inventory::create($inventoryData);
                }
            }
        }
    }
}
