<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BangladeshPhone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('The :attribute must be a valid phone number.');
            return;
        }

        $cleaned = static::normalize($value);

        // Bangladeshi phone number pattern: 01[3-9] followed by 8 digits
        if (!preg_match('/^01[3-9]\d{8}$/', $cleaned)) {
            $fail('The :attribute must be a valid 11-digit Bangladesh mobile number (e.g., 017XXXXXXXX).');
        }
    }

    /**
     * Normalize any Bangladeshi phone format (+8801..., 8801..., 01...) to 11-digit local format (01...).
     */
    public static function normalize(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remove spaces, hyphens, and brackets
        $cleaned = preg_replace('/[\s\-\(\)]+/', '', $phone);

        // Remove +88 or 88 country code if present
        if (str_starts_with($cleaned, '+880')) {
            $cleaned = substr($cleaned, 3);
        } elseif (str_starts_with($cleaned, '880')) {
            $cleaned = substr($cleaned, 2);
        }

        return $cleaned;
    }
}
