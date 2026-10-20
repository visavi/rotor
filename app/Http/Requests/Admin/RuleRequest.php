<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Rules\TextLengthRule;
use Illuminate\Foundation\Http\FormRequest;

class RuleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'msg' => ['required', 'string', new TextLengthRule(1, 65000)],
        ];
    }

    public function messages(): array
    {
        return [
            'msg' => __('admin.rules.rules_empty'),
        ];
    }
}
