<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use App\Traits\ExcelColumnMapper;
use App\Traits\ResolvesHouseScopedRecords;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;

class ProductsImport implements ToCollection
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
            throw new \Exception("Không tìm thấy dòng tiêu đề chứa cột Mã vật tư trong file Excel. Vui lòng đảm bảo có 1 cột mang ý nghĩa là Mã vật tư (ví dụ: 'Mã SP', 'Mã hàng', 'Mã VT').");
        }

        $headers = $rows[$headerRowIndex];

        // Bước 1 — đọc toàn bộ file, chưa đụng tới CSDL. Mã trùng nhau thì dòng
        // phía dưới ghi đè dòng phía trên và được đếm lại để báo cho người dùng.
        $parsed = [];
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

            $mappedRow = [];
            foreach ($headers as $colIndex => $headerName) {
                if ($headerName) {
                    $mappedRow[(string) $headerName] = $row[$colIndex] ?? null;
                }
            }

            // Mã vật tư là bắt buộc. Dòng thiếu mã thì bỏ hẳn, không đoán sang cột
            // khác — đó là nguyên nhân sinh ra các vật tư rác có mã là con số.
            $code = $this->findCode($mappedRow);
            $code = $code === null ? '' : strtoupper(trim((string) $code));

            if ($code === '') {
                $this->skippedNoCode++;
                continue;
            }

            $this->rowsRead++;
            if (isset($parsed[$code])) {
                $this->duplicateRows++;
            }

            // Chỉ đọc: mã, tên, ĐVT, vị trí (không đụng tồn kho)
            $parsed[$code] = [
                'name'     => $this->findName($mappedRow),
                'unit'     => $this->findUnit($mappedRow),
                'location' => $this->findLocation($mappedRow),
            ];
        }

        if (empty($parsed)) {
            return;
        }

        // Bước 2 — nạp sẵn vật tư của dự án hiện tại bằng vài query
        $products = $this->preloadProductsByCode(array_keys($parsed));

        $existingIds = [];
        foreach ($products as $product) {
            $existingIds[] = $product->id;
        }
        $inventories = $this->preloadInventories($existingIds);

        // Bước 3 — ghi dữ liệu trong một transaction để không nhập được nửa chừng
        DB::transaction(function () use ($parsed, &$products, &$inventories) {
            foreach ($parsed as $code => $data) {
                $product = $products[$code] ?? null;

                if (!$product) {
                    // Tạo mới nếu chưa có — tồn kho = 0 (sẽ cập nhật qua phiếu nhập)
                    $product = Product::create([
                        'code'     => $code,
                        'name'     => $data['name'] ?: 'Vật tư ' . $code,
                        'unit'     => $data['unit'] ?: 'Cái',
                        'status'   => 'active',
                        'type'     => 'material',
                        'location' => $data['location'] ?: null,
                    ]);

                    $products[$code] = $product;
                    $this->created++;
                } else {
                    // Cập nhật thông tin danh mục — KHÔNG thay đổi số lượng tồn kho
                    $attributes = ['type' => 'material'];
                    if ($data['name'])     $attributes['name']     = $data['name'];
                    if ($data['unit'])     $attributes['unit']     = $data['unit'];
                    if ($data['location']) $attributes['location'] = $data['location'];

                    $product->fill($attributes);
                    if ($product->isDirty()) {
                        $product->save();
                    }
                    $this->updated++;
                }

                if (!$data['location']) {
                    continue;
                }

                // Chỉ cập nhật vị trí của tồn kho THUỘC DỰ ÁN HIỆN TẠI
                $inventory = $inventories[$product->id] ?? null;

                if ($inventory) {
                    $inventory->warehouse_location = $data['location'];
                    if ($inventory->isDirty()) {
                        $inventory->save();
                    }
                } else {
                    // Tạo record Inventory trống để vật tư hiển thị trong màn hình Tồn Kho
                    $inventories[$product->id] = Inventory::create([
                        'product_id'         => $product->id,
                        'quantity'           => 0,
                        'warehouse_location' => $data['location'],
                    ]);
                }
            }
        });
    }

    /** Tìm dòng tiêu đề (dòng có chứa cột Mã vật tư) */
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
}
