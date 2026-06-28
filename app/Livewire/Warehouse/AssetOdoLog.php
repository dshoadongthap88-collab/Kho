<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetDailyOdo;
use App\Models\Asset;
use App\Models\MaintenanceRule;
use App\Models\MaintenancePlan;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssetOdoImport;

class AssetOdoLog extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $filterDate;
    public $isModalOpen = false;
    public $isImportModalOpen = false;
    public $excelFile;

    // Form fields
    public $logId;
    public $reading_date;
    public $asset_id;
    public $operator;
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
        'operator' => 'nullable|string|max:255',
        'new_odo' => 'required|numeric|min:0',
        'hours_diff' => 'required|numeric|min:0',
        'note' => 'nullable|string',
    ];

    public function mount()
    {
        $this->filterDate = now()->format('Y-m-d');
        $this->reading_date = now()->format('Y-m-d');
    }

    public function updatingFilterDate()
    {
        $this->resetPage();
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
                $this->old_odo = $asset->lifetime_odo ?? 0;
                $this->old_hours = $asset->lifetime_hours ?? 0;
                $this->new_odo = $this->old_odo;
                $this->hours_diff = 0;
                $this->new_hours = $this->old_hours;
            }
        }
    }

    public function updatedNewOdo($value)
    {
        $this->odo_diff = (float)$value - (float)$this->old_odo;
    }

    public function updatedHoursDiff($value)
    {
        $this->new_hours = (float)$this->old_hours + (float)$value;
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
        $this->operator = '';
        $this->old_odo = 0;
        $this->new_odo = 0;
        $this->odo_diff = 0;
        $this->old_hours = 0;
        $this->new_hours = 0;
        $this->hours_diff = 0;
        $this->note = '';
        $this->reading_date = $this->filterDate;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $log = AssetDailyOdo::findOrFail($id);
        
        $this->logId = $log->id;
        $this->asset_id = $log->asset_id;
        $this->operator = $log->operator;
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

        $this->odo_diff = $this->new_odo - $this->old_odo;
        $this->new_hours = $this->old_hours + $this->hours_diff;

        $data = [
            'reading_date' => $this->reading_date,
            'asset_id' => $this->asset_id,
            'operator' => $this->operator,
            'old_odo' => $this->old_odo,
            'new_odo' => $this->new_odo,
            'odo_diff' => $this->odo_diff,
            'old_hours' => $this->old_hours,
            'new_hours' => $this->new_hours,
            'hours_diff' => $this->hours_diff,
            'updated_by' => auth()->user()->name ?? 'System',
            'note' => $this->note,
            'is_synced' => false,
        ];

        if ($this->logId) {
            AssetDailyOdo::where('id', $this->logId)->update($data);
        } else {
            AssetDailyOdo::create($data);
        }

        $this->closeModal();
        session()->flash('message', 'Đã lưu nhật ký thành công! Số liệu sẽ được chốt tự động vào máy lúc 00:01.');
    }

    public function delete($id)
    {
        AssetDailyOdo::findOrFail($id)->delete();
        session()->flash('message', 'Đã xóa bản ghi thành công.');
    }

    public function openImportModal()
    {
        $this->resetValidation();
        $this->excelFile = null;
        $this->isImportModalOpen = true;
    }

    public function closeImportModal()
    {
        $this->isImportModalOpen = false;
        $this->excelFile = null;
        $this->resetValidation();
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB
        ]);

        try {
            Excel::import(new AssetOdoImport, $this->excelFile);
            session()->flash('message', 'Import dữ liệu Odo thành công!');
            $this->closeImportModal();
        } catch (\Exception $e) {
            $this->addError('excelFile', 'Lỗi import: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        return Excel::download(new \App\Exports\AssetOdoExport($this->filterDate, $this->search), 'Nhat_Ky_ODO_'.$this->filterDate.'.xlsx');
    }

    public function render()
    {
        $logs = AssetDailyOdo::with('asset')
            ->whereDate('reading_date', $this->filterDate)
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
