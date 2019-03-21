<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DeliveryController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {

            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));
            $type  = int($request->input('type'));
            $users = collect();

            $validator->equal($token, $_SESSION['token'], ['msg' => trans('validator.token')])
                ->length($msg, 5, setting('comment_length'), ['msg' => trans('validator.text')])
                ->between($type, 1, 4, 'Вы не выбрали получаетелей рассылки!');

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

            /** @var Collection $users */
            $users = $users->filter(function ($value, $key) {
                return $value->id !== getUser('id');
            });

            if ($users->isEmpty()) {
                $validator->addError('Отсутствуют получатели рассылки!');
            }

            if ($validator->isValid()) {

                foreach ($users as $user) {
                    $user->sendMessage(null, $msg);
                }

                setFlash('success', 'Сообщение успешно разослано!');
                redirect('/admin/delivery');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/delivery/index');
    }
}
