<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use App\Services\CaptchaService;
use Illuminate\Validation\Validator;

/**
 * Капча формы: сайт хранит ответ в сессии, API — в кеше по ключу задания
 */
trait ChecksCaptcha
{
    protected function checkCaptcha(Validator $validator): void
    {
        $passed = $this->is('api/*')
            ? app(CaptchaService::class)->verify($this)
            : captchaVerify();

        if (! $passed) {
            $validator->errors()->add('protect', __('validator.captcha'));
        }
    }
}
