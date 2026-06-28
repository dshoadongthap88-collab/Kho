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
        'shifts_count',
        'asset_id',
        'old_odo',
        'new_odo',
        'odo_diff',
        'old_hours',
        'new_hours',
        'hours_diff',
        'operator',
        'phone',
        'status',
        'is_synced',
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

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
