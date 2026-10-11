<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index(): View
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('superadmin.settings.index', compact('settings'));
    }

    /**
     * Update system-wide configuration settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:100'],
            'store_phone' => ['required', 'string', 'max:20'],
            'store_email' => ['required', 'email', 'max:100'],
            'store_address' => ['required', 'string', 'max:255'],
            'inside_dhaka_delivery' => ['required', 'numeric', 'min:0'],
            'outside_dhaka_delivery' => ['required', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'maintenance_mode' => ['nullable', 'boolean'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) $value);
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'settings.updated',
            'description' => 'Updated system store and delivery settings from Super Admin center',
            'properties' => $validated,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.settings.index')->with('success', 'System settings updated successfully.');
    }
}
