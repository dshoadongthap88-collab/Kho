<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceTicket;
use Livewire\Component;

class TicketList extends Component
{
    public function render()
    {
        $tickets = MaintenanceTicket::with('asset')->orderBy('created_at', 'desc')->get();
        return view('components.maintenance.ticket-list', compact('tickets'));
    }
}
