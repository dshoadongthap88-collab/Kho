<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;

class CustomerManagement extends Component
{
    public $activeTab = 'contacts'; // contacts, purchase-orders

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.warehouse.customer-management');
    }
}
