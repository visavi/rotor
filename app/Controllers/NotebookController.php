<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Notebook;

class NotebookController extends BaseController
{
    private $note;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, 'Для управления блокнотом, необходимо авторизоваться!');
        }

        $this->note = Notebook::query()
            ->where('user_id', getUser('id'))
            ->firstOrNew(['user_id' => getUser('id')]);
    }

    /**
     * Главная страница
     */
    public function index()
    {
        return view('notebook/index', ['note' => $this->note]);
    }

    /**
     * Редактирование
     */
    public function edit()
    {
        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $msg   = check(Request::input('msg'));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], ['msg' => 'Неверный идентификатор сессии, повторите действие!'])
                ->length($msg, 0, 10000, ['msg' => 'Слишком длинная запись!'], false);

            if ($validator->isValid()) {

                $this->note->fill([
                    'text'       => $msg,
                    'created_at' => SITETIME,
                ])->save();

                setFlash('success', 'Запись успешно сохранена!');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }

            redirect('/notebook');
        }

        return view('notebook/edit', ['note' => $this->note]);
    }
}
