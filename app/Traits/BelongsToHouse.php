<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToHouse
{
    public static function bootBelongsToHouse()
    {
        static::addGlobalScope('house', function (Builder $builder) {
            $houseId = session('current_house') ?? (auth()->user()?->current_house_id);
            
            if ($houseId) {
                $builder->where($builder->getModel()->getTable() . '.house_id', $houseId);
            }
        });

        static::creating(function ($model) {
            $houseId = session('current_house') ?? (auth()->user()?->current_house_id);
            if ($houseId && !$model->house_id) {
                $model->house_id = $houseId;
            }
        });
    }

    public function house()
    {
        return $this->belongsTo(\App\Models\House::class);
    }
}
