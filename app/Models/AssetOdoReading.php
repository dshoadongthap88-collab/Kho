<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;

class AssetOdoReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'product_id',
        'reading_date',
        'current_hours',
        'operator',
        'status',
        'notes',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'current_hours' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
