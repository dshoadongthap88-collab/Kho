<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToHouse;

class StockCount extends Model
{
    use BelongsToHouse;

    protected $fillable = [
        'house_id',
        'code',
        'status',
        'type',
        'note',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function transactions()
    {
        return $this->morphMany(InventoryTransaction::class, 'reference');
    }
}
