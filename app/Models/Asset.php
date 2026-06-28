<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_code',
        'name',
        'department',
        'machine_type',
        'model',
        'serial_number',
        'manufacturer',
        'installation_date',
        'status',
        'lifetime_odo',
        'lifetime_hours',
        'cycle_odo',
        'cycle_hours',
        'hours_per_shift',
        'maintenance_cycle_hours',
        'maintenance_cycle_odo',
        'last_maintenance_hours',
        'last_maintenance_odo',
    ];

    public function oilBoms()
    {
        return $this->hasMany(AssetOilBom::class);
    }

    public function meterReadings()
    {
        return $this->hasMany(AssetMeterReading::class);
    }

    public function maintenanceTickets()
    {
        return $this->hasMany(MaintenanceTicket::class);
    }
}