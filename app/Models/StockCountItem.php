<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToHouse;

class StockCountItem extends Model
{
    use BelongsToHouse;

    protected $fillable = [
        'house_id',
        'stock_count_id',
        'product_id',
        'product_code',
        'product_name',
        'unit',
        'warehouse_location',
        'system_quantity',
        'actual_quantity',
        'difference',
        'note',
    ];

    public function stockCount()
    {
        return $this->belongsTo(StockCount::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}