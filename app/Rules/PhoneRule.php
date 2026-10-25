<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value) || ! preg_match('#^\+?\d{8,13}$#', (string) $value)) {
            $fail(__('validator.phone'));
        }
    }
}
