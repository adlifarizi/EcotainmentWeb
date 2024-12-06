<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Periksa apakah user telah login dan role-nya adalah 'admin'
        if (auth('sanctum')->check() && auth('sanctum')->user()->role === 'admin') {
            return $next($request);
        }

        // Jika bukan admin, kembalikan response dengan status 403
        return response()->json([
            'success' => false,
            'message' => 'Access denied. Only admins are allowed.',
        ], 403);
    }
}
