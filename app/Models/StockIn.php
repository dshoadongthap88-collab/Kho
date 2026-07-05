<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use \App\Traits\BelongsToHouse;
    protected $fillable = [
        'code',
        'supplier_name',
        'type',
        'manufacturer',
        'status',
        'note',
        'created_by',
        'stock_in_date',
        'marked_received',
        'received_at',
    ];

    protected $casts = [
        'stock_in_date' => 'date',
        'marked_received' => 'boolean',
        'received_at' => 'datetime',
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
        return $this->hasMany(StockInItem::class);
    }
}