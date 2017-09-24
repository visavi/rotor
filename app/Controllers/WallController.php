<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Models\User;
use App\Models\Wall;

class WallController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($login)
    {
        $user = User::query()->where('login', $login)->first();

        $total   = Wall::query()->where('user_id', $user->id)->count();
        $page    = paginate(setting('wallpost'), $total);
        $newWall = getUser('newwall');

        $messages = Wall::query()
            ->where('user_id', $user->id)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($newWall && $user->id == getUser('id')) {
            $user->update([
                'newwall' => 0,
            ]);
        }

        return view('wall/index', compact('messages', 'user', 'page', 'newWall'));
    }

    /**
     * Удаление сообщений
     */
    public function delete() {

    }
}
