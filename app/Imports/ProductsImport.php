<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToModel, WithHeadingRow, WithUpserts, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Handle date conversion if needed
        $expiry_date = null;
        if (isset($row['han_dung']) && !empty($row['han_dung'])) {
            try {
                // If it's a numeric value from Excel (Excel serial date)
                if (is_numeric($row['han_dung'])) {
                    $expiry_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['han_dung']);
                } else {
                    $expiry_date = Carbon::parse($row['han_dung']);
                }
            } catch (\Exception $e) {
                // Ignore invalid date formats
            }
        }

        $product = Product::updateOrCreate(
            ['code' => $row['ma_sp']],
            [
                'name'         => $row['ten_san_pham'],
                'brand'        => $row['hang_san_xuat'] ?? null,
                'box_spec'     => $row['qc_hop'] ?? null,
                'carton_spec'  => $row['qc_thung'] ?? null,
                'status'       => 'active',
                'location'     => $row['vi_tri'] ?? null,
                'batch_number' => $row['so_lo'],
                'expiry_date'  => $expiry_date,
            ]
        );

        // Also ensure inventory record exists
        if (!$product->inventory) {
            Inventory::create([
                'product_id' => $product->id,
                'quantity'   => $row['so_luong'] ?? 0,
                'warehouse_location' => $row['vi_tri'] ?? null,
            ]);
        } else {
            // Update inventory if location or quantity is provided
            $product->inventory->update([
                'warehouse_location' => $row['vi_tri'] ?? $product->inventory->warehouse_location,
                'quantity' => isset($row['so_luong']) ? $row['so_luong'] : $product->inventory->quantity,
            ]);
        }

        return $product;
    }

    /**
     * Unique key for upsert
     */
    public function uniqueBy()
    {
        return 'code';
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'ma_sp'        => 'required',
            'ten_san_pham' => 'required',
            'so_lo'        => 'required',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'ma_sp.required'        => 'Thiếu mã sản phẩm.',
            'ten_san_pham.required' => 'Thiếu tên sản phẩm.',
            'so_lo.required'        => 'Thiếu mã Code NCC.',
        ];
    }
}
