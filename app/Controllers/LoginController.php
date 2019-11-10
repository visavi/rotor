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
            abort(403, __('main.not_authorized'));
        }
    }

    /**
     * Главная страница
     */
    public function index(): string
    {
        $logins = Login::query()
            ->where('user_id', getUser('id'))
            ->orderByDesc('created_at')
            ->paginate(setting('loginauthlist'));

        return view('logins/index', compact('logins'));
    }
}
