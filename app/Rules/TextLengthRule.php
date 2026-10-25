<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Длина текста без HTML-тегов
 *
 * Для полей редактора: min/max у Laravel считают разметку вместе с текстом.
 * Пустое значение правило пропускает — обязательность задаёт required
 */
readonly class TextLengthRule implements ValidationRule
{
    public function __construct(
        private int $min,
        private int $max,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_scalar($value)) {
            $fail(__('validator.text'));

            return;
        }

        $length = mb_strlen(strip_tags((string) $value), 'utf-8');

        if ($length < $this->min) {
            $fail(__('validator.length_min', ['length' => $this->min]));
        } elseif ($length > $this->max) {
            $fail(__('validator.length_max', ['length' => $this->max]));
        }
    }
}
