<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Flood;
use App\Models\Guest;
use App\Models\Login;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class LoginController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, 'Для просмотра истории необходимо авторизоваться');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Login::query()->where('user_id', getUser('id'))->count();
        $page = paginate(setting('loginauthlist'), $total);

        $logins = Login::query()
            ->where('user_id', getUser('id'))
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('login/index', compact('logins', 'page'));
    }
}
