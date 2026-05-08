<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckActiveStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->status === 'Not Active' || Auth::user()->active == 0)) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akun Anda tidak aktif, silakan hubungi admin.',
                    'status_code' => 'inactive_user',
                ], 403);
            }

            return redirect()->route('login')->withErrors(['status' => 'Access Denied.']);
        }

        return $next($request);
    }
}
