<?php

declare(strict_types=1);

namespace App\Traits;

use App\Support\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Ошибки валидатора сайта в ответе API
 *
 * Проверки аккаунта живут в сервисах и написаны на валидаторе сайта,
 * а клиент API ждёт обычный 422 с полем errors
 */
trait HandlesApiValidation
{
    protected function throwIfInvalid(Validator $validator): void
    {
        if (! $validator->isValid()) {
            throw ValidationException::withMessages($validator->getErrors());
        }
    }
}
