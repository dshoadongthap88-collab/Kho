<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'brand',
        'box_spec',
        'carton_spec',
        'unit',
        'price',
        'min_stock',
        'category_id',
        'type',
        'status',
        'location',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function getIsExpiringSoonAttribute()
    {
        if (!$this->expiry_date) {
            return false;
        }

        return now()->addMonths(6)->greaterThanOrEqualTo($this->expiry_date);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function boms()
    {
        return $this->hasMany(Bom::class, 'product_id');
    }

    public function materials()
    {
        return $this->belongsToMany(Product::class, 'boms', 'product_id', 'material_id')
            ->withPivot('quantity', 'unit');
    }
}
