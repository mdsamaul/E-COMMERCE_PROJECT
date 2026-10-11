<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authService->registerCustomer($request->validated());

        $guestSessionId = $request->session()->get('cart_token') ?: $request->session()->getId();
        if ($guestSessionId) {
            app(\App\Services\CartService::class)->mergeGuestCartToUser($guestSessionId, $user);
        }

        $request->session()->regenerate();

        return redirect()->route('customer.profile')->with('success', 'Your account has been created successfully! Welcome to ShopBD, ' . $user->name . '.');
    }
}
