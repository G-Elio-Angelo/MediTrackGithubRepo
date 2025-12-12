<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        $user = Auth::user();
        if (!($user->role === 'admin' || (property_exists($user, 'is_admin') && $user->is_admin))) {
            abort(403, 'Unauthorized: Admins only.');
        }

        // Validate a per-login token to prevent stale sessions/tabs from keeping admin access
        $sessionToken = session('login_token');
        $cacheToken = Cache::get("user:{$user->user_id}:login_token");
        if ($cacheToken && $sessionToken !== $cacheToken) {
            // Invalidate current session and force re-login
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->with('error', 'Your session has changed. Please log in again.');
        }
        return $next($request);
    }
}
