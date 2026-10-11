<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\SaveAddressRequest;
use App\Http\Requests\Customer\UpdatePasswordRequest;
use App\Http\Requests\Customer\UpdateProfileRequest;
use App\Models\Address;
use App\Rules\BangladeshPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = Auth::user()->load('addresses', 'orders');
        return view('customer.profile', compact('user'));
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->update([
            'name' => trim($request->input('name')),
            'email' => strtolower(trim($request->input('email'))),
            'phone' => BangladeshPhone::normalize($request->input('phone')),
        ]);

        return back()->with('success', 'Profile information updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('success', 'Your password has been changed successfully.');
    }

    public function storeAddress(SaveAddressRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'recipient_name' => trim($request->input('recipient_name')),
            'phone' => BangladeshPhone::normalize($request->input('phone')),
            'division' => trim($request->input('division')),
            'district' => trim($request->input('district')),
            'upazila_thana' => $request->input('upazila_thana'),
            'address_line' => trim($request->input('address_line')),
            'delivery_notes' => $request->input('delivery_notes'),
            'is_default' => $isDefault || $user->addresses()->count() === 0,
        ]);

        return back()->with('success', 'Address added successfully.');
    }

    public function deleteAddress(Address $address): RedirectResponse
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Address removed successfully.');
    }
}
