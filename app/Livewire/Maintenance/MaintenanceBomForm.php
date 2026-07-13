<?php

namespace App\Livewire\Maintenance;

use App\Models\Asset;
use App\Models\Product;
use App\Models\MaintenanceBom;
use App\Models\MaintenanceBomItem;
use Livewire\Component;
use Illuminate\Support\Str;

class MaintenanceBomForm extends Component
{
    public $bomId = null;
    public $bom_code = '';
    public $asset_id = '';
    public $maintenance_level = '';
    public $cycle = '';
    
    // Asset details
    public $asset_name = '';
    public $asset_model = '';
    public $asset_manufacturer = '';

    public $items = [];

    protected $rules = [
        'bom_code' => 'required|unique:maintenance_boms,bom_code',
        'asset_id' => 'required|exists:assets,id',
        'maintenance_level' => 'required|string',
        'cycle' => 'required|integer|min:0',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|numeric|min:0',
        'items.*.backup_quantity' => 'required|numeric|min:0',
    ];

    public function mount($bomId = null)
    {
        if ($bomId) {
            $bom = MaintenanceBom::with('items', 'asset')->findOrFail($bomId);
            $this->bomId = $bom->id;
            $this->bom_code = $bom->bom_code;
            $this->asset_id = $bom->asset_id;
            $this->maintenance_level = $bom->maintenance_level;
            $this->cycle = $bom->cycle;

            if ($bom->asset) {
                $this->asset_name = $bom->asset->name;
                $this->asset_model = $bom->asset->model;
                $this->asset_manufacturer = $bom->asset->manufacturer;
            }

            foreach ($bom->items as $item) {
                $product = $item->product;
                $this->items[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_code' => $product->code ?? '',
                    'product_name' => $product->name ?? '',
                    'product_desc' => $product->description ?? '',
                    'product_unit' => $product->unit ?? '',
                    'quantity' => $item->quantity,
                    'backup_quantity' => $item->backup_quantity,
                    'note' => $item->note,
                ];
            }
        } else {
            $this->bom_code = 'MBOM-' . strtoupper(Str::random(8));
            $this->addItem();
        }
    }

    public function updatedAssetId($value)
    {
        if ($value) {
            $asset = Asset::find($value);
            if ($asset) {
                $this->asset_name = $asset->name;
                $this->asset_model = $asset->model;
                $this->asset_manufacturer = $asset->manufacturer;
            }
        } else {
            $this->asset_name = '';
            $this->asset_model = '';
            $this->asset_manufacturer = '';
        }
    }

    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) == 2 && $parts[1] === 'product_id') {
            $index = $parts[0];
            $product = Product::find($value);
            if ($product) {
                $this->items[$index]['product_code'] = $product->code;
                $this->items[$index]['product_name'] = $product->name;
                $this->items[$index]['product_desc'] = $product->description;
                $this->items[$index]['product_unit'] = $product->unit;
            }
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'product_code' => '',
            'product_name' => '',
            'product_desc' => '',
            'product_unit' => '',
            'quantity' => 1,
            'backup_quantity' => 0,
            'note' => '',
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save()
    {
        if ($this->bomId) {
            $this->rules['bom_code'] = 'required|unique:maintenance_boms,bom_code,' . $this->bomId;
        }

        $this->validate();

        $bom = MaintenanceBom::updateOrCreate(
            ['id' => $this->bomId],
            [
                'bom_code' => $this->bom_code,
                'asset_id' => $this->asset_id,
                'maintenance_level' => $this->maintenance_level,
                'cycle' => $this->cycle,
                'created_by' => auth()->id(),
            ]
        );

        $bom->items()->delete();

        foreach ($this->items as $item) {
            MaintenanceBomItem::create([
                'maintenance_bom_id' => $bom->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'backup_quantity' => $item['backup_quantity'],
                'note' => $item['note'],
            ]);
        }

        session()->flash('message', 'Lưu BOM thành công.');
        return redirect()->route('maintenance-boms.index');
    }

    public function render()
    {
        return view('components.maintenance.maintenance-bom-form', [
            'assets' => Asset::all(),
            'products' => Product::all(),
        ]);
    }
}
