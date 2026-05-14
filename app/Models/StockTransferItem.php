<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_transfer_id',
        'product_code',
        'quantity'
    ];

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }
}
