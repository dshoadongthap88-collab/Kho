<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHouseContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Gán house context cho user nếu chưa có
            if (empty($user->current_house_id)) {
                    // Nếu chưa có, tự động lấy house đầu tiên trong allowed_houses
                    $allowed = $user->role === 'admin'
                        ? \App\Models\Project::pluck('id')->toArray()
                        : (is_array($user->allowed_houses) ? $user->allowed_houses : []);
                    
                    if (!empty($allowed)) {
                        $user->current_house_id = $allowed[0];
                        $user->save();
                    } else {
                        // Nếu không có quyền house nào, có thể throw 403 hoặc gán house 1 (Hóc Môn)
                        $user->current_house_id = 1;
                        $user->save();
                    }
                }
            }

        return $next($request);
    }
}
