<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory, BelongsToHouse;

    protected $fillable = [
        'transfer_code',
        'transfer_date',
        'from_warehouse_id',
        'to_warehouse_id',
        'from_project_id',
        'to_project_id',
        'sender_phone',
        'receiver_id',
        'receiver_phone',
        'status',
        'note',
        'reject_reason',
        'created_by',
        'confirmed_by',
        'confirmed_at',
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function fromProject()
    {
        return $this->belongsTo(Project::class, 'from_project_id');
    }

    public function toProject()
    {
        return $this->belongsTo(Project::class, 'to_project_id');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}