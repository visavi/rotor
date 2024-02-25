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
        $this->middleware('check.user');
    }

    /**
     * Главная страница
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
