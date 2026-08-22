<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenancePlan extends Model
{
    use HasFactory, SoftDeletes, BelongsToHouse;

    protected $fillable = [
        'house_id',
        'plan_code',
        'asset_id',
        'category',
        'expected_date',
        'current_odo',
        'maintenance_odo',
        'status',
        'assigned_to',
        'maintenance_bom_id',
        'total_cost',
        'completed_at',
    ];

    protected $casts = [
        'expected_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function maintenanceBom()
    {
        return $this->belongsTo(MaintenanceBom::class);
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class);
    }
}
