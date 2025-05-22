<?php

namespace App\Rules;

use Closure;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validator = new EmailValidator();
        $checkEmail = $validator->isValid((string) $value, new RFCValidation());

        $checkFilter = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

        if (! $checkEmail || ! $checkFilter) {
            $fail(__('validation.email'));
        }
    }
}
