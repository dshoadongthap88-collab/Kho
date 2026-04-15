<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryReport extends Model
{
    protected $fillable = [
        'stock_out_id', 'customer_name', 'status', 'payment_status', 
        'total_amount', 'paid_amount', 'due_date', 'photo_path', 'notes', 'delivered_at'
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class);
    }
}
