<?php

namespace App\Livewire\Warehouse;

use App\Models\AssetOdoReading;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssetOdoReadingsImport;
use Illuminate\Support\Facades\Storage;

class OdoManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';
    protected $layout = 'components.warehouse-layout';

    public $search = '';
    public $filterStatus = ''; // all, maintenance_required, maintenance_done, normal
    public $filterDateFrom = '';
    public $filterDateTo = '';

    public $selectedAssetId = null;
    public $readingDate;
    public $currentHours;
    public $operator;
    public $status = 'normal';
    public $notes;

    public $showModal = false;
    public $showImportModal = false;
    public $isEdit = false;
    public $readingId;

    public $excelFile;
    public $selectedIds = [];

    protected $queryString = ['search', 'filterStatus', 'filterDateFrom', 'filterDateTo'];

    public function mount()
    {
        $this->readingDate = now()->format('Y-m-d');
    }

    public function rules()
    {
        return [
            'selectedAssetId' => 'required|exists:products,id',
            'readingDate' => 'required|date',
            'currentHours' => 'required|numeric|min:0',
            'operator' => 'nullable|string|max:100',
            'status' => 'required|in:maintenance_required,maintenance_done,normal',
            'notes' => 'nullable|string',
        ];
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatedFilterDateTo()
    {
        $this->resetPage();
    }

    public function openModal($readingId = null)
    {
        $this->resetValidation();
        $this->reset(['selectedAssetId', 'currentHours', 'operator', 'status', 'notes', 'readingId']);

        if ($readingId) {
            $this->isEdit = true;
            $this->readingId = $readingId;
            $reading = AssetOdoReading::with('product')->findOrFail($readingId);
            $this->selectedAssetId = $reading->product_id;
            $this->readingDate = $reading->reading_date->format('Y-m-d');
            $this->currentHours = $reading->current_hours;
            $this->operator = $reading->operator;
            $this->status = $reading->status;
            $this->notes = $reading->notes;
        } else {
            $this->isEdit = false;
            $this->readingDate = now()->format('Y-m-d');
            $this->status = 'normal';
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'product_id' => $this->selectedAssetId,
                'reading_date' => $this->readingDate,
                'current_hours' => $this->currentHours,
                'operator' => $this->operator,
                'status' => $this->status,
                'notes' => $this->notes,
            ];

            if ($this->isEdit) {
                AssetOdoReading::find($this->readingId)->update($data);
                session()->flash('message', 'Cập nhật số giờ odo thành công.');
            } else {
                // Kiểm tra đã có reading cho ngày này chưa
                $existing = AssetOdoReading::where('product_id', $this->selectedAssetId)
                    ->where('reading_date', $this->readingDate)
                    ->first();

                if ($existing) {
                    session()->flash('error', 'Đã có bản ghi số giờ cho tài sản này trong ngày ' . $this->readingDate . '. Vui lòng chỉnh sửa thay vì thêm mới.');
                    return;
                }

                AssetOdoReading::create($data);
                session()->flash('message', 'Thêm số giờ odo thành công.');
            }

            $this->reset(['selectedAssetId', 'currentHours', 'operator', 'status', 'notes']);
            $this->showModal = false;

        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            AssetOdoReading::findOrFail($id)->delete();
            session()->flash('message', 'Đã xóa bản ghi odo thành công.');
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa bản ghi này.');
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedIds)) return;

        try {
            $count = AssetOdoReading::whereIn('id', $this->selectedIds)->delete();
            $this->selectedIds = [];
            session()->flash('message', "Đã xóa {$count} bản ghi odo.");
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra khi xóa.');
        }
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new AssetOdoReadingsImport, $this->excelFile);
            $this->reset(['excelFile', 'showImportModal']);
            session()->flash('message', 'Nhập dữ liệu odo từ Excel thành công!');
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = AssetOdoReading::query()
            ->with(['product' => function($q) {
                $q->select('id', 'code', 'name', 'location');
            }])
            ->when($this->search, function($q) {
                $q->whereHas('product', function($pq) {
                    $pq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function($q) {
                $q->where('status', $this->filterStatus);
            })
            ->when($this->filterDateFrom, function($q) {
                $q->where('reading_date', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function($q) {
                $q->where('reading_date', '<=', $this->filterDateTo);
            })
            ->orderBy('reading_date', 'desc')
            ->orderBy('created_at', 'desc');

        $readings = $query->paginate(15);

        // Lấy danh sách tài sản (products có type = product_produced hoặc product_purchased)
        $assets = Product::whereIn('type', ['product_produced', 'product_purchased'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('livewire.warehouse.odo-manager', [
            'readings' => $readings,
            'assets' => $assets,
            'allReadingIdsOnPage' => $readings->pluck('id')->toArray()
        ]);
    }
}
