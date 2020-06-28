<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Rule;
use App\Models\User;
use Illuminate\Http\Request;

class RuleController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::ADMIN)) {
            abort(403, __('errors.forbidden'));
        }
    }

    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $rules = Rule::query()->first();

        $replace = [
            '%SITENAME%' => setting('title'),
        ];

        if ($rules) {
            $rules->text = str_replace(array_keys($replace), $replace, $rules->text);
        }

        return view('admin/rules/index', compact('rules'));
    }

    /**
     * Редактирование правил
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return string
     */
    public function edit(Request $request, Validator $validator): string
    {
        $rules = Rule::query()->firstOrNew([]);

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->equal($request->input('token'), $_SESSION['token'], __('validator.token'))
                ->notEmpty($msg, ['msg' => __('admin.rules.rules_empty')]);

            if ($validator->isValid()) {
                $rules->fill([
                    'text'       => $msg,
                    'created_at' => SITETIME,
                ])->save();

                setFlash('success', __('admin.rules.rules_success_saved'));
                redirect('/admin/rules');
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('admin/rules/edit', compact('rules'));
    }
}
