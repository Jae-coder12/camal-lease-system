<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!$request->user()) return redirect()->route('login');
        abort_unless($request->user()->role === $role, 403, 'You are not allowed to access this page.');
        return $next($request);
    }
}
