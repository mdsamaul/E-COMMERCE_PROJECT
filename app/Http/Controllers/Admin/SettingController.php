<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'ShopBD'),
            'store_tagline' => Setting::get('store_tagline', 'Best Online Shopping Experience in Bangladesh'),
            'store_phone' => Setting::get('store_phone', '01700000000'),
            'store_email' => Setting::get('store_email', 'support@shopbd.com'),
            'store_address' => Setting::get('store_address', 'Motijheel Commercial Area, Dhaka-1000, Bangladesh'),
            'inside_dhaka_delivery_fee' => Setting::get('inside_dhaka_delivery_fee', 60.00),
            'outside_dhaka_delivery_fee' => Setting::get('outside_dhaka_delivery_fee', 120.00),
            'free_shipping_threshold' => Setting::get('free_shipping_threshold', 2500.00),
            'currency_symbol' => Setting::get('currency_symbol', '৳'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Setting::set('store_name', $validated['store_name'], 'general', 'string', 'Store name');
        Setting::set('store_tagline', $validated['store_tagline'] ?? '', 'general', 'string', 'Store tagline');
        Setting::set('store_phone', $validated['store_phone'], 'general', 'string', 'Support phone');
        Setting::set('store_email', $validated['store_email'], 'general', 'string', 'Support email');
        Setting::set('store_address', $validated['store_address'], 'general', 'string', 'Store office address');

        Setting::set('inside_dhaka_delivery_fee', $validated['inside_dhaka_delivery_fee'], 'shipping', 'number', 'Inside Dhaka delivery fee');
        Setting::set('outside_dhaka_delivery_fee', $validated['outside_dhaka_delivery_fee'], 'shipping', 'number', 'Outside Dhaka delivery fee');
        Setting::set('free_shipping_threshold', $validated['free_shipping_threshold'] ?? 0, 'shipping', 'number', 'Free shipping threshold');

        Setting::set('currency_symbol', $validated['currency_symbol'], 'localization', 'string', 'Currency symbol');

        return redirect()->route('admin.settings.index')->with('success', 'Website and delivery settings saved successfully.');
    }
}
