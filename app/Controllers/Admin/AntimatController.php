<?php

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Antimat;
use App\Models\User;
use Illuminate\Http\Request;

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
     *
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $word  = check(utfLower($request->input('word')));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->notEmpty($word, 'Вы не ввели слово для занесения в список!');

            $duplicate = Antimat::query()->where('string', $word)->first();
            $validator->empty($duplicate, 'Введенное слово уже имеется в списке!');

            if ($validator->isValid()) {

                Antimat::query()->create([
                    'string' => $word
                ]);

                setFlash('success', 'Слово успешно добавлено!');
                redirect('/admin/antimat');

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $words = Antimat::query()->get();

        return view('admin/antimat/index', compact('words'));
    }

    /**
     * Удаление слова из списка
     *
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function delete(Request $request): void
    {
        $token = check($request->input('token'));
        $id    = int($request->input('id'));

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
     *
     * @param Request $request
     * @return void
     */
    public function clear(Request $request): void
    {
        $token = check($request->input('token'));

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
