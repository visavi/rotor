<?php

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Notebook;
use Illuminate\Http\Request;

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
        return view('notebooks/index', ['note' => $this->note]);
    }

    /**
     * Редактирование
     *
     * @param Request $request
     * @return string
     */
    public function edit(Request $request): string
    {
        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $msg   = check($request->input('msg'));

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
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }

            redirect('/notebooks');
        }

        return view('notebooks/edit', ['note' => $this->note]);
    }
}
