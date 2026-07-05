<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use \App\Traits\BelongsToHouse;
    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'transaction_date',
        'batch_number',
        'expiry_date',
        'warehouse_location',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'transaction_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
