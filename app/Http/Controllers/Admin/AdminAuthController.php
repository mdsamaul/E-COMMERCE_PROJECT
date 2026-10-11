<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $result = $this->authService->authenticate(
            login: $request->input('login'),
            password: $request->input('password'),
            remember: $request->boolean('remember'),
            requiredRole: 'admin' // Enforces administrator role on server
        );

        if (!$result['success']) {
            $request->hitRateLimit();

            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => $result['message']]);
        }

        $request->clearRateLimit();
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'))->with('success', 'Admin session authenticated.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Admin session logged out.');
    }
}
