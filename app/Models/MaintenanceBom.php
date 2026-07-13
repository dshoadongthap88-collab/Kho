<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;

class MaintenanceBom extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'bom_code',
        'asset_id',
        'maintenance_level',
        'cycle',
        'created_by',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function items()
    {
        return $this->hasMany(MaintenanceBomItem::class, 'maintenance_bom_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
