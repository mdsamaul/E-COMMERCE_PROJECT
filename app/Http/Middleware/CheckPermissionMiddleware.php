<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('admin.login')->with('error', 'Please log in to continue.');
        }

        $user = Auth::user();

        // Super Admin bypasses all specific permission requirements
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Active admin check with permission
        if (!$user->isAdmin() || !$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden: You do not have permission to access this resource.'], 403);
            }

            abort(403, "Access Denied: Missing required permission [{$permission}].");
        }

        return $next($request);
    }
}
