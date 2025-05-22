<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('#^\+?\d{8,13}$#', $value)) {
            $fail(__('validator.phone'));
        }
    }
}
