<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'STT',
            'Mã Vật Tư',
            'Tên Vật Tư',
            'Phân Loại',
            'Danh Mục',
            'Hãng SX',
            'Mã NCC',
            'Số Lượng Tồn',
            'Tồn Tối Thiểu',
            'Tồn Tối Đa',
            'Vị Trí',
            'Tình Trạng',
            'Ghi Chú'
        ];
    }

    public function map($product): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $product->code,
            $product->name,
            $product->type === 'product_produced' ? 'Thành phẩm (SX)' : ($product->type === 'product_purchased' ? 'Thành phẩm (Mua)' : 'Vật tư'),
            $product->category ? $product->category->name : '',
            $product->brand,
            $product->batch_number,
            $product->inventory ? $product->inventory->quantity : 0,
            $product->min_stock,
            $product->max_stock,
            $product->inventory ? $product->inventory->warehouse_location : $product->location,
            $product->status === 'active' ? 'Đang kinh doanh' : 'Ngừng kinh doanh',
            $product->description,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
