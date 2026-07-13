<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceBomItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_bom_id',
        'product_id',
        'quantity',
        'backup_quantity',
        'note',
    ];

    public function maintenanceBom()
    {
        return $this->belongsTo(MaintenanceBom::class, 'maintenance_bom_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
