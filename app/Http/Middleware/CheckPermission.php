<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        // Admin mặc định có toàn quyền thao tác (có thể chỉnh sửa nếu cần)
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Kiểm tra quyền
        $permissions = $user->permissions ?? [];
        if (in_array($permission, $permissions)) {
            return $next($request);
        }

        return redirect()->back()->with('error', 'Bạn chưa được cấp quyền truy cập. Vui lòng liên hệ quản trị viên.');
    }
}
