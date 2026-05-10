<?php

namespace App\Imports;

use App\Models\StockCountItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StockCountImport implements ToCollection, WithHeadingRow
{
    protected $stockCountId;

    public function __construct($stockCountId)
    {
        $this->stockCountId = $stockCountId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $productCode = $row['ma_san_pham'] ?? null;
            $actualQty = $row['so_luong_thuc_te_vui_long_dien_vao_day'] ?? null;

            if ($productCode === null || $actualQty === null) {
                continue;
            }

            $product = Product::where('code', $productCode)->first();
            if (!$product) {
                continue;
            }

            $item = StockCountItem::where('stock_count_id', $this->stockCountId)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                $actual = (float) $actualQty;
                $item->update([
                    'actual_quantity' => $actual,
                    'physical_quantity' => $actual,
                    'difference' => $actual - $item->system_quantity,
                ]);
            }
        }
    }
}
