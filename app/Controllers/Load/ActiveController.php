<?php

declare(strict_types=1);

namespace App\Controllers\Load;

use App\Controllers\BaseController;
use App\Models\Comment;
use App\Models\Down;
use App\Models\User;
use Illuminate\Http\Request;

class ActiveController extends BaseController
{
    /**
     * @var User
     */
    public $user;

    /**
     * Конструктор
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $login      = $request->input('user', getUser('login'));
        $this->user = getUserByLogin($login);

        if (! $this->user) {
            abort(404, __('validator.user'));
        }
    }

    /**
     * Мои файлы
     *
     * @param Request $request
     *
     * @return string
     */
    public function files(Request $request): string
    {
        $active = int($request->input('active', 1));
        $user   = $this->user;

        if (getUser() && getUser('id') !== $user->id) {
            $active = 1;
        }

        $downs = Down::query()
            ->where('active', $active)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('category', 'user')
            ->paginate(setting('downlist'))
            ->appends([
                'user'   => $user->login,
                'active' => $active,
            ]);

        return view('loads/active_files', compact('downs', 'user', 'active'));
    }

    /**
     * Мои комментарии
     *
     * @return string
     */
    public function comments(): string
    {
        $user = $this->user;

        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', Down::$morphName)
            ->where('comments.user_id', $user->id)
            ->leftJoin('downs', 'comments.relate_id', 'downs.id')
            ->orderByDesc('comments.created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'))
            ->appends(['user' => $user->login]);

        return view('loads/active_comments', compact('comments', 'user'));
    }
}
