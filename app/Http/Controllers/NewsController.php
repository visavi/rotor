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
    public function view(int $id): View
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
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        $cid = int($request->input('cid'));
        if ($cid) {
            $total = $news->comments->where('id', '<=', $cid)->count();

            $page = ceil($total / setting('comments_per_page'));

            return redirect()->route('news.comments', ['id' => $news->id, 'page' => $page])
                ->withFragment('comment_' . $cid);
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
                sendNotify($msg, route('news.comments', ['id' => $news->id, 'cid' => $comment->id], false), $news->title);

                setFlash('success', __('main.comment_added_success'));

                if ($request->has('read')) {
                    return redirect()->route('news.view', ['id' => $news->id]);
                }

                return redirect()->route('news.comments', [
                    'id'   => $news->id,
                    'page' => ceil($news->comments->count() / setting('comments_per_page')),
                ]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $comments = $news->comments()
            ->select('comments.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('comments.id', 'polls.relate_id')
                    ->where('polls.relate_type', Comment::$morphName)
                    ->where('polls.user_id', getUser('id'));
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

        $news = News::query()->find($id);

        if (! $news) {
            abort(404, __('news.news_not_exist'));
        }

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

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

                return redirect()->route('news.comments', ['id' => $news->id, 'page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('news/editcomment', compact('news', 'comment', 'page'));
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
}
