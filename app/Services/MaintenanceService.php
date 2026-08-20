<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetDailyOdo;
use App\Models\MaintenanceTicket;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MaintenanceService
{
    /**
     * Log daily shifts for an asset and update its ODO/Hours
     */
    public function logDailyShifts(Asset $asset, $shiftsCount, $readingDate, $updatedBy = null, $note = null)
    {
        $hoursAdded = $shiftsCount * $asset->hours_per_shift;
        $odoAdded = 0; // ODO calculation logic can be added if formula is provided

        $oldHours = $asset->current_hours;
        $newHours = $oldHours + $hoursAdded;

        $oldOdo = $asset->current_odo;
        $newOdo = $oldOdo + $odoAdded;

        // Create log entry
        AssetDailyOdo::create([
            'reading_date' => Carbon::parse($readingDate),
            'asset_id'     => $asset->id,
            'shifts_count' => $shiftsCount,
            'old_odo'      => $oldOdo,
            'new_odo'      => $newOdo,
            'odo_diff'     => $odoAdded,
            'old_hours'    => $oldHours,
            'new_hours'    => $newHours,
            'hours_diff'   => $hoursAdded,
            'updated_by'   => $updatedBy,
            'note'         => $note,
        ]);

        // Update asset
        $asset->current_hours = $newHours;
        $asset->current_odo = $newOdo;
        $asset->save();

        // Check for maintenance trigger
        $this->checkAndGenerateMaintenanceTicket($asset);

        return $asset;
    }

    /**
     * Check if asset needs maintenance and auto-generate ticket if required
     */
    public function checkAndGenerateMaintenanceTicket(Asset $asset)
    {
        // Don't generate if there's already a pending ticket
        $hasPending = MaintenanceTicket::where('asset_id', $asset->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if ($hasPending) {
            return false;
        }

        $needsMaintenance = false;
        $maintenanceRuleId = null;

        // Check by ODO (Số giờ còn lại <= 0)
        if ($asset->maintenance_cycle_odo > 0) {
            $hoursRun = $asset->current_odo - $asset->last_maintenance_odo;
            $hoursRemaining = $asset->maintenance_cycle_odo - $hoursRun;
            
            if ($hoursRemaining <= 0) {
                $needsMaintenance = true;
                $targetCycle = floor($asset->current_odo / $asset->maintenance_cycle_odo) * $asset->maintenance_cycle_odo;
                if ($targetCycle > 0) {
                    // Cấp bảo dưỡng, ví dụ: 250h, 500h, 1000h
                    $maintenanceRuleId = $targetCycle . 'h';
                }
            }
        }

        // Check by HOURS (giờ vận hành) — nếu chưa đến hạn theo ODO thì xét theo giờ chạy
        if (!$needsMaintenance && $asset->maintenance_cycle_hours > 0) {
            $hoursRun = $asset->current_hours - $asset->last_maintenance_hours;
            $hoursRemaining = $asset->maintenance_cycle_hours - $hoursRun;

            if ($hoursRemaining <= 0) {
                $needsMaintenance = true;
                $targetCycle = floor($asset->current_hours / $asset->maintenance_cycle_hours) * $asset->maintenance_cycle_hours;
                if ($targetCycle > 0) {
                    // Cấp bảo dưỡng theo giờ, ví dụ: 250h, 500h, 1000h
                    $maintenanceRuleId = $targetCycle . 'h';
                }
            }
        }

        if ($needsMaintenance) {
            return MaintenanceTicket::create([
                'ticket_code'         => 'MT-' . strtoupper(Str::random(6)),
                'asset_id'            => $asset->id,
                'maintenance_rule_id' => $maintenanceRuleId,
                'maintenance_date'    => now(), // Date it was triggered
                'maintenance_odo'     => $asset->current_odo,
                'type'                => 'auto_generated',
                'description'         => 'Tự động tạo phiếu bảo dưỡng do thiết bị đã đến hạn ' . ($maintenanceRuleId ? "($maintenanceRuleId)." : "."),
                'status'              => 'pending',
                'created_by'          => null, // System generated
            ]);
        }

        return false;
    }

    /**
     * Complete a maintenance ticket and update asset markers
     */
    public function completeMaintenanceTicket(MaintenanceTicket $ticket, $completionDate, $content, $replacedMaterials, $totalCost, $completedBy = null)
    {
        // Update ticket
        $ticket->status = 'completed';
        $ticket->maintenance_date = Carbon::parse($completionDate);
        $ticket->description = $content;
        $ticket->replaced_materials = $replacedMaterials;
        $ticket->total_cost = $totalCost;
        $ticket->save();

        // Reset asset's last maintenance markers
        $asset = $ticket->asset;
        $asset->last_maintenance_hours = $asset->current_hours;
        $asset->last_maintenance_odo = $asset->current_odo;
        $asset->save();

        return $ticket;
    }

    /**
     * Generate pending daily ODO logs for a given date
     */
    public function generatePendingDailyOdos($date)
    {
        $dateObj = Carbon::parse($date)->format('Y-m-d');
        $assets = Asset::where('status', 'active')->get();

        foreach ($assets as $asset) {
            $exists = AssetDailyOdo::where('asset_id', $asset->id)
                ->whereDate('reading_date', $dateObj)
                ->exists();

            if (!$exists) {
                AssetDailyOdo::create([
                    'reading_date' => $dateObj,
                    'asset_id'     => $asset->id,
                    'shifts_count' => 1,
                    'old_odo'      => $asset->current_odo,
                    'new_odo'      => $asset->current_odo + 8,
                    'odo_diff'     => 8, // Mặc định 8h
                    'old_hours'    => $asset->current_hours,
                    'new_hours'    => $asset->current_hours + 8,
                    'hours_diff'   => 8,
                    'operator'     => null,
                    'phone'        => null,
                    'status'       => 'pending',
                ]);
            }
        }
    }

    /**
     * Approve batch daily ODO updates
     */
    public function approveBatchDailyOdos($recordsData, $updatedBy = null)
    {
        foreach ($recordsData as $data) {
            $this->approveSingleDailyOdo($data, $updatedBy);
        }
    }

    /**
     * Approve single daily ODO update
     */
    public function approveSingleDailyOdo($data, $updatedBy = null)
    {
        $odoId = $data['id'];
        $hoursDiff = (float)($data['hours_diff'] ?? 8);
        $operator = $data['operator'] ?? null;
        $phone = $data['phone'] ?? null;

        $dailyLog = AssetDailyOdo::find($odoId);
        if (!$dailyLog || $dailyLog->status == 'approved') {
            return false;
        }

        $asset = $dailyLog->asset;
        
        // Recalculate
        $dailyLog->hours_diff = $hoursDiff;
        $dailyLog->old_hours = $asset->current_hours;
        $dailyLog->new_hours = $asset->current_hours + $hoursDiff;
        
        // For ODO as per spec: ODO hiện tại mới = ODO hiện tại cũ + Số giờ hoạt động
        $dailyLog->old_odo = $asset->current_odo;
        $dailyLog->odo_diff = $hoursDiff;
        $dailyLog->new_odo = $asset->current_odo + $hoursDiff;

        $dailyLog->operator = $operator;
        $dailyLog->phone = $phone;
        $dailyLog->status = 'approved';
        $dailyLog->updated_by = $updatedBy;
        $dailyLog->save();

        // Update asset
        $asset->current_hours = $dailyLog->new_hours;
        $asset->current_odo = $dailyLog->new_odo;
        $asset->save();

        // Maintenance trigger
        $this->checkAndGenerateMaintenanceTicket($asset);

        return true;
    }
}
