<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Social;

class SocialController extends BaseController
{
    public $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! $this->user = getUser()) {
            abort(403, 'Для управления социальными сетями необходимо авторизоваться!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $socials = Social::query()
            ->where('user_id', $this->user->id)
            ->get();

        return view('socials/index', compact('socials'));
    }

    /**
     * Удаление привязки
     */
    public function delete($id)
    {
        $token = check(Request::input('token'));

        $social = Social::query()->where('user_id', $this->user->id)->find($id);

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($social, 'Не найдена привязка к социальной сети!');

        if ($validator->isValid()) {

            $social->delete();

            setFlash('success', 'Привязка к социальной сети успешно удалена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/socials');
    }
}
