<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'reserved_quantity',
        'warehouse_location',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
