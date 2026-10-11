<?php

namespace App\Http\Requests\Admin;

use App\Rules\BangladeshPhone;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:100'],
            'store_tagline' => ['nullable', 'string', 'max:255'],
            'store_phone' => ['required', 'string', new BangladeshPhone()],
            'store_email' => ['required', 'email', 'max:191'],
            'store_address' => ['required', 'string', 'max:500'],
            'inside_dhaka_delivery_fee' => ['required', 'numeric', 'min:0'],
            'outside_dhaka_delivery_fee' => ['required', 'numeric', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'numeric', 'min:0'],
            'currency_symbol' => ['required', 'string', 'max:10'],
        ];
    }
}
