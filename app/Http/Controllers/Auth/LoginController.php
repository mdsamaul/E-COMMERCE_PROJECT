<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        $result = $this->authService->authenticate(
            login: $request->input('login'),
            password: $request->input('password'),
            remember: $request->boolean('remember'),
            requiredRole: null // Customers can login here
        );

        if (!$result['success']) {
            $request->hitRateLimit();

            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => $result['message']]);
        }

        $request->clearRateLimit();
        $request->session()->regenerate();

        if ($result['user']->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('customer.profile'))->with('success', 'Welcome back, ' . $result['user']->name . '!');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been successfully logged out.');
    }
}
