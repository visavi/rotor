<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Models\Inbox;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;

class WallController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($login)
    {
        $user = User::query()->where('login', $login)->first();

        return view('wall/index', compact('user'));
    }
}
