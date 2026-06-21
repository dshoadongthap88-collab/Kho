<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetDailyOdo;
use App\Models\Asset;
use App\Models\MaintenanceRule;
use App\Models\MaintenancePlan;

class AssetOdoLog extends Component
{
    use WithPagination;

    public $search = '';
    public $isModalOpen = false;

    // Form fields
    public $logId;
    public $reading_date;
    public $asset_id;
    public $old_odo = 0;
    public $new_odo = 0;
    public $odo_diff = 0;
    public $old_hours = 0;
    public $new_hours = 0;
    public $hours_diff = 0;
    public $note;

    protected $rules = [
        'reading_date' => 'required|date',
        'asset_id' => 'required|exists:assets,id',
        'new_odo' => 'required|numeric|min:0',
        'new_hours' => 'required|numeric|min:0',
        'note' => 'nullable|string',
    ];

    public function mount()
    {
        $this->reading_date = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedAssetId($value)
    {
        if ($value) {
            $asset = Asset::find($value);
            if ($asset) {
                $this->old_odo = $asset->current_odo ?? 0;
                $this->old_hours = $asset->current_hours ?? 0;
                $this->new_odo = $this->old_odo;
                $this->new_hours = $this->old_hours;
            }
        }
    }

    public function updatedNewOdo($value)
    {
        $this->odo_diff = (float)$value - (float)$this->old_odo;
    }

    public function updatedNewHours($value)
    {
        $this->hours_diff = (float)$value - (float)$this->old_hours;
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->logId = null;
        $this->asset_id = null;
        $this->old_odo = 0;
        $this->new_odo = 0;
        $this->odo_diff = 0;
        $this->old_hours = 0;
        $this->new_hours = 0;
        $this->hours_diff = 0;
        $this->note = '';
        $this->reading_date = now()->format('Y-m-d');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $log = AssetDailyOdo::findOrFail($id);
        
        $this->logId = $log->id;
        $this->asset_id = $log->asset_id;
        $this->reading_date = $log->reading_date->format('Y-m-d');
        $this->old_odo = $log->old_odo;
        $this->new_odo = $log->new_odo;
        $this->odo_diff = $log->odo_diff;
        $this->old_hours = $log->old_hours;
        $this->new_hours = $log->new_hours;
        $this->hours_diff = $log->hours_diff;
        $this->note = $log->note;

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->new_odo < $this->old_odo) {
            $this->addError('new_odo', 'Odo mới không được nhỏ hơn Odo cũ.');
            return;
        }

        if ($this->new_hours < $this->old_hours) {
            $this->addError('new_hours', 'Giờ máy mới không được nhỏ hơn giờ máy cũ.');
            return;
        }

        $this->odo_diff = $this->new_odo - $this->old_odo;
        $this->hours_diff = $this->new_hours - $this->old_hours;

        AssetDailyOdo::updateOrCreate(
            ['id' => $this->logId],
            [
                'reading_date' => $this->reading_date,
                'asset_id' => $this->asset_id,
                'old_odo' => $this->old_odo,
                'new_odo' => $this->new_odo,
                'odo_diff' => $this->odo_diff,
                'old_hours' => $this->old_hours,
                'new_hours' => $this->new_hours,
                'hours_diff' => $this->hours_diff,
                'updated_by' => auth()->user()->name ?? 'System',
                'note' => $this->note,
            ]
        );

        // Update Asset
        $asset = Asset::find($this->asset_id);
        if ($asset && !$this->logId) { // Chỉ update current asset nếu là thêm mới, edit thì logic phức tạp hơn nên bỏ qua hoặc tuỳ chỉnh sau
            $asset->current_odo = $this->new_odo;
            $asset->current_hours = $this->new_hours;
            $asset->save();

            // Check maintenance rules and auto generate plans
            $this->checkAndGenerateMaintenancePlans($asset);
        }

        $this->closeModal();
        session()->flash('message', $this->logId ? 'Cập nhật thành công.' : 'Thêm mới thành công.');
    }

    private function checkAndGenerateMaintenancePlans($asset)
    {
        if (!$asset->machine_type) return;

        $rules = MaintenanceRule::where('machine_type', $asset->machine_type)->get();

        foreach ($rules as $rule) {
            $needsMaintenance = false;

            if ($rule->cycle_km > 0 && $asset->current_odo > 0) {
                // Kiểm tra xem plan gần nhất cho hạng mục này ở Odo nào
                $lastPlan = MaintenancePlan::where('asset_id', $asset->id)
                    ->where('category', $rule->category)
                    ->orderBy('id', 'desc')
                    ->first();

                $lastOdo = $lastPlan ? $lastPlan->maintenance_odo : 0;
                
                // Nếu khoảng cách ODO hiện tại và lần bảo dưỡng cuối lớn hơn chu kỳ
                if (($asset->current_odo - $lastOdo) >= $rule->cycle_km) {
                    $needsMaintenance = true;
                }
            }

            if ($rule->cycle_hours > 0 && $asset->current_hours > 0 && !$needsMaintenance) {
                 $lastPlan = MaintenancePlan::where('asset_id', $asset->id)
                    ->where('category', $rule->category)
                    ->orderBy('id', 'desc')
                    ->first();

                // Lấy giờ máy bảo dưỡng gần nhất (logic này tương tự ODO nhưng cần lưu trong plan, tạm tính theo ODO field hoặc tái sử dụng)
                $lastHours = $lastPlan ? $lastPlan->current_odo : 0; // Để đơn giản, coi current_odo lưu giờ nếu category dùng giờ

                if (($asset->current_hours - $lastHours) >= $rule->cycle_hours) {
                    $needsMaintenance = true;
                }
            }

            if ($needsMaintenance) {
                // Generate Maintenance Plan
                $targetOdo = $rule->cycle_km > 0 ? ($lastOdo ?? 0) + $rule->cycle_km : 0;
                $targetHours = $rule->cycle_hours > 0 ? ($lastHours ?? 0) + $rule->cycle_hours : 0;

                MaintenancePlan::create([
                    'plan_code' => 'BD-' . date('YmdHis') . '-' . rand(10,99),
                    'asset_id' => $asset->id,
                    'category' => $rule->category,
                    'expected_date' => now()->addDays(3), // Dự kiến 3 ngày sau
                    'current_odo' => $rule->cycle_km > 0 ? $asset->current_odo : $asset->current_hours,
                    'maintenance_odo' => $rule->cycle_km > 0 ? $targetOdo : $targetHours,
                    'status' => 'pending',
                ]);
            }
        }
    }

    public function delete($id)
    {
        AssetDailyOdo::findOrFail($id)->delete();
        session()->flash('message', 'Đã xóa bản ghi thành công.');
    }

    public function render()
    {
        $logs = AssetDailyOdo::with('asset')
            ->whereHas('asset', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('asset_code', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $assets = Asset::orderBy('name')->get();

        return view('livewire.warehouse.asset-odo-log', [
            'logs' => $logs,
            'assets' => $assets
        ])->layout('components.warehouse-layout', ['title' => 'Nhật Ký Odo Hàng Ngày']);
    }
}
