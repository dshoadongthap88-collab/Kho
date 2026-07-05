<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mysql';

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'username',
        'password',
        'role',
        'department',
        'status',
        'avatar',
        'hire_date',
        'permissions',
        'allowed_houses',
        'current_house_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
        'allowed_houses' => 'array',
    ];

    public function purchaseOrders()
    {
        return $this->hasMany(\App\Models\PurchaseOrder::class);
    }

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(\App\Models\LeaveRequest::class);
    }
}