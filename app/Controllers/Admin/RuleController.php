<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Rule;
use App\Models\User;

class RuleController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $rules = Rule::query()->first();

        $replace = [
            '%SITENAME%' => setting('title'),
        ];

        if ($rules) {
            $rules->text = str_replace(array_keys($replace), $replace, $rules->text);
        }

        return view('admin/rule/index', compact('rules'));
    }

    /**
     * Редактирование правил
     */
    public function edit()
    {
        $rules = Rule::query()->firstOrNew([]);

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->notEmpty($msg, ['msg' => 'Вы не ввели текст с правилами сайта!']);

            if ($validator->isValid()) {

                $rules->fill([
                    'text'       => $msg,
                    'created_at' => SITETIME,
                ])->save();

                setFlash('success', 'Правила успешно изменены!');
                redirect('/admin/rules');

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/rule/edit', compact('rules'));
    }
}
