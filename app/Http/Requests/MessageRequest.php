<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Личное сообщение с сайта
 *
 * Текст — те же правила, что у комментариев (CommentRequest::textRules), и на сайте, и в API
 */
class MessageRequest extends FormRequest
{
    private ?User $recipient = null;

    public function rules(): array
    {
        return [
            'msg' => [...CommentRequest::textRules(), CommentRequest::floodRule()],
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
     * Себе не пишут; капча — пока у отправителя мало актива
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $user = auth()->user() ?? abort(403);

                if ($this->recipient()?->id === $user->id) {
                    $validator->errors()->add('msg', __('messages.send_yourself'));
                }

                if ($user->point < (int) setting('privatprotect') && ! captchaVerify()) {
                    $validator->errors()->add('protect', __('validator.captcha'));
                }
            },
        ];
    }

    /**
     * Получатель из ?user=
     */
    public function recipient(): ?User
    {
        return $this->recipient ??= getUserByLogin($this->input('user'));
    }

    protected function getRedirectUrl(): string
    {
        $recipient = $this->recipient();

        return $recipient ? url('messages/talk/' . $recipient->login) : parent::getRedirectUrl();
    }
}
