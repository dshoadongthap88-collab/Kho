<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToHouse;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, BelongsToHouse;

    protected $connection = 'mysql';

    protected $fillable = [
        'house_id',
        'name',
        'address',
        'phone',
        'contact_person',
        'email',
        'type',
        'status',
        'department',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}