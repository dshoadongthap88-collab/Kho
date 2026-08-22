<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Scopes\ProjectScope;
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
        'project_id',
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

    /**
     * The "booted" method of the model.
     * 
     * Áp dụng ProjectScope để tự động lọc users theo dự án.
     * Scope tự bảo vệ nếu cột project_id chưa tồn tại (chưa migrate).
     */
    protected static function booted(): void
    {
        // Cache kết quả check schema để tránh query DB lặp lại mỗi request
        static $hasProjectId = null;
        if ($hasProjectId === null) {
            try {
                $hasProjectId = \Illuminate\Support\Facades\Schema::hasColumn('users', 'project_id');
            } catch (\Exception $e) {
                $hasProjectId = false;
            }
        }

        if ($hasProjectId) {
            static::addGlobalScope(new ProjectScope());
        }
    }

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

    /**
     * Quan hệ với Project/Dự án
     */
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    /**
     * Kiểm tra user có thuộc dự án cụ thể không
     */
    public function belongsToProject($projectId): bool
    {
        // Admin có quyền truy cập tất cả dự án
        if ($this->role === 'admin') {
            return true;
        }
        
        return $this->project_id == $projectId;
    }

    /**
     * Lấy danh sách dự án mà user có quyền truy cập
     * Admin: tất cả dự án
     * User thường: chỉ dự án của mình
     */
    public function getAccessibleProjects()
    {
        if ($this->role === 'admin') {
            return \App\Models\Project::all();
        }
        
        return \App\Models\Project::where('id', $this->project_id)->get();
    }

    /**
     * Kiểm tra có thể xem thông tin user khác không
     * Chỉ xem được user cùng dự án hoặc admin xem tất cả
     */
    public function canViewUser(User $otherUser): bool
    {
        if ($this->role === 'admin') {
            return true;
        }
        
        return $this->project_id === $otherUser->project_id;
    }
}