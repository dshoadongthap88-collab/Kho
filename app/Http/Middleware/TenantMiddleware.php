<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for unauthenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $currentHouse = session('current_house');

        if (!$currentHouse) {
            // Redirect to select house if not selected
            return redirect()->route('tenant.select-house');
        }

        // Hệ thống sử dụng Shared Database, không switch connection nữa
        // Chỉ cần đảm bảo Global Scope (BelongsToHouse) hoạt động là đủ phân tách dữ liệu

        return $next($request);
    }
}
