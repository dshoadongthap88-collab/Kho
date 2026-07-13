<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'house_id',
        'rule_code',
        'name',
        'machine_type',
        'category',
        'cycle_km',
        'cycle_hours',
        'cycle_months',
        'content',
        'material_needed',
        'estimated_time',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'material_needed' => 'array',
    ];
}
