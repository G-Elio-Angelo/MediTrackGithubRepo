<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

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
        return $next($request);
    }
}
