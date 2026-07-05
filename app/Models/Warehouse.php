<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, BelongsToHouse;

    protected $fillable = [
        'code',
        'name',
        'address',
        'manager_name',
        'phone',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transfersFrom()
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    public function transfersTo()
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
}
