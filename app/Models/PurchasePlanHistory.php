<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePlanHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_plan_id',
        'old_status',
        'new_status',
        'old_quantity',
        'new_quantity',
        'notes',
        'changed_by',
    ];

    public function purchasePlan()
    {
        return $this->belongsTo(PurchasePlan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
