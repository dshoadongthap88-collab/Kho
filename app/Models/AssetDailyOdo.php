<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetDailyOdo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reading_date',
        'asset_id',
        'old_odo',
        'new_odo',
        'odo_diff',
        'old_hours',
        'new_hours',
        'hours_diff',
        'updated_by',
        'note',
    ];

    protected $casts = [
        'reading_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
