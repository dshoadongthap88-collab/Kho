<?php

namespace App\Livewire\Warehouse;

use App\Models\StockTransfer;
use Livewire\Component;
use Livewire\WithPagination;

class StockTransferList extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $transfers = StockTransfer::with(['creator', 'items'])
            ->where('transfer_code', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.warehouse.stock-transfer-list', [
            'transfers' => $transfers
        ])->layout('components.warehouse-layout', ['title' => 'Lịch sử chuyển kho']);
    }
}
