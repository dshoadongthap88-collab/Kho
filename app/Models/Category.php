<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToHouse;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use \App\Traits\BelongsToHouse;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
