<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Rules\BangladeshPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    /**
     * Show administrator profile.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->load(['roles', 'directPermissions']);

        return view('admin.profile', compact('user'));
    }

    /**
     * Update administrator profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', new BangladeshPhone(), Rule::unique('users', 'phone')->ignore($user->id)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $normalizedPhone = BangladeshPhone::normalize($validated['phone']);

        $user->name = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));
        $user->phone = $normalizedPhone;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'admin.profile_updated',
            'description' => "Administrator updated profile details",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.profile')->with('success', 'Your administrator profile has been updated.');
    }
}
