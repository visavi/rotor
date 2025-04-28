<?php

declare(strict_types=1);

namespace App\Http\Controllers\Load;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Down;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActiveController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $login = $request->input('user', getUser('login'));
            $this->user = getUserByLogin($login);

            if (! $this->user) {
                abort(404, __('validator.user'));
            }

            return $next($request);
        });
    }

    /**
     * Мои файлы
     */
    public function files(Request $request): View
    {
        $active = (bool) $request->input('active', true);
        $user = $this->user;

        if (getUser() && getUser('id') !== $user->id) {
            $active = true;
        }

        $downs = Down::query()
            ->active($active)
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
     */
    public function comments(): View
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
