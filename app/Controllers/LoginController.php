<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Login;

class LoginController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, trans('main.not_authorized'));
        }
    }

    /**
     * Главная страница
     */
    public function index(): string
    {
        $total = Login::query()->where('user_id', getUser('id'))->count();
        $page = paginate(setting('loginauthlist'), $total);

        $logins = Login::query()
            ->where('user_id', getUser('id'))
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('logins/index', compact('logins', 'page'));
    }
}
