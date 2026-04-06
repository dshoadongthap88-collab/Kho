<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    protected $fillable = [
        'product_id',
        'material_id',
        'quantity',
        'unit',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function material()
    {
        return $this->belongsTo(Product::class, 'material_id');
    }
}
