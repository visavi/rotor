<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Flood;
use App\Rules\TextLengthRule;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Добавление комментария с сайта
 *
 * Правила текста общие для сайта и API — textRules()
 */
class CommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'msg' => [
                ...self::textRules(),
                self::floodRule(),
                // Гостю — ошибка формы, а не 403: форма ответа разбирает только errors
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if (! getUser()) {
                        $fail(__('main.not_authorized'));
                    }
                },
            ],
            'parent_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'msg.required' => __('validator.text'),
            'msg.string'   => __('validator.text'),
        ];
    }

    /**
     * Правила текста комментария
     *
     * Текст — HTML из редактора, длину считает TextLengthRule без тегов
     */
    public static function textRules(): array
    {
        return [
            'required',
            'string',
            new TextLengthRule((int) setting('comment_text_min'), (int) setting('comment_text_max')),
        ];
    }

    /**
     * Запрещает писать чаще, чем позволяет антифлуд
     */
    public static function floodRule(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            $flood = app(Flood::class);

            if ($flood->isFlood()) {
                $fail(__('validator.flood', ['sec' => $flood->getPeriod()]));
            }
        };
    }
}
