<?php

namespace App\Imports;

use App\Models\AssetDailyOdo;
use App\Models\Asset;
use App\Models\MaintenanceRule;
use App\Models\MaintenancePlan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class AssetOdoImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Mapping headings (adjust based on actual excel headers)
            // Example: ma_may, ngay_bao_cao, odo_hien_tai, gio_hien_tai, ghi_chu
            
            $assetCode = $row['ma_tai_san'] ?? $row['ma_may'] ?? null;
            if (!$assetCode) {
                throw new \Exception("Dòng có mã tài sản trống. Vui lòng kiểm tra lại file Excel.");
            }

            $asset = Asset::where('asset_code', $assetCode)->first();
            if (!$asset) {
                throw new \Exception("Mã tài sản '{$assetCode}' không tồn tại trong hệ thống.");
            }

            // ODO mới (từ file Excel)
            $newOdo = $row['odo_hien_tai'] ?? $asset->lifetime_odo ?? 0;
            // Số giờ làm việc (ca) mới, ví dụ 8h, 12h
            $hoursDiff = $row['so_gio_lam_viec'] ?? $row['so_gio'] ?? 0;
            $newHours = $asset->lifetime_hours + $hoursDiff;
            
            // Validation: new cannot be smaller than old
            if ($newOdo < $asset->lifetime_odo) {
                throw new \Exception("Thiết bị '{$assetCode}': ODO hiện tại ({$newOdo}) không được nhỏ hơn ODO tích lũy ({$asset->lifetime_odo}).");
            }

            $dateRaw = $row['ngay_bao_cao'] ?? $row['date'] ?? now();
            try {
                $readingDate = Carbon::parse($dateRaw)->format('Y-m-d');
            } catch (\Exception $e) {
                $readingDate = now()->format('Y-m-d');
            }

            $odoDiff = $newOdo - ($asset->lifetime_odo ?? 0);
            $operator = $row['nhan_vien_lai_xe'] ?? $row['nhan_vien'] ?? null;

            // Log
            AssetDailyOdo::create([
                'reading_date' => $readingDate,
                'asset_id' => $asset->id,
                'operator' => $operator,
                'old_odo' => $asset->lifetime_odo ?? 0,
                'new_odo' => $newOdo,
                'odo_diff' => $odoDiff,
                'old_hours' => $asset->lifetime_hours ?? 0,
                'new_hours' => $newHours,
                'hours_diff' => $hoursDiff,
                'updated_by' => auth()->user()->name ?? 'Excel Import',
                'note' => $row['ghi_chu'] ?? $row['note'] ?? 'Imported from Excel',
                'is_synced' => false,
            ]);

            // Asset will be updated automatically by cron job at 00:01

        }
    }
}
