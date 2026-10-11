<?php

namespace App\Services;

use App\Models\User;
use App\Rules\BangladeshPhone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Authenticate a user by either email or Bangladesh mobile number.
     *
     * @param string $login Email or phone number
     * @param string $password User password
     * @param bool $remember Remember me flag
     * @param string|null $requiredRole Optional role guard ('customer' or 'admin')
     * @return array{success: bool, message: string, user: User|null}
     */
    public function authenticate(string $login, string $password, bool $remember = false, ?string $requiredRole = null): array
    {
        $login = trim($login);
        $user = null;

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', strtolower($login))->first();
        } else {
            $normalizedPhone = BangladeshPhone::normalize($login);
            $user = User::where('phone', $normalizedPhone)->first();
        }

        if (!$user) {
            return [
                'success' => false,
                'message' => 'No account found with this email or mobile number.',
                'user' => null,
            ];
        }

        if (!$user->is_active) {
            return [
                'success' => false,
                'message' => 'Your account is deactivated. Please contact customer support.',
                'user' => null,
            ];
        }

        if ($requiredRole !== null) {
            if ($requiredRole === 'superadmin' && !$user->isSuperAdmin()) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized: Super Administrator privileges required.',
                    'user' => null,
                ];
            }

            if ($requiredRole === 'admin' && !$user->isAdmin()) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized access for this account type.',
                    'user' => null,
                ];
            }

            if ($requiredRole === 'customer' && !$user->isCustomer()) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized access for this account type.',
                    'user' => null,
                ];
            }
        }

        if (!Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => 'The provided password does not match our records.',
                'user' => null,
            ];
        }

        Auth::login($user, $remember);

        return [
            'success' => true,
            'message' => 'Login successful.',
            'user' => $user,
        ];
    }

    /**
     * Register a new customer.
     */
    public function registerCustomer(array $data): User
    {
        $normalizedPhone = BangladeshPhone::normalize($data['phone'] ?? null);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => $normalizedPhone,
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'is_active' => true,
        ]);

        Auth::login($user);

        return $user;
    }

    /**
     * Automatically find or provision a customer account during product purchase.
     * Requirement: If a guest customer orders with a phone number, an account is auto-created
     * so they can later log in using their phone number.
     *
     * @return array{user: User, was_created: bool, initial_password: string|null}
     */
    public function findOrCreateFromPurchase(string $phone, string $name, ?string $email = null): array
    {
        $normalizedPhone = BangladeshPhone::normalize($phone);

        // 1. Check if user already exists by phone
        $existingUser = User::where('phone', $normalizedPhone)->first();
        if ($existingUser) {
            return [
                'user' => $existingUser,
                'was_created' => false,
                'initial_password' => null,
            ];
        }

        // 2. Determine a unique email
        $cleanEmail = $email ? strtolower(trim($email)) : null;
        if ($cleanEmail && User::where('email', $cleanEmail)->exists()) {
            // If the entered email belongs to another profile, fallback to phone-based email
            $cleanEmail = $normalizedPhone . '@shopbd.local';
        } elseif (!$cleanEmail) {
            $cleanEmail = $normalizedPhone . '@shopbd.local';
        }

        // 3. Create initial password (e.g. 8-character secure string or last 6 digits of phone for easy access)
        $initialPassword = 'BD' . substr($normalizedPhone, -6);

        $newUser = User::create([
            'name' => trim($name),
            'email' => $cleanEmail,
            'phone' => $normalizedPhone,
            'password' => Hash::make($initialPassword),
            'role' => 'customer',
            'is_active' => true,
        ]);

        return [
            'user' => $newUser,
            'was_created' => true,
            'initial_password' => $initialPassword,
        ];
    }
}
