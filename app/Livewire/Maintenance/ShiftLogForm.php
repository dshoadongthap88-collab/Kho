<?php

namespace App\Livewire\Maintenance;

use App\Models\Asset;
use App\Services\MaintenanceService;
use Livewire\Component;

class ShiftLogForm extends Component
{
    public $assetId;
    public $shiftsCount = 1;
    public $readingDate;
    public $note;

    protected $rules = [
        'assetId' => 'required|exists:assets,id',
        'shiftsCount' => 'required|numeric|min:0.5',
        'readingDate' => 'required|date',
    ];

    public function mount()
    {
        $this->readingDate = now()->format('Y-m-d');
    }

    public function submit(MaintenanceService $service)
    {
        $this->validate();

        $asset = Asset::find($this->assetId);
        
        $service->logDailyShifts(
            $asset, 
            $this->shiftsCount, 
            $this->readingDate, 
            auth()->id(), 
            $this->note
        );

        session()->flash('message', 'Đã cập nhật số ca thành công! Giờ máy đã được cộng dồn.');
        $this->reset(['shiftsCount', 'note', 'assetId']);
    }

    public function render()
    {
        $assets = Asset::where('status', 'active')->get();
        return view('components.maintenance.shift-log-form', compact('assets'));
    }
}
