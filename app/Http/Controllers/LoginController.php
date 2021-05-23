<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Login;
use Illuminate\View\View;

class LoginController extends Controller
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
     *
     * @return View
     */
    public function index(): View
    {
        $logins = Login::query()
            ->where('user_id', getUser('id'))
            ->orderByDesc('created_at')
            ->paginate(setting('loginauthlist'));

        return view('logins/index', compact('logins'));
    }
}
