<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerDebtExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Số phiếu',
            'Tên khách hàng',
            'Ngày giao hàng',
            'Ngày hạn thanh toán',
            'Tổng tiền hóa đơn',
            'Đã thanh toán',
            'Còn nợ',
            'Trạng thái'
        ];
    }

    public function map($debt): array
    {
        $remaining = $debt->total_amount - $debt->paid_amount;
        $status = ($remaining <= 0) ? 'Đã thu đủ' : (($debt->paid_amount > 0) ? 'Đang trả dần' : 'Chưa thanh toán');

        return [
            $debt->stockOut->code ?? 'N/A',
            $debt->customer_name,
            $debt->delivered_at ? $debt->delivered_at->format('d/m/Y') : '',
            $debt->due_date ? $debt->due_date->format('d/m/Y') : '',
            $debt->total_amount,
            $debt->paid_amount,
            $remaining,
            $status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
