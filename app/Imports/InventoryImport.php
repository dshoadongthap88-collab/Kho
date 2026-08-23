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

class InventoryImport implements ToCollection, WithColumnLimit, SkipsEmptyRows
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
     * Chỉ đọc tới cột AZ. File báo cáo xuất từ phần mềm kế toán có tới hơn 1000
     * cột rỗng phía sau — đọc hết ngốn ~870MB RAM và làm chết tiến trình PHP.
     * Giới hạn 52 cột đưa mức tiêu thụ về khoảng 78MB.
     */
    public function endColumn(): string
    {
        return 'AZ';
    }

    public function collection(Collection $rows)
    {
        $header = $this->resolveHeader($rows);

        if ($header === null) {
            throw new \Exception("Không nhận ra dòng tiêu đề trong file Excel. Vui lòng đảm bảo file có một dòng tiêu đề gồm cột Mã vật tư và ít nhất một cột nữa (Tên vật tư, ĐVT, Tồn kho...).");
        }

        $columns               = $header['columns'];
        $this->detectedColumns = $this->describeColumns($header);

        // Bước 1 — đọc và chuẩn hoá toàn bộ file, chưa đụng tới CSDL.
        // Mã trùng nhau thì dòng phía dưới ghi đè dòng phía trên (tồn kho là ghi đè,
        // không cộng dồn) và được đếm lại để báo cho người dùng biết.
        $parsed = [];
        for ($i = $header['dataStartRow']; $i < count($rows); $i++) {
            $row = $rows[$i];

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

            $parsed[$code] = [
                'name'      => $this->cell($row, $columns, 'name'),
                'unit'      => $this->cell($row, $columns, 'unit'),
                'brand'     => $this->cell($row, $columns, 'brand'),
                'batch'     => $this->cell($row, $columns, 'batch'),
                'expiry'    => $this->normalizeExpiry($this->cell($row, $columns, 'expiry')),
                'min_stock' => $this->cell($row, $columns, 'min_stock'),
                'location'  => $this->cell($row, $columns, 'location'),
                'quantity'  => $this->normalizeQuantity($this->cell($row, $columns, 'quantity')),
            ];
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
            $houseId = $this->currentHouseId();
            $now     = now();

            // 3a. Vật tư đã có: chỉ chạm CSDL khi thực sự có thay đổi.
            //     Vật tư mới: gom lại để chèn hàng loạt (1500 INSERT lẻ mất ~12s,
            //     chèn theo lô 500 dòng chỉ còn vài query).
            $newProductRows = [];
            foreach ($parsed as $code => $data) {
                $product = $products[$code] ?? null;

                if (!$product) {
                    $newProductRows[$code] = $this->newProductRow($code, $data, $houseId, $now);
                    continue;
                }

                // Đảm bảo tự động cập nhật vào module Danh mục vật tư
                $product->fill($this->productAttributes($data) + ['type' => 'material']);
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

            // 3b. Tồn kho: cập nhật cái đã có, chèn hàng loạt cái mới
            $newInventoryRows = [];
            foreach ($parsed as $code => $data) {
                $product = $products[$code] ?? null;
                if (!$product) {
                    continue;
                }

                $quantity = $data['quantity'];
                $location = $data['location'];
                if ($quantity === null && $location === null) {
                    continue;
                }

                $inventory = $inventories[$product->id] ?? null;

                if ($inventory) {
                    // GHI ĐÈ tồn kho = giá trị mới (không cộng thêm vào tồn cũ)
                    if ($quantity !== null) $inventory->quantity = $quantity;
                    if ($location !== null) $inventory->warehouse_location = $location;
                    if ($inventory->isDirty()) {
                        $inventory->save();
                    }
                    continue;
                }

                $newInventoryRows[] = [
                    'product_id'         => $product->id,
                    'quantity'           => $quantity ?? 0,
                    'warehouse_location' => $location,
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

    /**
     * Một dòng products đầy đủ cột để chèn hàng loạt.
     * Mọi dòng phải có CÙNG bộ khoá thì MySQL mới nhận insert nhiều dòng một lần.
     */
    private function newProductRow(string $code, array $data, $houseId, $now): array
    {
        $attributes = $this->productAttributes($data);

        return [
            'code'         => $code,
            'name'         => $data['name'] ?: 'Sản phẩm ' . $code,
            'unit'         => $data['unit'] ?: 'Cái',
            'brand'        => $attributes['brand']        ?? null,
            'batch_number' => $attributes['batch_number'] ?? null,
            'expiry_date'  => $attributes['expiry_date']  ?? null,
            'location'     => $attributes['location']     ?? null,
            'min_stock'    => (int) ($attributes['min_stock'] ?? 0), // cột int NOT NULL
            'status'       => 'active',
            'type'         => 'material', // Luôn cho vào danh mục vật tư
            'house_id'     => $houseId,
            'created_at'   => $now,
            'updated_at'   => $now,
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

    /** Ghép lại "trường => tên cột trong file" để hiện cho người dùng đối chiếu */
    private function describeColumns(array $header): array
    {
        $labels = [
            'code' => 'Mã vật tư', 'name' => 'Tên vật tư', 'unit' => 'ĐVT',
            'quantity' => 'Tồn kho', 'location' => 'Vị trí', 'brand' => 'Hãng SX',
            'batch' => 'Số lô', 'expiry' => 'Hạn dùng', 'min_stock' => 'Tồn tối thiểu',
        ];

        $described = [];
        foreach ($header['columns'] as $field => $colIndex) {
            if (!isset($labels[$field])) continue;
            $described[$labels[$field]] = $header['names'][$colIndex] ?? ('cột ' . $colIndex);
        }

        return $described;
    }
}
