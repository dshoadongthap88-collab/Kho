<?php

namespace App\Exports;

use App\Models\AssetDailyOdo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AssetOdoExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filterDate;
    protected $search;

    public function __construct($filterDate, $search)
    {
        $this->filterDate = $filterDate;
        $this->search = $search;
    }

    public function collection()
    {
        return AssetDailyOdo::with('asset')
            ->whereDate('reading_date', $this->filterDate)
            ->when($this->search, function($q) {
                $q->whereHas('asset', function($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('asset_code', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Mã tài sản',
            'Thiết bị',
            'Ngày báo cáo',
            'ODO tích lũy (cũ)',
            'ODO hiện tại',
            'Số giờ làm việc (ca)',
            'Nhân viên lái xe',
            'Ghi chú'
        ];
    }

    public function map($log): array
    {
        return [
            $log->asset->asset_code ?? '',
            $log->asset->name ?? '',
            $log->reading_date ? $log->reading_date->format('Y-m-d') : '',
            $log->old_odo,
            $log->new_odo,
            $log->hours_diff,
            $log->operator,
            $log->note
        ];
    }
}
