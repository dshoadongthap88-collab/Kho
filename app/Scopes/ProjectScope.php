<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * ProjectScope - Global scope để tự động lọc users theo dự án
 * 
 * Scope này đảm bảo:
 * - User thường chỉ thấy users cùng dự án (same project_id)
 * - Admin thấy tất cả users
 * - Login/Auth queries không bị ảnh hưởng (skip scope khi chưa authenticated)
 */
class ProjectScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Chỉ áp dụng scope khi user đã authenticated
        if (!auth()->check()) {
            return;
        }

        $currentUser = auth()->user();

        // Admin có quyền thấy tất cả users
        if ($currentUser->role === 'admin') {
            return;
        }

        // User thường chỉ thấy users cùng dự án
        if ($currentUser->project_id) {
            $builder->where($model->getTable() . '.project_id', $currentUser->project_id);
        }
    }

    /**
     * Extend the query builder with the ability to disable the scope.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutProjectScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        $builder->macro('forProject', function (Builder $builder, $projectId) {
            return $builder->withoutGlobalScope($this)
                          ->where('project_id', $projectId);
        });

        $builder->macro('forAllProjects', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
