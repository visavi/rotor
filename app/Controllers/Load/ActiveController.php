<?php

declare(strict_types=1);

namespace App\Controllers\Load;

use App\Controllers\BaseController;
use App\Models\Comment;
use App\Models\Down;
use Illuminate\Http\Request;

class ActiveController extends BaseController
{
    public $user;

    /**
     * Конструктор
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $login      = check($request->input('user', getUser('login')));
        $this->user = getUserByLogin($login);

        if (! $this->user) {
            abort(404, __('validator.user'));
        }
    }

    /**
     * Мои файлы
     *
     * @param Request $request
     * @return string
     */
    public function files(Request $request): string
    {
        $active = int($request->input('active', 1));
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
            ->leftJoin('downs', 'comments.relate_id', 'downs.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        return view('loads/active_comments', compact('comments', 'page', 'user'));
    }
}

