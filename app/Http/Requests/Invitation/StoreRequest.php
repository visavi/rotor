<?php

namespace App\Http\Requests\Invitation;

use App\Services\InviteService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator)
    {
        $inviteService = app(InviteService::class);
        $lastInvite = $inviteService->getLastInviteByUserId($this->user()->id);

        $validator->after(function ($validator) use ($lastInvite) {
            if ($lastInvite) {
                $validator->errors()->add('', __('invitations.limit_reached'));
            }
        });
    }
}
