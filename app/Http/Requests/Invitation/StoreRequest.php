<?php

namespace App\Http\Requests\Invitation;

use App\Services\InviteService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreRequest extends FormRequest
{
    public function __construct(private InviteService $inviteService)
    {
        parent::__construct();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '_token' => 'required|in:' . csrf_token(),
        ];
    }

    public function withValidator(Validator $validator)
    {
        $lastInvite = $this->inviteService->getLastInviteByUserId(getUser('id'));

        $validator->after(function ($validator) use ($lastInvite) {
            if ($lastInvite) {
                $validator->errors()->add('', __('invitations.limit_reached'));
            }
        });
    }
}
