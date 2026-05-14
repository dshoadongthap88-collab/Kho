<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id', 'reading_date', 'reading_value', 'user_id', 'note'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
