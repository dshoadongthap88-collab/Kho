<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetOilBom extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id', 'product_id', 'bom_code', 'standard_qty',
        'min_qty', 'max_qty', 'replace_cycle_hour', 'replace_cycle_day',
        'warning_before_day', 'vendor', 'note'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
