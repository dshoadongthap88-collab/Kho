<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockOutListExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Khách hàng / Bộ phận',
            'Loại xuất',
            'Tổng tiền',
            'Ghi chú',
            'Người lập'
        ];
    }

    public function map($so): array
    {
        $typeLabels = [
            'production' => 'Sản xuất',
            'delivery' => 'Giao khách hàng',
            'disposal' => 'Xuất hủy',
            'manual' => 'Xuất khác',
        ];

        return [
            $so->code,
            $so->created_at->format('d/m/Y H:i'),
            $so->customer_name ?: $so->receiver_department,
            $typeLabels[$so->type] ?? $so->type,
            $so->items->sum('total_amount'),
            $so->note,
            $so->creator->name ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
