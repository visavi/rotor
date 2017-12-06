<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Rule;
use App\Models\User;

class RulesController extends AdminController
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
            '%MAXBAN%'   => round(setting('maxbantime') / 1440),
        ];

        if ($rules) {
            $rules->text = str_replace(array_keys($replace), $replace, $rules->text);
        }

        return view('admin/rules/index', compact('rules'));
    }
}
