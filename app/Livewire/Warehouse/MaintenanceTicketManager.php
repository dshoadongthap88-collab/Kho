<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\MaintenanceTicket;
use App\Models\Asset;
use App\Models\MaintenanceRule;
use Illuminate\Support\Facades\Storage;

class MaintenanceTicketManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $isModalOpen = false;

    // Form fields
    public $ticketId;
    public $ticket_code;
    public $asset_id;
    public $maintenance_rule_id; // Cấp bảo dưỡng
    public $maintenance_date;
    public $maintenance_odo; // Giờ máy tại thời điểm bảo dưỡng
    public $description; // Nội dung công việc
    public $materials_used; // Vật tư đã sử dụng
    public $staff_name; // Nhân viên thực hiện
    public $inspector; // Người kiểm tra
    public $result; // Kết quả
    public $notes;
    
    public $image_before;
    public $image_after;
    public $existing_image_before;
    public $existing_image_after;

    protected $rules = [
        'ticket_code' => 'required|string|max:100',
        'asset_id' => 'required|exists:assets,id',
        'maintenance_rule_id' => 'nullable|string',
        'maintenance_date' => 'required|date',
        'maintenance_odo' => 'nullable|numeric|min:0',
        'description' => 'nullable|string',
        'materials_used' => 'nullable|string',
        'staff_name' => 'nullable|string|max:150',
        'inspector' => 'nullable|string|max:150',
        'result' => 'nullable|string',
        'notes' => 'nullable|string',
        'image_before' => 'nullable|image|max:5120', // 5MB Max
        'image_after' => 'nullable|image|max:5120', // 5MB Max
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Auto-fill from query params if coming from tracking board
        if (request()->query('asset_id')) {
            $this->asset_id = request()->query('asset_id');
            $this->openModal();
        }
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->ticket_code = 'PB-' . date('YmdHis') . '-' . rand(10,99);
        $this->maintenance_date = now()->format('Y-m-d');
        
        if ($this->asset_id) {
            $asset = Asset::find($this->asset_id);
            if ($asset) {
                $this->maintenance_odo = $asset->current_hours;
            }
        }
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
        $this->ticketId = null;
        $this->ticket_code = '';
        if (!request()->query('asset_id')) {
            $this->asset_id = null;
        }
        $this->maintenance_rule_id = '';
        $this->maintenance_date = now()->format('Y-m-d');
        $this->maintenance_odo = 0;
        $this->description = '';
        $this->materials_used = '';
        $this->staff_name = '';
        $this->inspector = '';
        $this->result = '';
        $this->notes = '';
        $this->image_before = null;
        $this->image_after = null;
        $this->existing_image_before = null;
        $this->existing_image_after = null;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $ticket = MaintenanceTicket::findOrFail($id);
        
        $this->ticketId = $ticket->id;
        $this->ticket_code = $ticket->ticket_code;
        $this->asset_id = $ticket->asset_id;
        $this->maintenance_rule_id = $ticket->maintenance_rule_id;
        $this->maintenance_date = $ticket->maintenance_date ? $ticket->maintenance_date->format('Y-m-d') : now()->format('Y-m-d');
        $this->maintenance_odo = $ticket->maintenance_odo;
        $this->description = $ticket->description;
        $this->materials_used = $ticket->materials_used;
        $this->staff_name = $ticket->staff_name;
        $this->inspector = $ticket->inspector;
        $this->result = $ticket->result;
        $this->notes = $ticket->notes;
        $this->existing_image_before = $ticket->image_before;
        $this->existing_image_after = $ticket->image_after;

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'ticket_code' => $this->ticket_code,
            'asset_id' => $this->asset_id,
            'maintenance_rule_id' => $this->maintenance_rule_id,
            'maintenance_date' => $this->maintenance_date,
            'maintenance_odo' => $this->maintenance_odo,
            'description' => $this->description,
            'materials_used' => $this->materials_used,
            'staff_name' => $this->staff_name,
            'inspector' => $this->inspector,
            'result' => $this->result,
            'notes' => $this->notes,
            'status' => 'completed', // Assuming tickets are created when work is done
        ];

        if ($this->image_before) {
            $data['image_before'] = $this->image_before->store('maintenance_images', 'public');
        }

        if ($this->image_after) {
            $data['image_after'] = $this->image_after->store('maintenance_images', 'public');
        }

        if (!$this->ticketId) {
            $data['created_by'] = auth()->id();
            MaintenanceTicket::create($data);
        } else {
            MaintenanceTicket::where('id', $this->ticketId)->update($data);
        }

        $this->closeModal();
        session()->flash('message', $this->ticketId ? 'Cập nhật phiếu bảo dưỡng thành công.' : 'Tạo phiếu bảo dưỡng thành công.');
    }

    public function delete($id)
    {
        $ticket = MaintenanceTicket::findOrFail($id);
        if ($ticket->image_before) Storage::disk('public')->delete($ticket->image_before);
        if ($ticket->image_after) Storage::disk('public')->delete($ticket->image_after);
        $ticket->delete();
        session()->flash('message', 'Đã xóa phiếu bảo dưỡng.');
    }

    public function render()
    {
        $tickets = MaintenanceTicket::with('asset')
            ->where(function($q) {
                $q->where('ticket_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('asset', function($aq) {
                      $aq->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('asset_code', 'like', '%' . $this->search . '%');
                  });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $assets = Asset::where('status', '!=', 'inactive')->orderBy('name')->get();
        
        $rules = [];
        if ($this->asset_id) {
            $asset = $assets->firstWhere('id', $this->asset_id);
            if ($asset) {
                $rules = MaintenanceRule::where('machine_type', $asset->machine_type)->get();
            }
        } else {
            $rules = MaintenanceRule::all();
        }

        return view('livewire.warehouse.maintenance-ticket-manager', [
            'tickets' => $tickets,
            'assets' => $assets,
            'rules' => $rules
        ])->layout('components.warehouse-layout', ['title' => 'Phiếu Thực Hiện Bảo Dưỡng']);
    }
}
