<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\BlackList;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * Значение не в чёрном списке сайта
 *
 * Тип domain проверяет домен адреса почты
 */
readonly class NotBlacklistedRule implements ValidationRule
{
    /**
     * @param 'login'|'email'|'domain' $type
     */
    public function __construct(private string $type)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = strtolower((string) $value);

        if ($this->type === 'domain') {
            $value = Str::after($value, '@');
        }

        if (BlackList::isBlacklisted($this->type, $value)) {
            $fail(__('users.' . $this->type . '_is_blacklisted'));
        }
    }
}
