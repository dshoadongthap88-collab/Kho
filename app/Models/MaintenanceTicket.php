<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'ticket_code', 'asset_id', 'maintenance_rule_id', 'maintenance_date', 'type',
        'maintenance_odo', 'description', 'materials_used', 'staff_name',
        'inspector', 'result', 'image_before', 'image_after', 'notes',
        'status', 'created_by', 'replaced_materials', 'total_cost'
    ];

    protected $casts = [
        'replaced_materials' => 'array',
        'maintenance_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function items()
    {
        return $this->hasMany(MaintenanceItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
