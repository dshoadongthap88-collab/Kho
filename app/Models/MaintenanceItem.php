<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;

class MaintenanceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'maintenance_ticket_id', 'asset_oil_bom_id', 'suggested_qty',
        'actual_qty', 'unit_price'
    ];

    public function ticket()
    {
        return $this->belongsTo(MaintenanceTicket::class, 'maintenance_ticket_id');
    }

    public function bom()
    {
        return $this->belongsTo(AssetOilBom::class, 'asset_oil_bom_id');
    }
}
