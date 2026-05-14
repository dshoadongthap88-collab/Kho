<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code', 'asset_id', 'maintenance_date', 'type',
        'description', 'status', 'created_by'
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
