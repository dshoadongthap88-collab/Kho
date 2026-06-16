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

    public function exportList()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ContactsExport, 'danh_sach_doi_tac.xlsx');
    }

    public function render()
    {
        return view('livewire.warehouse.customer-management');
    }
}
