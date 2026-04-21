<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockInListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Mã Phiếu',
            'Ngày lập',
            'Nhà cung cấp / Đối tác',
            'Loại nhập',
            'Tổng tiền',
            'Ghi chú',
            'Người lập'
        ];
    }

    public function map($si): array
    {
        $typeLabels = [
            'purchase_produced' => 'Nhập mua hàng',
            'import_material' => 'Nhập nguyên liệu',
            'production' => 'Nhập từ sản xuất',
            'return' => 'Hàng trả lại',
        ];

        return [
            $si->code,
            $si->created_at->format('d/m/Y H:i'),
            $si->supplier_name ?: $si->manufacturer,
            $typeLabels[$si->type] ?? $si->type,
            $si->items->sum('total_amount'),
            $si->note,
            $si->creator->name ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
