<?php

declare(strict_types=1);

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

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $this->note = Notebook::query()
            ->where('user_id', $user->id)
            ->firstOrNew(['user_id' => $user->id]);
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        return view('notebooks/index', ['note' => $this->note]);
    }

    /**
     * Редактирование
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function edit(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->equal($request->input('token'), $_SESSION['token'], ['msg' => __('validator.token')])
                ->length($msg, 0, 10000, ['msg' => __('validator.text_long')], false);

            if ($validator->isValid()) {
                $this->note->fill([
                    'text'       => $msg,
                    'created_at' => SITETIME,
                ])->save();

                setFlash('success', __('main.record_saved_success'));
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }

            redirect('/notebooks');
        }

        return view('notebooks/edit', ['note' => $this->note]);
    }
}
