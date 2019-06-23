<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\News;

use Illuminate\Http\Request;

class NewsController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = News::query()->count();
        $page = paginate(setting('postnews'), $total);

        $news = News::query()
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('news/index', compact('news', 'page'));
    }

    /**
     * Вывод новости
     *
     * @param int $id
     * @return string
     */
    public function view(int $id): string
    {
        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        $comments = Comment::query()
            ->where('relate_type', News::class)
            ->where('relate_id', $id)
            ->limit(5)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        $comments = $comments->reverse();

        return view('news/view', compact('news', 'comments'));
    }

    /**
     * Комментарии
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     * @return string
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): string
    {
        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        if ($request->isMethod('post')) {
            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator->true(getUser(), 'Чтобы добавить комментарий необходимо авторизоваться')
                ->equal($token, $_SESSION['token'], trans('validator.token'))
                ->false($flood->isFlood(), ['msg' => trans('validator.flood', ['sec' => $flood->getPeriod()])])
                ->length($msg, 5, setting('comment_length'), ['msg' => trans('validator.text')])
                ->empty($news->closed, ['msg' => 'Комментирование данной новости запрещено!']);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                /** @var Comment $comment */
                $comment = Comment::query()->create([
                    'relate_type' => News::class,
                    'relate_id'   => $news->id,
                    'text'        => $msg,
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $user = getUser();
                $user->increment('allcomments');
                $user->increment('point');
                $user->increment('money', 5);

                $news->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, '/news/comment/' . $news->id . '/' . $comment->id, $news->title);

                setFlash('success', 'Комментарий успешно добавлен!');

                if ($request->has('read')) {
                    redirect('/news/' . $news->id);
                }

                redirect('/news/end/' . $news->id . '');

            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Comment::query()
            ->where('relate_type', News::class)
            ->where('relate_id', $id)
            ->count();

        $page = paginate(setting('postnews'), $total);

        $comments = Comment::query()
            ->where('relate_type', News::class)
            ->where('relate_id', $id)
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('created_at')
            ->with('user')
            ->get();

        return view('news/comments', compact('news', 'comments', 'page'));
    }

    /**
     * Редактирование комментария
     *
     * @param int       $id
     * @param int       $cid
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): string
    {
        $page = int($request->input('page', 1));

        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        if (! getUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::query()
            ->where('relate_type', News::class)
            ->where('comments.id', $cid)
            ->where('comments.user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if ($request->isMethod('post')) {
            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator
                ->equal($token, $_SESSION['token'], trans('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => trans('validator.text')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
                redirect('/news/comments/' . $news->id . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }
        return view('news/editcomment', compact('news', 'comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     *
     * @param $id
     */
    public function end($id): void
    {
        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, 'Данной новости не существует!');
        }

        $end = ceil($news->count_comments / setting('postnews'));
        redirect('/news/comments/' . $id . '?page=' . $end);
    }

    /**
     * Rss новостей
     */
    public function rss()
    {
        $newses = News::query()->orderBy('created_at', 'desc')->limit(15)->get();

        if ($newses->isEmpty()) {
            abort('default', 'Новости не найдены!');
        }

        return view('news/rss', compact('newses'));
    }

    /**
     * Все комментарии
     */
    public function allComments()
    {
        $total = Comment::query()->where('relate_type', News::class)->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('postnews'), $total);

        $comments = Comment::query()
            ->select('comments.*', 'title', 'count_comments')
            ->where('relate_type', News::class)
            ->leftJoin('news', 'comments.relate_id', 'news.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        return view('news/allcomments', compact('comments', 'page'));
    }

    /**
     * Переход к сообщению
     *
     * @param int $id
     * @param int $cid
     */
    public function viewComment(int $id, int $cid): void
    {
        /** @var News $news */
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, 'Данной новости не существует!');
        }

        $total = Comment::query()
            ->where('relate_type', News::class)
            ->where('relate_id', $id)
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('postnews'));
        redirect('/news/comments/' . $news->id . '?page=' . $end . '#comment_' . $cid);
    }
}
