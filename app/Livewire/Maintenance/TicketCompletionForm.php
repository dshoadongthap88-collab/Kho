<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceTicket;
use App\Services\MaintenanceService;
use Livewire\Component;

class TicketCompletionForm extends Component
{
    public $ticketId;
    public $completionDate;
    public $content;
    public $replacedMaterials;
    public $totalCost;

    protected $rules = [
        'ticketId' => 'required|exists:maintenance_tickets,id',
        'completionDate' => 'required|date',
        'content' => 'required|string',
        'totalCost' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->completionDate = now()->format('Y-m-d');
    }

    public function complete(MaintenanceService $service)
    {
        $this->validate();

        $ticket = MaintenanceTicket::find($this->ticketId);
        
        if ($ticket->status == 'completed') {
            session()->flash('error', 'Phiếu này đã được hoàn thành trước đó.');
            return;
        }

        $materialsArray = explode("\n", $this->replacedMaterials);

        $service->completeMaintenanceTicket(
            $ticket,
            $this->completionDate,
            $this->content,
            $materialsArray,
            $this->totalCost,
            auth()->id()
        );

        session()->flash('message', 'Đã xác nhận hoàn thành bảo dưỡng và cập nhật mốc bảo dưỡng mới!');
        $this->reset(['content', 'replacedMaterials', 'totalCost', 'ticketId']);
    }

    public function render()
    {
        $pendingTickets = MaintenanceTicket::with('asset')->where('status', 'pending')->get();
        return view('components.maintenance.ticket-completion-form', compact('pendingTickets'));
    }
}
