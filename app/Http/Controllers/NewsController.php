<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\News;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * Главная страница
     */
    public function index(): View
    {
        $news = News::query()
            ->select('news.*', 'pollings.vote')
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('news.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', News::$morphName)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('postnews'));

        return view('news/index', compact('news'));
    }

    /**
     * Вывод новости
     */
    public function view(int $id): View
    {
        /** @var News $news */
        $news = News::query()
            ->select('news.*', 'pollings.vote')
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('news.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', News::$morphName)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        $comments = $news->comments()
            ->limit(5)
            ->orderByDesc('created_at')
            ->with('user')
            ->get()
            ->reverse();

        return view('news/view', compact('news', 'comments'));
    }

    /**
     * Комментарии
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator->true(getUser(), __('main.not_authorized'))
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->empty($news->closed, ['msg' => __('news.closed_news')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                /** @var Comment $comment */
                $comment = $news->comments()->create([
                    'text'       => $msg,
                    'user_id'    => getUser('id'),
                    'created_at' => SITETIME,
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                ]);

                $user = getUser();
                $user->increment('allcomments');
                $user->increment('point');
                $user->increment('money', 5);

                $news->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, '/news/comment/' . $news->id . '/' . $comment->id, $news->title);

                setFlash('success', __('main.comment_added_success'));

                if ($request->has('read')) {
                    return redirect('news/' . $news->id);
                }

                return redirect('news/end/' . $news->id . '');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $comments = $news->comments()
            ->select('comments.*', 'pollings.vote')
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('comments.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Comment::$morphName)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->orderBy('created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return view('news/comments', compact('news', 'comments'));
    }

    /**
     * Редактирование комментария
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): View|RedirectResponse
    {
        $page = int($request->input('page', 1));

        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Comment $comment */
        $comment = $news->comments()
            ->where('id', $cid)
            ->where('user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort(200, __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort(200, __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));

                return redirect('news/comments/' . $news->id . '?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('news/editcomment', compact('news', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end(int $id): RedirectResponse
    {
        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        $end = ceil($news->count_comments / setting('comments_per_page'));

        return redirect('news/comments/' . $id . '?page=' . $end);
    }

    /**
     * Rss новостей
     */
    public function rss(): View
    {
        $newses = News::query()->orderByDesc('created_at')->limit(15)->get();

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

    /**
     * Переход к сообщению
     */
    public function viewComment(int $id, int $cid): RedirectResponse
    {
        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        $total = $news->comments()
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('comments_per_page'));

        return redirect('news/comments/' . $news->id . '?page=' . $end . '#comment_' . $cid);
    }
}
