<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Rules\TextLengthRule;
use Illuminate\Foundation\Http\FormRequest;

class BanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'time'   => ['required', 'integer', 'min:1'],
            'type'   => ['required', 'in:minutes,hours,days'],
            'reason' => ['required', 'string', new TextLengthRule(5, 1000)],
        ];
    }

    public function messages(): array
    {
        return [
            'time'   => __('admin.bans.time_not_indicated'),
            'type'   => __('admin.bans.time_not_selected'),
            'reason' => __('validator.text'),
        ];
    }

    /**
     * Срок бана в секундах
     */
    public function term(): int
    {
        return (int) $this->validated('time') * match ($this->validated('type')) {
            'days'  => 86400,
            'hours' => 3600,
            default => 60,
        };
    }
}
