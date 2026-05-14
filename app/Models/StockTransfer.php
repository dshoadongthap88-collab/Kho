<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_code',
        'transfer_date',
        'from_house',
        'to_house',
        'status',
        'note',
        'created_by'
    ];

    protected $casts = [
        'transfer_date' => 'date'
    ];

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
