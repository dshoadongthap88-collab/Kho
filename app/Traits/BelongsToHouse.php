<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToHouse
{
    public static function bootBelongsToHouse()
    {
        static::addGlobalScope('house', function (Builder $builder) {
            $user = auth()->user();
            
            if ($user && $user->role !== 'hr' && $user->current_house_id) {
                $builder->where($builder->getModel()->getTable() . '.house_id', $user->current_house_id);
            }
        });

        static::creating(function ($model) {
            $user = auth()->user();
            if ($user && $user->current_house_id && !$model->house_id) {
                $model->house_id = $user->current_house_id;
            }
        });
    }

    public function house()
    {
        return $this->belongsTo(\App\Models\House::class);
    }
}
