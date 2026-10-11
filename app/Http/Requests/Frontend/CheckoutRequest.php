<?php

namespace App\Http\Requests\Frontend;

use App\Rules\BangladeshPhone;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', new BangladeshPhone()],
            'alternative_phone' => ['nullable', 'string', new BangladeshPhone()],
            'email' => ['nullable', 'email', 'max:150'],
            'division' => ['nullable', 'string', 'max:50'],
            'district' => ['required', 'string', 'max:50'],
            'delivery_address' => ['required', 'string', 'min:5', 'max:500'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'in:cod,sslcommerz,bkash'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_name.required' => 'Please enter the recipient full name.',
            'phone.required' => 'A valid Bangladesh mobile number is required for order delivery and SMS updates.',
            'district.required' => 'Please select your delivery district to compute accurate shipping charges.',
            'delivery_address.required' => 'Please enter the detailed street, road, house/apartment delivery address.',
            'payment_method.in' => 'Please select a valid payment method.',
        ];
    }
}
