<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenancePlan extends Model
{
    use HasFactory, SoftDeletes;

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
    ];

    protected $casts = [
        'expected_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
