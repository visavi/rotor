<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;

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
     */
    public function index()
    {
        if (Request::isMethod('post')) {

            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));
            $type  = int(Request::input('type'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий текст комментария!'])
                ->between($type, 1, 4, 'Вы не выбрали получаетелей рассылки!');

            // Рассылка пользователям, которые в онлайне
            if ($type == 1) {
                $users = User::query()->whereHas('online')->get();
            }

            // Рассылка активным пользователям, которые посещали сайт менее недели назад
            if ($type == 2) {
                $users = User::query()->where('updated_at', '>', strtotime('-1 week', SITETIME))->get();
            }

            // Рассылка администрации
            if ($type == 3){
                $users = User::query()->whereIn('level', User::ADMIN_GROUPS)->get();
            }

            // Рассылка всем пользователям сайта
            if ($type == 4){
                $users = User::query()->whereIn('level', User::USER_GROUPS)->get();
            }

            $users = $users->filter(function ($value, $key) {
                return $value->id != getUser('id');
            });

            if ($users->isEmpty()) {
                $validator->addError('Отсутствуют получатели рассылки!');
            }

            if ($validator->isValid()) {

                foreach ($users as $user) {
                    sendPrivate($user, null, $msg);
                }

                setFlash('success', 'Сообщение успешно разослано!');
                redirect('/admin/delivery');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/delivery/index');
    }
}
