<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\News;
use App\Traits\CommentableTrait;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    use CommentableTrait;

    protected function commentableModel(): string
    {
        return News::class;
    }

    /**
     * Главная страница
     */
    public function index(): View
    {
        $news = News::query()
            ->select('news.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('news.id', 'polls.relate_id')
                    ->where('polls.relate_type', News::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->orderByDesc('created_at')
            ->with('user', 'files')
            ->paginate(setting('postnews'));

        return view('news/index', compact('news'));
    }

    /**
     * Вывод новости
     */
    public function view(int $id, Request $request): View|RedirectResponse
    {
        $news = News::query()
            ->select('news.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('news.id', 'polls.relate_id')
                    ->where('polls.relate_type', News::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        if ($redirect = $this->cidRedirect($news, $request)) {
            return $redirect;
        }

        ['comments' => $comments, 'files' => $files] = $this->getCommentsData($news);

        return view('news/view', compact('news', 'comments', 'files'));
    }

    /**
     * Rss новостей
     */
    public function rss(): View
    {
        $newses = News::query()
            ->orderByDesc('created_at')
            ->with('user', 'files')
            ->limit(15)
            ->get();

        if ($newses->isEmpty()) {
            abort(200, __('news.empty_news'));
        }

        return view('news/rss', compact('newses'));
    }

    /**
     * Все комментарии
     */
    public function allComments(): View
    {
        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', News::$morphName)
            ->leftJoin('news', 'comments.relate_id', 'news.id')
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return view('news/allcomments', compact('comments'));
    }
}
