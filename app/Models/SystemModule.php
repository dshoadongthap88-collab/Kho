<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
        'route_name',
        'label',
        'is_active',
    ];

    protected $connection = 'mysql';
}
