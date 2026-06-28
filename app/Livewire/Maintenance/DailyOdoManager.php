<?php

namespace App\Livewire\Maintenance;

use App\Models\AssetDailyOdo;
use App\Models\Setting;
use App\Services\MaintenanceService;
use Carbon\Carbon;
use Livewire\Component;

class DailyOdoManager extends Component
{
    public $selectedDate;
    public $dailyRecords = [];
    public $autoCronEnabled = false;

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->autoCronEnabled = Setting::getVal('auto_daily_odo_enabled', 'false') === 'true';
        $this->loadRecords();
    }

    public function updatedSelectedDate()
    {
        $this->loadRecords();
    }

    public function toggleAutoCron()
    {
        $this->autoCronEnabled = !$this->autoCronEnabled;
        Setting::setVal('auto_daily_odo_enabled', $this->autoCronEnabled ? 'true' : 'false');
        session()->flash('message', 'Đã ' . ($this->autoCronEnabled ? 'bật' : 'tắt') . ' tính năng tạo ODO tự động.');
    }

    public function loadRecords()
    {
        $records = AssetDailyOdo::with(['asset', 'updater'])
            ->whereDate('reading_date', $this->selectedDate)
            ->get();
            
        $this->dailyRecords = $records->map(function ($record) {
            return [
                'id' => $record->id,
                'asset_code' => $record->asset->asset_code ?? '',
                'asset_name' => $record->asset->name ?? '',
                'old_odo' => $record->old_odo,
                'odo_diff' => $record->odo_diff,
                'new_odo' => $record->new_odo,
                'old_hours' => $record->old_hours,
                'hours_diff' => $record->hours_diff,
                'new_hours' => $record->new_hours,
                'operator' => $record->operator,
                'phone' => $record->phone,
                'status' => $record->status,
                'updated_by_name' => optional($record->updater)->name ?? '',
            ];
        })->toArray();
    }

    public function generateNewDay(MaintenanceService $service)
    {
        $service->generatePendingDailyOdos($this->selectedDate);
        $this->loadRecords();
        session()->flash('message', 'Đã tạo danh sách chờ duyệt thành công cho ngày ' . $this->selectedDate);
    }

    public function updateSingleRecord(MaintenanceService $service, $index)
    {
        if (!isset($this->dailyRecords[$index])) {
            return;
        }

        $data = $this->dailyRecords[$index];
        $success = $service->approveSingleDailyOdo($data, auth()->id());
        
        $this->loadRecords(); // reload to get new calculation
        
        if ($success) {
            session()->flash('message', 'Đã cập nhật dòng thành công!');
        } else {
            session()->flash('error', 'Cập nhật thất bại hoặc dòng đã được khóa.');
        }
    }

    public function cancelUpdate()
    {
        $this->loadRecords();
        session()->flash('message', 'Đã hủy các thay đổi chưa lưu.');
    }

    public function updateBatch(MaintenanceService $service)
    {
        $service->approveBatchDailyOdos($this->dailyRecords, auth()->id());
        $this->loadRecords();
        session()->flash('message', 'Đã cập nhật và cộng giờ hàng loạt thành công!');
    }

    public function render()
    {
        return view('components.maintenance.daily-odo-manager');
    }
}
