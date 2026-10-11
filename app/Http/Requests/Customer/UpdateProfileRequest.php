<?php

namespace App\Http\Requests\Customer;

use App\Rules\BangladeshPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:191', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required', 'string', new BangladeshPhone(), Rule::unique('users', 'phone')->ignore($userId)],
        ];
    }
}
