<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\BlackList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlacklistRequest extends FormRequest
{
    public function rules(): array
    {
        $type = $this->type();

        $rules = match ($type) {
            'login'  => ['between:3,20', 'regex:#^[a-z0-9\-]+$#'],
            'domain' => ['between:1,100', 'regex:#^[а-яa-z0-9_.-]+(\.[а-яa-z0-9/]+)+$#u'],
            default  => ['between:1,100', 'regex:#^[a-z0-9_.-]+@[a-z0-9_.-]+(\.[a-z0-9]+)+$#'],
        };

        return [
            'value' => [
                'required',
                'string',
                ...$rules,
                Rule::unique(BlackList::class, 'value')->where('type', $type),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'value.regex' => match ($this->type()) {
                'login'  => __('admin.blacklists.invalid_login'),
                'domain' => __('validator.site'),
                default  => __('validator.email'),
            },
            'value.unique' => __('main.record_exists'),
            'value'        => __('validator.text'),
        ];
    }

    /**
     * Возвращает тип списка
     */
    public function type(): string
    {
        return $this->string('type', 'email')->value();
    }

    /**
     * Приводит значение к виду, в котором оно хранится в списке
     */
    protected function prepareForValidation(): void
    {
        $value = $this->string('value')->lower()->value();

        if ($this->type() === 'domain') {
            if (! preg_match('#^https?://#i', $value)) {
                $value = 'https://' . $value;
            }

            // Для строки без хоста parse_url() отдаёт false или null
            $value = (string) parse_url($value, PHP_URL_HOST);
        }

        $this->merge(['value' => $value]);
    }

    protected function getRedirectUrl(): string
    {
        return url('admin/blacklists?type=' . $this->type());
    }
}
