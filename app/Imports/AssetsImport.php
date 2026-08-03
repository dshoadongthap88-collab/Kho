<?php

namespace App\Imports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class AssetsImport implements ToModel, WithHeadingRow, WithUpserts, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return Asset::updateOrCreate(
            ['equipment_code' => $row['ma_thiet_bi']],
            [
                'name'         => $row['ten_thiet_bi'],
                'machine_type' => $row['loai_thiet_bi'] ?? null,
                'asset_code'   => $row['ma_tai_san'] ?? null,
                'manager'      => $row['nguoi_quan_ly'] ?? null,
                'warranty_status'=> $row['tinh_trang'] ?? 'Còn bảo hành',
                'department'   => 'KHO',
                'model'        => 'N/A',
            ]
        );
    }

    /**
     * Unique key for upsert
     */
    public function uniqueBy()
    {
        return 'equipment_code';
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'ma_thiet_bi'  => 'required',
            'ten_thiet_bi' => 'required',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'ma_thiet_bi.required'  => 'Thiếu mã thiết bị.',
            'ten_thiet_bi.required' => 'Thiếu tên thiết bị.',
        ];
    }
}
