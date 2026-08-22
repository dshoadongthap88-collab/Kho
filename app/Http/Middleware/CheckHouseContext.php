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
     * QUAN TRỌNG: Middleware này KHÔNG được gọi $user->save() vì sẽ làm
     * Livewire checksum bị invalidate trên các AJAX update requests,
     * gây ra CorruptComponentPayloadException.
     *
     * Chỉ đồng bộ current_house_id vào memory (không write DB).
     * Việc persist được thực hiện bởi TenantMiddleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Gán house context vào in-memory object (không save vào DB ở đây)
            if (empty($user->current_house_id)) {
                $allowed = $user->role === 'admin'
                    ? \App\Models\Project::pluck('id')->toArray()
                    : (is_array($user->allowed_houses) ? $user->allowed_houses : []);

                $houseId = !empty($allowed) ? $allowed[0] : 1;

                // Chỉ gán vào object, KHÔNG gọi save() — tránh corrupt Livewire checksum
                $user->current_house_id = $houseId;

                // Persist vào DB chỉ khi đây là GET request thông thường (không phải Livewire AJAX)
                // Livewire AJAX: /livewire/update, headers có X-Livewire
                $isLivewireRequest = $request->hasHeader('X-Livewire')
                    || str_contains($request->path(), 'livewire/update')
                    || str_contains($request->path(), 'livewire/message');

                if (!$isLivewireRequest) {
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'current_house_id' => $houseId,
                            // Không cập nhật updated_at để tránh ảnh hưởng audit trails
                        ]);
                }
            }
        }

        return $next($request);
    }
}
