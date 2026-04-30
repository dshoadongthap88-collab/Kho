<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    protected $fillable = [
        'code',
        'customer_name',
        'receiver_name',
        'asset_code',
        'type',
        'status',
        'note',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->morphMany(InventoryTransaction::class, 'reference');
    }

    public function items()
    {
        return $this->hasMany(StockOutItem::class);
    }
}
