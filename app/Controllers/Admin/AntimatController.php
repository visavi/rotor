<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Antimat;
use App\Models\User;

class AntimatController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
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
            $word  = check(utfLower(Request::input('word')));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->notEmpty($word, 'Вы не ввели слово для занесения в список!');

            $duplicate = Antimat::query()->where('string', $word)->first();
            $validator->empty($duplicate, 'Введенное слово уже имеетеся в списке!');

            if ($validator->isValid()) {

                Antimat::query()->create([
                    'string' => $word
                ]);

                setFlash('success', 'Слово успешно добавлено!');
                redirect('/admin/antimat');

            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $words = Antimat::query()->get();

        return view('admin/antimat/index', compact('words'));
    }

    /**
     * Удаление слова из списка
     */
    public function delete()
    {
        $token = check(Request::input('token'));
        $id    = int(Request::input('id'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

        $word = Antimat::query()->find($id);
        $validator->notEmpty($word, 'Выбранное для удаления слово не найдено!');

        if ($validator->isValid()) {

            $word->delete();

            setFlash('success', 'Слово успешно удалено!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/antimat');
    }

    /**
     * Очистка списка слов
     */
    public function clear()
    {
        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(isAdmin(User::BOSS), 'Очищать список может только владелец!');

        if ($validator->isValid()) {

            Antimat::query()->truncate();

            setFlash('success', 'Список успешно очищен!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/antimat');
    }
}
