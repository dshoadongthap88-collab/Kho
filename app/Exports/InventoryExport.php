<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'Mã Vật Tư',
            'Tên Vật Tư',
            'Hãng SX',
            'ĐVT',
            'Vị Trí',
            'Tồn Kho',
            'Đang Chờ Xuất',
            'Tồn Khả Dụng',
            'Tồn Tối Thiểu',
            'Số Lô',
            'Hạn Dùng',
        ];
    }

    public function map($item): array
    {
        $available = $item->quantity - $item->reserved_quantity;
        return [
            $item->product_code,
            $item->product_name,
            $item->brand,
            $item->unit,
            $item->warehouse_location,
            $item->quantity,
            $item->reserved_quantity,
            $available,
            $item->min_stock,
            $item->batch_number,
            $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('d/m/Y') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
