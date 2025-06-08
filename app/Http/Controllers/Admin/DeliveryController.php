<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends AdminController
{
    /**
     * Главная страница
     */
    public function index(Request $request, Validator $validator): View|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $msg = $request->input('msg');
            $type = int($request->input('type'));
            $users = collect();

            $validator->equal($request->input('_token'), csrf_token(), ['msg' => __('validator.token')])
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->between($type, 1, 4, __('admin.delivery.not_recipients_selected'));

            // Рассылка пользователям, которые в онлайне
            if ($type === 1) {
                $users = User::query()->whereHas('online')->get();
            }

            // Рассылка активным пользователям, которые посещали сайт менее недели назад
            if ($type === 2) {
                $users = User::query()->where('updated_at', '>', strtotime('-1 week', SITETIME))->get();
            }

            // Рассылка администрации
            if ($type === 3) {
                $users = User::query()->whereIn('level', User::ADMIN_GROUPS)->get();
            }

            // Рассылка всем пользователям сайта
            if ($type === 4) {
                $users = User::query()->whereIn('level', User::USER_GROUPS)->get();
            }

            $users = $users->filter(static function ($value, $key) {
                return $value->id !== getUser('id');
            });

            if ($users->isEmpty()) {
                $validator->addError(__('admin.delivery.not_recipients'));
            }

            if ($validator->isValid()) {
                foreach ($users as $user) {
                    $user->sendMessage(null, $msg);
                }

                setFlash('success', __('admin.delivery.success_sent'));

                return redirect('admin/delivery');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('admin/delivery/index');
    }
}
