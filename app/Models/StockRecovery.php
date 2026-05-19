<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRecovery extends Model
{
    use HasFactory;

    protected $fillable = [
        'recovery_number',
        'stock_out_id',
        'product_id',
        'quantity',
        'unit',
        'recovery_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'recovery_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function stockOut()
    {
        return $this->belongsTo(StockOut::class, 'stock_out_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDateRange($query, $dateFrom, $dateTo)
    {
        return $query->whereBetween('recovery_date', [$dateFrom, $dateTo]);
    }
}