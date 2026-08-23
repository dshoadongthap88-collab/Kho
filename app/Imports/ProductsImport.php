<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use App\Traits\ExcelColumnMapper;
use App\Traits\ResolvesHouseScopedRecords;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithColumnLimit;

class ProductsImport implements ToCollection, WithColumnLimit, SkipsEmptyRows
{
    use ExcelColumnMapper, ResolvesHouseScopedRecords;

    /** Thống kê để báo lại cho người dùng sau khi nhập xong */
    public int $rowsRead      = 0; // số dòng dữ liệu đọc được
    public int $skippedNoCode = 0; // dòng bị bỏ vì thiếu mã vật tư
    public int $duplicateRows = 0; // dòng có mã trùng với dòng phía trên trong cùng file
    public int $created       = 0; // vật tư tạo mới
    public int $updated       = 0; // vật tư đã có, được cập nhật

    /** Tên các cột đã nhận diện được, để hiển thị lại cho người dùng đối chiếu */
    public array $detectedColumns = [];

    /**
     * Chỉ đọc tới cột AZ — file báo cáo xuất từ phần mềm kế toán có hơn 1000 cột
     * rỗng phía sau, đọc hết sẽ ngốn hàng trăm MB RAM và làm chết tiến trình PHP.
     */
    public function endColumn(): string
    {
        return 'AZ';
    }

    public function collection(Collection $rows)
    {
        $header = $this->resolveHeader($rows);

        if ($header === null) {
            throw new \Exception("Không nhận ra dòng tiêu đề trong file Excel. Vui lòng đảm bảo file có một dòng tiêu đề gồm cột Mã vật tư và ít nhất một cột nữa (Tên vật tư, ĐVT, Vị trí...).");
        }

        $columns               = $header['columns'];
        $this->detectedColumns = $this->describeColumns($header);

        // Bước 1 — đọc toàn bộ file, chưa đụng tới CSDL. Mã trùng nhau thì dòng
        // phía dưới ghi đè dòng phía trên và được đếm lại để báo cho người dùng.
        $parsed = [];
        for ($i = $header['dataStartRow']; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Mã vật tư là bắt buộc. Dòng thiếu mã thì bỏ hẳn, không đoán sang cột
            // khác — đó là nguyên nhân sinh ra các vật tư rác có mã là con số.
            $code = $this->cell($row, $columns, 'code');
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
                'name'     => $this->cell($row, $columns, 'name'),
                'unit'     => $this->cell($row, $columns, 'unit'),
                'location' => $this->cell($row, $columns, 'location'),
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
            $houseId = $this->currentHouseId();
            $now     = now();

            // 3a. Vật tư mới gom lại chèn hàng loạt; vật tư cũ chỉ lưu khi có đổi
            $newProductRows = [];
            foreach ($parsed as $code => $data) {
                $product = $products[$code] ?? null;

                if (!$product) {
                    // Tồn kho để 0 — sẽ cập nhật qua phiếu nhập
                    $newProductRows[$code] = [
                        'code'       => $code,
                        'name'       => $data['name'] ?: 'Vật tư ' . $code,
                        'unit'       => $data['unit'] ?: 'Cái',
                        'location'   => $data['location'] ?: null,
                        'status'     => 'active',
                        'type'       => 'material',
                        'house_id'   => $houseId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    continue;
                }

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

            if ($newProductRows) {
                foreach (array_chunk($newProductRows, 500) as $chunk) {
                    Product::insert($chunk);
                }
                $this->created += count($newProductRows);

                // Lấy lại id của các vật tư vừa chèn
                $products += $this->preloadProductsByCode(array_keys($newProductRows));
            }

            // 3b. Vị trí trong tồn kho — chỉ của DỰ ÁN HIỆN TẠI, không đụng số lượng
            $newInventoryRows = [];
            foreach ($parsed as $code => $data) {
                if (!$data['location']) {
                    continue;
                }

                $product = $products[$code] ?? null;
                if (!$product) {
                    continue;
                }

                $inventory = $inventories[$product->id] ?? null;

                if ($inventory) {
                    $inventory->warehouse_location = $data['location'];
                    if ($inventory->isDirty()) {
                        $inventory->save();
                    }
                    continue;
                }

                // Tạo record Inventory trống để vật tư hiển thị trong màn hình Tồn Kho
                $newInventoryRows[] = [
                    'product_id'         => $product->id,
                    'quantity'           => 0,
                    'warehouse_location' => $data['location'],
                    'house_id'           => $houseId,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }

            foreach (array_chunk($newInventoryRows, 500) as $chunk) {
                Inventory::insert($chunk);
            }
        });
    }

    /** Ghép lại "trường => tên cột trong file" để hiện cho người dùng đối chiếu */
    private function describeColumns(array $header): array
    {
        $labels = [
            'code' => 'Mã vật tư', 'name' => 'Tên vật tư',
            'unit' => 'ĐVT', 'location' => 'Vị trí',
        ];

        $described = [];
        foreach ($header['columns'] as $field => $colIndex) {
            if (!isset($labels[$field])) continue;
            $described[$labels[$field]] = $header['names'][$colIndex] ?? ('cột ' . $colIndex);
        }

        return $described;
    }
}
