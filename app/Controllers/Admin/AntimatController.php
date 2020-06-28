<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Antimat;
use App\Models\User;
use Exception;
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
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        if ($request->isMethod('post')) {
            $word = utfLower($request->input('word'));

            $validator
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->notEmpty($word, __('admin.antimat.not_enter_word'));

            $duplicate = Antimat::query()->where('string', $word)->first();
            $validator->empty($duplicate, __('admin.antimat.word_listed'));

            if ($validator->isValid()) {
                Antimat::query()->create([
                    'string' => $word
                ]);

                setFlash('success', __('main.record_added_success'));
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
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     * @throws Exception
     */
    public function delete(Request $request, Validator $validator): void
    {
        $id = int($request->input('id'));

        $validator->equal($request->input('token'), $_SESSION['token'], __('validator.token'));

        $word = Antimat::query()->find($id);
        $validator->notEmpty($word, __('main.record_not_found'));

        if ($validator->isValid()) {
            $word->delete();

            setFlash('success', __('main.record_deleted_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/antimat');
    }

    /**
     * Очистка списка слов
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return void
     */
    public function clear(Request $request, Validator $validator): void
    {
        $validator
            ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
            ->true(isAdmin(User::BOSS), __('main.page_only_owner'));

        if ($validator->isValid()) {
            Antimat::query()->truncate();

            setFlash('success', __('main.records_cleared_success'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/admin/antimat');
    }
}
