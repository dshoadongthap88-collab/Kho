<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceTicket;
use Livewire\Component;

class TicketList extends Component
{
    public $selectedTickets = [];
    public $selectAll = false;

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedTickets = MaintenanceTicket::pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedTickets = [];
        }
    }

    public function printSelected()
    {
        if (empty($this->selectedTickets)) {
            session()->flash('error', 'Vui lòng chọn ít nhất một phiếu để in.');
            return;
        }

        // Redirect to a print view with selected IDs
        $ids = implode(',', $this->selectedTickets);
        return redirect()->route('warehouse.maintenance-tickets.print', ['ids' => $ids]);
    }

    public function render()
    {
        $tickets = MaintenanceTicket::with('asset')->orderBy('created_at', 'desc')->get();
        return view('components.maintenance.ticket-list', compact('tickets'));
    }
}
