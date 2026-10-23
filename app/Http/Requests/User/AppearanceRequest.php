<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\Attributes\RedirectTo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

/**
 * Платная смена оформления (статус, цвет ника): одни правила на сайт и API
 *
 * Сброс в пустое значение бесплатный, но актив нужен и для него
 */
#[RedirectTo('settings#tab-appearance')]
abstract class AppearanceRequest extends FormRequest
{
    /**
     * Поле формы и колонка пользователя: status или color
     */
    abstract protected function field(): string;

    /**
     * Новое значение, пустое — null
     */
    public function value(): ?string
    {
        return $this->string($this->field())->value() ?: null;
    }

    /**
     * Цена смены
     */
    public function cost(): int
    {
        return $this->value() ? (int) setting('edit' . $this->field() . 'money') : 0;
    }

    /**
     * Списывает цену и сохраняет новое значение
     *
     * after() сверяет баланс, прочитанный в начале запроса, а параллельный запрос
     * мог его уже потратить: chargeMoney списывает, только пока денег хватает
     *
     * @throws ValidationException
     */
    public function apply(): void
    {
        $user = auth()->user() ?? abort(403);
        $field = $this->field();

        DB::transaction(function () use ($user, $field): void {
            if (! $user->chargeMoney($this->cost())) {
                throw ValidationException::withMessages([$field => __('users.' . $field . '_moneys')])
                    ->redirectTo($this->getRedirectUrl());
            }

            $user->update([$field => $this->value()]);
        });
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $field = $this->field();
                $user = auth()->user() ?? abort(403);

                if ($validator->errors()->has($field)) {
                    return;
                }

                $error = match (true) {
                    $this->value() === $user->$field                        => 'different',
                    $user->point < (int) setting('edit' . $field . 'point') => 'points',
                    $user->money < $this->cost()                            => 'moneys',
                    default                                                 => null,
                };

                if ($error) {
                    $validator->errors()->add($field, __('users.' . $field . '_' . $error));
                }
            },
        ];
    }
}
