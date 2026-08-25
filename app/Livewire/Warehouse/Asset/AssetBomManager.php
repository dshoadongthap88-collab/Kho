<?php

namespace App\Livewire\Warehouse\Asset;

use Livewire\Component;
use Livewire\Attributes\Url;
use App\Models\Asset;
use App\Models\Product;
use App\Models\MaintenanceBom;
use App\Models\MaintenanceBomItem;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class AssetBomManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Old inline binding properties are removed as we now use multi-cycle BOM
    
    // Thêm thiết bị nhanhfor adding new asset
    public $isAddingAsset = false;
    public $new_asset_code = '';
    public $new_name = '';
    public $new_department = '';

    // Checkbox selections
    public $selectedIds = [];
    public $selectAll = false;
    #[Url(history: true)]
    public $search = '';

    // Excel import/export
    public $showImportModal = false;
    public $excelFile;

    // Asset editing
    public $showEditModal = false;
    public $editingAssetId = null;
    public $edit_asset_code = '';
    public $edit_name = '';
    public $edit_department = '';
    
    // New multi-cycle BOM properties
    public $bomItemsByCycle = [
        '250' => [],
        '500' => [],
        '1000' => [],
        '2000' => [],
        '4000' => []
    ];
    public $activeCycleTab = '250';
    // KHONG de danh sach vat tu trong property public: Livewire se nhet ca
    // 1050 ban ghi vao wire:snapshot (807KB) va gui di/ve moi lan bam.
    // Day la du lieu chi de hien thi -> dua xuong render() lam bien view.

    public function updatedSearch()
    {
        $this->resetPage();
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    public function mount()
    {
        $this->initFields();
    }

    public function initFields()
    {
        // Removed old property initialization since inline edit is removed
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $assetsQuery = Asset::query();
            if ($this->search) {
                $assetsQuery->where(function($q) {
                    $q->where('asset_code', 'like', '%'.$this->search.'%')
                      ->orWhere('name', 'like', '%'.$this->search.'%')
                      ->orWhere('department', 'like', '%'.$this->search.'%');
                });
            }
            $this->selectedIds = $assetsQuery->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    // saveBoms() removed because we now edit BOMs via modal per asset

    public function toggleAddAsset()
    {
        $this->isAddingAsset = !$this->isAddingAsset;
        $this->new_asset_code = '';
        $this->new_name = '';
        $this->new_department = '';
    }

    public function addAsset()
    {
        $this->validate([
            'new_asset_code' => 'required|string|unique:assets,asset_code',
            'new_name' => 'required|string|max:255',
            'new_department' => 'nullable|string|max:255',
        ], [
            'new_asset_code.required' => 'Mã tài sản không được để trống.',
            'new_asset_code.unique' => 'Mã tài sản này đã tồn tại.',
            'new_name.required' => 'Tên thiết bị không được để trống.',
        ]);

        $asset = Asset::create([
            'asset_code' => $this->new_asset_code,
            'name' => $this->new_name,
            'department' => $this->new_department,
            'status' => 'active'
        ]);

        $this->isAddingAsset = false;
        session()->flash('message', 'Đã thêm thiết bị mới thành công.');
    }

    public function openEditModal()
    {
        if (count($this->selectedIds) !== 1) {
            session()->flash('error', 'Vui lòng tích chọn duy nhất một thiết bị để sửa.');
            return;
        }

        $id = $this->selectedIds[0];
        $asset = Asset::findOrFail($id);
        
        $this->editingAssetId = $asset->id;
        $this->edit_asset_code = $asset->asset_code;
        $this->edit_name = $asset->name;
        $this->edit_department = $asset->department;
        
        // Reset BOMs
        $this->bomItemsByCycle = [
            '250' => [], '500' => [], '1000' => [], '2000' => [], '4000' => []
        ];
        $this->activeCycleTab = '250';

        // Load existing Maintenance BOMs for this asset
        $boms = MaintenanceBom::with('items.product')->where('asset_id', $asset->id)->get();
        
        foreach ($boms as $bom) {
            $cycle = (string)$bom->cycle;
            if (!isset($this->bomItemsByCycle[$cycle])) {
                $this->bomItemsByCycle[$cycle] = [];
            }
            
            foreach ($bom->items as $item) {
                $this->bomItemsByCycle[$cycle][] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'backup_quantity' => $item->backup_quantity,
                    'note' => $item->note,
                ];
            }
        }
        
        $this->showEditModal = true;
    }

    public function setActiveCycleTab($cycle)
    {
        $this->activeCycleTab = $cycle;
    }

    public function addBomItem($cycle)
    {
        if (!isset($this->bomItemsByCycle[$cycle])) {
            $this->bomItemsByCycle[$cycle] = [];
        }
        $this->bomItemsByCycle[$cycle][] = [
            'product_id' => '', 
            'quantity' => 1,
            'backup_quantity' => 0,
            'note' => ''
        ];
    }

    public function removeBomItem($cycle, $index)
    {
        if (isset($this->bomItemsByCycle[$cycle][$index])) {
            unset($this->bomItemsByCycle[$cycle][$index]);
            $this->bomItemsByCycle[$cycle] = array_values($this->bomItemsByCycle[$cycle]);
        }
    }

    public function updateAsset()
    {
        $this->validate([
            'edit_asset_code' => 'required|string|unique:assets,asset_code,' . $this->editingAssetId,
            'edit_name' => 'required|string|max:255',
            'edit_department' => 'nullable|string|max:255',
        ], [
            'edit_asset_code.required' => 'Mã tài sản không được để trống.',
            'edit_asset_code.unique' => 'Mã tài sản này đã tồn tại.',
            'edit_name.required' => 'Tên thiết bị không được để trống.',
        ]);

        $asset = Asset::findOrFail($this->editingAssetId);
        $asset->update([
            'asset_code' => $this->edit_asset_code,
            'name' => $this->edit_name,
            'department' => $this->edit_department,
            // 'bom_details' is no longer used, we store in related tables
        ]);

        // Save BOMs by cycle
        foreach ($this->bomItemsByCycle as $cycle => $items) {
            // Check if there are valid items for this cycle
            $validItems = array_filter($items, function($item) {
                return !empty($item['product_id']);
            });

            if (count($validItems) > 0) {
                // Find or create MaintenanceBom
                $bom = MaintenanceBom::firstOrCreate(
                    [
                        'asset_id' => $asset->id,
                        'cycle' => $cycle,
                    ],
                    [
                        'bom_code' => 'MBOM-' . strtoupper(Str::random(8)),
                        'maintenance_level' => $cycle . ' giờ',
                        'created_by' => auth()->id() ?? 1,
                    ]
                );

                // Delete old items and insert new
                $bom->items()->delete();
                foreach ($validItems as $item) {
                    MaintenanceBomItem::create([
                        'maintenance_bom_id' => $bom->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'] ?? 0,
                        'backup_quantity' => $item['backup_quantity'] ?? 0,
                        'note' => $item['note'] ?? '',
                    ]);
                }
            } else {
                // If cycle has no items, delete the BOM if it exists
                $existingBom = MaintenanceBom::where('asset_id', $asset->id)->where('cycle', $cycle)->first();
                if ($existingBom) {
                    $existingBom->delete(); // cascading deletes items
                }
            }
        }

        $this->initFields();
        $this->showEditModal = false;
        $this->selectedIds = [];
        $this->selectAll = false;
        session()->flash('message', 'Đã cập nhật thông tin thiết bị thành công.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng tích chọn ít nhất một thiết bị để xóa.');
            return;
        }

        Asset::whereIn('id', $this->selectedIds)->delete();
        
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->initFields();
        session()->flash('message', 'Đã xóa các thiết bị được chọn thành công.');
    }

    public function printSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Vui lòng tích chọn ít nhất một thiết bị để in.');
            return;
        }

        $this->dispatch('trigger-print');
    }

    public function exportExcel()
    {
        $assets = Asset::all();
        
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=dinh_muc_ma_tai_san_" . now()->format('Ymd_His') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'Mã tài sản', 'Tên thiết bị', 'Bộ phận', 
            'Dầu động cơ 15W40 (Lít)', 'Nhớt thủy lực AW68 (Lít)', 
            'Lọc nhớt động cơ', 'Lọc thủy lực', 'Lọc gió', 'Chu kỳ'
        ];

        $callback = function() use($assets, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            foreach ($assets as $asset) {
                fputcsv($file, [
                    $asset->asset_code,
                    $asset->name,
                    $asset->department ?: '',
                    $asset->engine_oil_cap ?: '',
                    $asset->hydraulic_oil_cap ?: '',
                    $asset->engine_oil_filter ?: '',
                    $asset->hydraulic_filter ?: '',
                    $asset->air_filter ?: '',
                    $asset->maintenance_cycle ?: ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=mau_dinh_muc_ma_tai_san.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            'Mã tài sản', 'Tên thiết bị', 'Bộ phận', 
            'Dầu động cơ 15W40 (Lít)', 'Nhớt thủy lực AW68 (Lít)', 
            'Lọc nhớt động cơ', 'Lọc thủy lực', 'Lọc gió', 'Chu kỳ'
        ];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            
            fputcsv($file, [
                'TS-001', 'Xe nâng 3 tấn', 'Kho vận', '8', '35', 'LF-3349', 'HF-6710', 'AF-2555', '250 giờ'
            ]);
            fputcsv($file, [
                'TS-002', 'Máy xúc Kobelco', 'Cơ giới', '18', '120', 'LF-9001', 'HF-7602', 'AF-8721', '500 giờ'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'excelFile.required' => 'Vui lòng chọn tệp tin.',
            'excelFile.mimes' => 'Tệp tin phải có định dạng CSV, XLSX hoặc XLS.',
        ]);

        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $this->excelFile);
            if (!empty($data) && isset($data[0])) {
                $rows = $data[0];
                $header = array_shift($rows);
                
                // Khởi tạo các chỉ số cột mặc định là null
                $indices = [
                    'asset_code' => null,
                    'name' => null,
                    'department' => null,
                    'engine_oil_cap' => null,
                    'hydraulic_oil_cap' => null,
                    'engine_oil_filter' => null,
                    'hydraulic_filter' => null,
                    'air_filter' => null,
                    'maintenance_cycle' => null
                ];

                // Hàm chuẩn hóa chuỗi để so sánh
                $normalize = function($str) {
                    $str = mb_strtolower($str, 'UTF-8');
                    $str = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u', 'a', $str);
                    $str = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $str);
                    $str = preg_replace('/[íìỉĩị]/u', 'i', $str);
                    $str = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $str);
                    $str = preg_replace('/[úùủũụưứừửữự]/u', 'u', $str);
                    $str = preg_replace('/[ýỳỷỹỵ]/u', 'y', $str);
                    $str = preg_replace('/[đ]/u', 'd', $str);
                    $str = preg_replace('/[^a-z0-9]/', '', $str);
                    return $str;
                };

                // So khớp tiêu đề cột thông minh
                foreach ($header as $index => $colName) {
                    if (empty($colName)) continue;
                    $norm = $normalize($colName);
                    
                    if (str_contains($norm, 'mataisan') || str_contains($norm, 'assetcode') || str_contains($norm, 'macode') || $norm === 'ma' || $norm === 'code') {
                        $indices['asset_code'] = $index;
                    } elseif (str_contains($norm, 'tenthietbi') || str_contains($norm, 'tenmay') || str_contains($norm, 'assetname') || str_contains($norm, 'name') || str_contains($norm, 'thietbi')) {
                        $indices['name'] = $index;
                    } elseif (str_contains($norm, 'bophan') || str_contains($norm, 'phongban') || str_contains($norm, 'department') || str_contains($norm, 'dept')) {
                        $indices['department'] = $index;
                    } elseif (str_contains($norm, 'daudongco') || str_contains($norm, 'nhotdongco') || str_contains($norm, 'engineoil') || str_contains($norm, '15w40')) {
                        $indices['engine_oil_cap'] = $index;
                    } elseif (str_contains($norm, 'nhotthuyluc') || str_contains($norm, 'dauthuyluc') || str_contains($norm, 'hydraulicoil') || str_contains($norm, 'aw68')) {
                        $indices['hydraulic_oil_cap'] = $index;
                    } elseif (str_contains($norm, 'locnhotdongco') || str_contains($norm, 'locdau') || str_contains($norm, 'locnhot') || str_contains($norm, 'engineoilfilter')) {
                        $indices['engine_oil_filter'] = $index;
                    } elseif (str_contains($norm, 'locthuyluc') || str_contains($norm, 'hydraulicfilter') || str_contains($norm, 'lochut') || str_contains($norm, 'lochoi')) {
                        $indices['hydraulic_filter'] = $index;
                    } elseif (str_contains($norm, 'locgio') || str_contains($norm, 'airfilter')) {
                        $indices['air_filter'] = $index;
                    } elseif (str_contains($norm, 'chuky') || str_contains($norm, 'cycle') || str_contains($norm, 'baoduong') || str_contains($norm, 'period')) {
                        $indices['maintenance_cycle'] = $index;
                    }
                }

                // Nếu không khớp tiêu đề nào thì fallback theo vị trí cột mặc định
                if ($indices['asset_code'] === null) $indices['asset_code'] = 0;
                if ($indices['name'] === null) $indices['name'] = 1;
                if ($indices['department'] === null) $indices['department'] = 2;
                if ($indices['engine_oil_cap'] === null) $indices['engine_oil_cap'] = 3;
                if ($indices['hydraulic_oil_cap'] === null) $indices['hydraulic_oil_cap'] = 4;
                if ($indices['engine_oil_filter'] === null) $indices['engine_oil_filter'] = 5;
                if ($indices['hydraulic_filter'] === null) $indices['hydraulic_filter'] = 6;
                if ($indices['air_filter'] === null) $indices['air_filter'] = 7;
                if ($indices['maintenance_cycle'] === null) $indices['maintenance_cycle'] = 8;
                
                $importedCount = 0;
                foreach ($rows as $row) {
                    $assetCode = isset($row[$indices['asset_code']]) ? trim($row[$indices['asset_code']]) : '';
                    if (empty($assetCode)) continue;
                    
                    $name = isset($row[$indices['name']]) ? trim($row[$indices['name']]) : '';
                    $dept = isset($row[$indices['department']]) ? trim($row[$indices['department']]) : '';
                    $engOil = isset($row[$indices['engine_oil_cap']]) ? trim($row[$indices['engine_oil_cap']]) : '';
                    $hydOil = isset($row[$indices['hydraulic_oil_cap']]) ? trim($row[$indices['hydraulic_oil_cap']]) : '';
                    $engFilter = isset($row[$indices['engine_oil_filter']]) ? trim($row[$indices['engine_oil_filter']]) : '';
                    $hydFilter = isset($row[$indices['hydraulic_filter']]) ? trim($row[$indices['hydraulic_filter']]) : '';
                    $airFilter = isset($row[$indices['air_filter']]) ? trim($row[$indices['air_filter']]) : '';
                    $cycle = isset($row[$indices['maintenance_cycle']]) ? trim($row[$indices['maintenance_cycle']]) : '';

                    Asset::updateOrCreate(
                        ['asset_code' => $assetCode],
                        [
                            'name' => $name ?: 'Thiết bị ' . $assetCode,
                            'department' => $dept ?: null,
                            'engine_oil_cap' => $engOil ?: null,
                            'hydraulic_oil_cap' => $hydOil ?: null,
                            'engine_oil_filter' => $engFilter ?: null,
                            'hydraulic_filter' => $hydFilter ?: null,
                            'air_filter' => $airFilter ?: null,
                            'maintenance_cycle' => $cycle ?: null,
                            'status' => 'active'
                        ]
                    );
                    $importedCount++;
                }
                
                $this->initFields();
                $this->showImportModal = false;
                $this->excelFile = null;
                session()->flash('message', "Nhập dữ liệu thành công! Đã đồng bộ và xử lý {$importedCount} thiết bị.");
            } else {
                session()->flash('error', 'Tệp tin trống hoặc không hợp lệ.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra khi nhập dữ liệu: ' . $e->getMessage());
        }
    }

    public function saveOcrData($rows)
    {
        $savedCount = 0;
        foreach ($rows as $row) {
            if (empty($row['asset_code'])) continue;
            
            Asset::updateOrCreate(
                ['asset_code' => trim($row['asset_code'])],
                [
                    'name' => trim($row['name'] ?: 'Thiết bị mới'),
                    'department' => trim($row['department'] ?: 'Cơ giới'),
                    'engine_oil_cap' => trim($row['engine_oil_cap'] ?: null),
                    'hydraulic_oil_cap' => trim($row['hydraulic_oil_cap'] ?: null),
                    'engine_oil_filter' => trim($row['engine_oil_filter'] ?: null),
                    'hydraulic_filter' => trim($row['hydraulic_filter'] ?: null),
                    'air_filter' => trim($row['air_filter'] ?: null),
                    'maintenance_cycle' => trim($row['maintenance_cycle'] ?: null),
                    'status' => 'active'
                ]
            );
            $savedCount++;
        }

        $this->initFields();
        $this->showImportModal = false;
        session()->flash('message', "Đồng bộ nhận diện ảnh thành công! Đã lưu {$savedCount} thiết bị.");
    }

    public function render()
    {
        $assetsQuery = Asset::query();

        if ($this->search) {
            $assetsQuery->where(function($q) {
                $q->where('asset_code', 'like', '%'.$this->search.'%')
                  ->orWhere('name', 'like', '%'.$this->search.'%')
                  ->orWhere('department', 'like', '%'.$this->search.'%');
            });
        }

        $assets = $assetsQuery->with(['maintenanceBoms.items.product'])
                              ->orderBy('asset_code', 'asc')
                              ->paginate(15);

        // Chi lay dung 3 cot ma the <select> can
        $availableProducts = Product::orderBy('name')->get(['id', 'code', 'name'])->toArray();

        return view('livewire.warehouse.asset.asset-bom-manager', compact('assets', 'availableProducts'));
    }
}
