<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOutItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_out_id',
        'product_id',
        'batch_number',
        'expiry_date',
        'warehouse_location',
        'quantity',
        'unit_price',
        'vat_rate',
        'total_amount',
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
