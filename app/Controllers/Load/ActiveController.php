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
            abort(404, 'Пользователь не найден!');
        }
    }

    /**
     * Мои файлы
     *
     * @return string
     */
    public function files(): string
    {
        $active = check(Request::input('active', 1));
        $user   = $this->user;

        if ($user->id !== getUser('id')) {
            $active = 1;
        }

        $total = Down::query()->where('active', $active)->where('user_id', $user->id)->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('downlist'), $total);

        $downs = Down::query()
            ->where('active', $active)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($page->limit)
            ->offset($page->offset)
            ->with('category', 'user')
            ->get();

        return view('loads/active_files', compact('downs', 'page', 'user', 'active'));
    }

    /**
     * Мои комментарии
     *
     * @return string
     */
    public function comments(): string
    {
        $user  = $this->user;
        $total = Comment::query()
            ->where('relate_type', Down::class)
            ->where('user_id', $user->id)
            ->count();

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

        return view('loads/active_comments', compact('comments', 'page', 'user'));
    }
}

