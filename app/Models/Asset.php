<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes, BelongsToHouse;

    protected $fillable = [
        'equipment_code',
        'asset_code',
        'license_plate',
        'driver_name',
        'phone_number',
        'name',
        'department',
        'machine_type',
        'manager',
        'warranty_status',
        'model',
        'serial_number',
        'manufacturer',
        'installation_date',
        'status',
        'lifetime_odo',
        'lifetime_hours',
        'cycle_odo',
        'cycle_hours',
        'current_hours',
        'current_odo',
        'hours_per_shift',
        'maintenance_cycle_hours',
        'maintenance_cycle_odo',
        'last_maintenance_hours',
        'last_maintenance_odo',
        'bom_details',
        'management_unit',
    ];

    /**
     * Cột số NOT NULL của bảng assets, kèm giá trị mặc định trong CSDL.
     * Ô để trống trên form gửi lên chuỗi rỗng; MySQL chế độ strict từ chối ''
     * cho cột decimal/int (SQLSTATE[22007] Incorrect decimal value).
     */
    private const NUMERIC_DEFAULTS = [
        'lifetime_odo'           => 0,
        'lifetime_hours'         => 0,
        'cycle_odo'              => 0,
        'cycle_hours'            => 0,
        'current_odo'            => 0,
        'current_hours'          => 0,
        'last_maintenance_odo'   => 0,
        'last_maintenance_hours' => 0,
        'hours_per_shift'        => 8,
    ];

    /** Cột số cho phép NULL — chuỗi rỗng cũng bị từ chối, phải quy về null */
    private const NULLABLE_NUMERIC = [
        'maintenance_cycle_hours',
        'maintenance_cycle_odo',
        'house_id',
    ];

    /**
     * Chặn ngay tại model để mọi nơi ghi vào assets đều an toàn, thay vì phải
     * nhớ xử lý ở từng form và từng luồng import.
     */
    public function setAttribute($key, $value)
    {
        if ($value === '' || $value === null) {
            if (array_key_exists($key, self::NUMERIC_DEFAULTS)) {
                $value = self::NUMERIC_DEFAULTS[$key];
            } elseif (in_array($key, self::NULLABLE_NUMERIC, true)) {
                $value = null;
            }
        }

        return parent::setAttribute($key, $value);
    }

    public function oilBoms()
    {
        return $this->hasMany(AssetOilBom::class);
    }

    public function maintenanceBoms()
    {
        return $this->hasMany(MaintenanceBom::class);
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