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
        'receiver_contact',
        'asset_code',
        'type',
        'status',
        'note',
        'created_by',
        'project_name',
        'document_number',
        'license_plate',
        'km_number',
        'operating_hours',
        'device_name',
        'department',
        'warehouse_keeper',
        'supervisor_qltb',
        'supervisor_ca',
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
