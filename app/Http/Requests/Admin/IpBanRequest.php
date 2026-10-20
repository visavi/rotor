<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Ban;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IpBanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ip' => ['required', 'ip', Rule::unique(Ban::class, 'ip')],
        ];
    }

    public function messages(): array
    {
        return [
            'ip.unique' => __('admin.ipbans.ip_exists'),
            'ip'        => __('admin.ipbans.ip_invalid'),
        ];
    }
}
