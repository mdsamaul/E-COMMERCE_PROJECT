<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('admin.login')->with('error', 'Please log in to access the admin area.');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('error', 'Your administrator account has been deactivated. Please contact support.');
        }

        if (!$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized: Admin access required.'], 403);
            }
            abort(403, 'Unauthorized access: Admin privileges required.');
        }

        return $next($request);
    }
}
