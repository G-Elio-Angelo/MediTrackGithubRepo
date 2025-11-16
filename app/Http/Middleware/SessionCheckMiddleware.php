<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Ensures a valid user session exists for web routes. If not authenticated,
 * redirects to the login page. Excludes public auth routes (login, register, otp).
 */
class SessionCheckMiddleware
{
    public function handle($request, Closure $next)
    {
        // Allow unauthenticated access to auth routes and public assets
        $except = [
            'login',
            'login/*',
            'register',
            'register/*',
            'otp',
            'otp/*',
            'logout',
            '_debugbar/*'
        ];

        $path = ltrim($request->path(), '/');

        foreach ($except as $pattern) {
            $regex = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#';
            if (preg_match($regex, $path)) {
                return $next($request);
            }
        }

        // If the user is not authenticated, redirect to login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
