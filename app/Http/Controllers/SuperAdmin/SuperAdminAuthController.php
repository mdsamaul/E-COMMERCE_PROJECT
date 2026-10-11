<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuperAdminAuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('error', 'You are logged in as a standard administrator.');
            }
        }

        return view('superadmin.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $result = $this->authService->authenticate(
            login: $request->input('login'),
            password: $request->input('password'),
            remember: $request->boolean('remember'),
            requiredRole: 'superadmin' // Strictly enforce Super Admin flag
        );

        if (!$result['success']) {
            $request->hitRateLimit();

            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => $result['message']]);
        }

        $request->clearRateLimit();
        $request->session()->regenerate();

        return redirect()->intended(route('superadmin.dashboard'))->with('success', 'Welcome to the Super Admin Control Center.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('superadmin.login')->with('success', 'Super Admin session terminated safely.');
    }
}
