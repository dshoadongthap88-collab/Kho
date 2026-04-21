<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeliveryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            'Khách hàng',
            'Trạng thái',
            'Thanh toán',
            'Tổng tiền',
            'Ngày tạo',
            'Ghi chú'
        ];
    }

    public function map($report): array
    {
        return [
            $report->stockOut->code ?? 'N/A',
            $report->customer_name,
            $report->status == 'delivered' ? 'Đã giao' : 'Chờ giao',
            $this->formatPayment($report->payment_status),
            $report->stockOut ? $report->stockOut->items->sum('total_amount') : 0,
            optional($report->stockOut->created_at)->format('d/m/Y H:i'),
            $report->notes
        ];
    }

    private function formatPayment($status)
    {
        return match($status) {
            'paid' => 'Đã thanh toán',
            'bank_transfer' => 'Chuyển khoản',
            'debt' => 'Ghi nợ',
            default => 'Chưa thanh toán'
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
