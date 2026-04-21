<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Thời gian',
            'Sản phẩm',
            'Mã SP',
            'Loại',
            'Số lượng',
            'Người thực hiện',
            'Ghi chú'
        ];
    }

    public function map($tx): array
    {
        $typeLabels = [
            'import' => 'Nhập kho',
            'export' => 'Xuất kho',
            'adjust' => 'Điều chỉnh',
        ];

        return [
            $tx->created_at->format('d/m/Y H:i'),
            $tx->product->name ?? '',
            $tx->product->code ?? '',
            $typeLabels[$tx->type] ?? $tx->type,
            $tx->quantity,
            $tx->creator->name ?? '',
            $tx->note
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
