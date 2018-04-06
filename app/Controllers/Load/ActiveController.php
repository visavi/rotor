<?php

namespace App\Controllers\Load;

use App\Classes\Request;
use App\Controllers\BaseController;
use App\Models\Comment;
use App\Models\Down;
use App\Models\User;

class ActiveController extends BaseController
{
    public $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        $login = check(Request::input('user', getUser('login')));

        $this->user = User::query()->where('login', $login)->first();

        if (! $this->user) {
            abort('default', 'Пользователь не найден!');
        }
    }

    /**
     * Мои файлы
     */
    public function files()
    {
        $user  = $this->user;
        $total = Down::query()->where('active', 1)->where('user_id', $user->id)->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('downlist'), $total);

        $downs = Down::query()
            ->where('active', 1)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category', 'user')
            ->get();

        return view('load/active_files', compact('downs', 'page', 'user'));
    }

    /**
     * Мои комментарии
     */
    public function comments()
    {
        $user  = $this->user;
        $total = Comment::query()->where('relate_type', Down::class)->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('downcomm'), $total);

        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', Down::class)
            ->where('comments.user_id', $user->id)
            ->leftJoin('downs', 'comments.relate_id', '=', 'downs.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        return view('load/active_comments', compact('comments', 'page', 'user'));
    }
}

