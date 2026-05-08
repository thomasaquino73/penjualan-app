<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class LastUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $userId = Auth::id();

            // Durasi online (detik)
            $duration = config('session.online_duration', 15);

            $expiresAt = now()->addSeconds($duration);

            // Tandai user online di cache, otomatis expire
            Cache::put('user-is-online-'.$userId, true, $expiresAt);

            // Update last_seen di DB (opsional)
            User::where('id', $userId)->update(['last_seen' => now()]);
        }

        return $next($request);
    }
}
