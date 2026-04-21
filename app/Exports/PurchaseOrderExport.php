<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseOrderExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Mã PO',
            'Ngày đặt',
            'Nhà cung cấp',
            'Ngày dự kiến giao',
            'Tổng tiền',
            'Trạng thái',
            'Ghi chú'
        ];
    }

    public function map($po): array
    {
        $statusLabels = [
            'pending' => 'Đang chờ',
            'confirmed' => 'Đã xác nhận',
            'received' => 'Đã nhập kho',
            'cancelled' => 'Đã hủy',
        ];

        return [
            $po->po_number,
            $po->order_date ? $po->order_date->format('d/m/Y') : '',
            $po->supplier->name ?? 'N/A',
            $po->expected_delivery_date ? $po->expected_delivery_date->format('d/m/Y') : '',
            $po->total_amount,
            $statusLabels[$po->status] ?? $po->status,
            $po->notes
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
