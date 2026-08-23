<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use App\Traits\ExcelColumnMapper;
use App\Traits\ResolvesHouseScopedRecords;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InventoryImport implements ToCollection
{
    use ExcelColumnMapper, ResolvesHouseScopedRecords;

    /** Thống kê để báo lại cho người dùng sau khi nhập xong */
    public int $rowsRead      = 0; // số dòng dữ liệu đọc được
    public int $skippedNoCode = 0; // dòng bị bỏ vì thiếu mã vật tư
    public int $duplicateRows = 0; // dòng có mã trùng với dòng phía trên trong cùng file
    public int $created       = 0; // vật tư tạo mới
    public int $updated       = 0; // vật tư đã có, được cập nhật

    public function collection(Collection $rows)
    {
        $headerRowIndex = $this->locateHeaderRow($rows);

        if ($headerRowIndex === -1) {
            throw new \Exception("Không tìm thấy dòng tiêu đề chứa cột Mã Sản Phẩm trong file Excel. Vui lòng đảm bảo có 1 cột tên là 'Mã SP', 'Mã hàng' hoặc 'Mã vật tư'.");
        }

        $headers = $rows[$headerRowIndex];

        // Bước 1 — đọc và chuẩn hoá toàn bộ file, chưa đụng tới CSDL.
        // Mã trùng nhau thì dòng phía dưới ghi đè dòng phía trên (tồn kho là ghi đè,
        // không cộng dồn) và được đếm lại để báo cho người dùng biết.
        $parsed = [];
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $mappedRow = [];
            foreach ($headers as $colIndex => $headerName) {
                if ($headerName) {
                    $mappedRow[(string) $headerName] = $rows[$i][$colIndex] ?? null;
                }
            }

            $data = $this->parseRow($mappedRow);
            if ($data === null) {
                continue;
            }

            $this->rowsRead++;
            if (isset($parsed[$data['code']])) {
                $this->duplicateRows++;
            }
            $parsed[$data['code']] = $data;
        }

        if (empty($parsed)) {
            return;
        }

        // Bước 2 — nạp sẵn vật tư và tồn kho của dự án hiện tại bằng vài query,
        // thay vì mỗi dòng một query như trước.
        $products = $this->preloadProductsByCode(array_keys($parsed));

        $existingIds = [];
        foreach ($products as $product) {
            $existingIds[] = $product->id;
        }
        $inventories = $this->preloadInventories($existingIds);

        // Bước 3 — ghi dữ liệu. Bọc transaction để nếu đứt giữa chừng thì không
        // còn cảnh "file 1500 dòng nhưng chỉ vào được một nửa".
        DB::transaction(function () use ($parsed, &$products, &$inventories) {
            foreach ($parsed as $code => $data) {
                $product = $products[$code] ?? null;

                if (!$product) {
                    $product = Product::create($this->productAttributes($data) + [
                        'code'   => $code,
                        'name'   => $data['name'] ?: 'Sản phẩm ' . $code,
                        'unit'   => $data['unit'] ?: 'Cái',
                        'status' => 'active',
                        'type'   => 'material', // Luôn cho vào danh mục vật tư
                    ]);

                    $products[$code] = $product;
                    $this->created++;
                } else {
                    // Đảm bảo tự động cập nhật vào module Danh mục vật tư
                    $product->fill($this->productAttributes($data) + ['type' => 'material']);
                    if ($product->isDirty()) {
                        $product->save();
                    }
                    $this->updated++;
                }

                $inventoryData = [];
                if ($data['quantity'] !== null) $inventoryData['quantity'] = $data['quantity'];
                if ($data['location'] !== null) $inventoryData['warehouse_location'] = $data['location'];

                if (empty($inventoryData)) {
                    continue;
                }

                $inventory = $inventories[$product->id] ?? null;

                if ($inventory) {
                    // GHI ĐÈ tồn kho = giá trị mới (không cộng thêm vào tồn cũ)
                    $inventory->fill($inventoryData);
                    if ($inventory->isDirty()) {
                        $inventory->save();
                    }
                } else {
                    $inventories[$product->id] = Inventory::create($inventoryData + [
                        'product_id' => $product->id,
                        'quantity'   => $inventoryData['quantity'] ?? 0,
                    ]);
                }
            }
        });
    }

    /** Tìm dòng tiêu đề (dòng có chứa cột Mã SP) */
    private function locateHeaderRow(Collection $rows): int
    {
        foreach ($rows as $index => $row) {
            foreach ($row as $cellValue) {
                if ($cellValue === null) continue;
                $valStr = Str::slug((string) $cellValue, '');
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
                    return $index;
                }
            }
        }

        return -1;
    }

    /** Đọc một dòng thành mảng đã chuẩn hoá, trả về null nếu dòng không dùng được */
    private function parseRow(array $row): ?array
    {
        // Mã vật tư là bắt buộc. Dòng thiếu mã thì bỏ hẳn, không đoán sang cột khác
        // — đó là nguyên nhân sinh ra các vật tư rác có mã là con số.
        $productCode = $this->findCode($row);
        $productCode = $productCode === null ? '' : strtoupper(trim((string) $productCode));

        if ($productCode === '') {
            $this->skippedNoCode++;
            return null;
        }

        return [
            'code'      => $productCode,
            'name'      => $this->findName($row),
            'unit'      => $this->findUnit($row),
            'brand'     => $this->findBrand($row),
            'batch'     => $this->findBatch($row),
            'expiry'    => $this->normalizeExpiry($this->findExpiry($row)),
            'min_stock' => $this->findMinStock($row),
            'location'  => $this->findLocation($row),
            'quantity'  => $this->normalizeQuantity($this->findQuantity($row)),
        ];
    }

    /** Các cột của bảng products lấy được từ một dòng Excel */
    private function productAttributes(array $data): array
    {
        $attributes = [];
        if ($data['name'])               $attributes['name']         = $data['name'];
        if ($data['unit'])               $attributes['unit']         = $data['unit'];
        if ($data['brand'])              $attributes['brand']        = $data['brand'];
        if ($data['batch'])              $attributes['batch_number'] = $data['batch'];
        if ($data['min_stock'] !== null) $attributes['min_stock']    = floatval($data['min_stock']);
        if ($data['location'])           $attributes['location']     = $data['location'];
        if ($data['expiry'])             $attributes['expiry_date']  = $data['expiry'];

        return $attributes;
    }

    private function normalizeExpiry($expiry): ?string
    {
        if (!$expiry) {
            return null;
        }

        if (is_numeric($expiry)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expiry)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse(str_replace('/', '-', $expiry))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function normalizeQuantity($quantity): ?float
    {
        if ($quantity === null) {
            return null;
        }

        $val = trim((string) $quantity);
        if ($val === '' || $val === '-') {
            return null;
        }

        $val = preg_replace('/[^\d.,]/', '', $val);

        if (str_contains($val, ',') && str_contains($val, '.')) {
            if (strrpos($val, ',') > strrpos($val, '.')) {
                $val = str_replace(',', '.', str_replace('.', '', $val));
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

        return floatval($val);
    }
}
