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

        // Configure the database for the current house
        $dbName = 'laravel'; // Default for house 1
        if ($currentHouse > 1) {
            $dbName = 'laravel_' . $currentHouse;
        }

        Config::set('database.connections.tenant.database', $dbName);
        DB::purge('tenant');
        
        // Set tenant as default connection so all models use it automatically
        Config::set('database.default', 'tenant');

        return $next($request);
    }
}
