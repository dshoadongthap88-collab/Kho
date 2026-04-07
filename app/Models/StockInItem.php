<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_in_id',
        'product_id',
        'batch_number',
        'expiry_date',
        'warehouse_location',
        'quantity',
    ];

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
