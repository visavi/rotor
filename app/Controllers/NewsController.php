<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\News;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

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
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('user')
            ->get();

        $isModer = isAdmin(User::MODER);

        return view('news/index', compact('news', 'page', 'isModer'));
    }

    /**
     * Вывод новости
     */
    public function view($id)
    {
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        $news['text'] = str_replace('[cut]', '', $news['text']);

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
     */
    public function comments($id)
    {
        $news = News::query()->find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validator = new Validator();
            $validator->true(getUser(), 'Чтобы добавить комментарий необходимо авторизоваться')
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->equal(Flood::isFlood(), true, ['msg' => 'Антифлуд! Разрешается комментировать раз в ' . Flood::getPeriod() . ' сек!'])
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий комментарий!'])
                ->empty($news['closed'], ['msg' => 'Комментирование данной новости запрещено!']);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                Comment::query()->create([
                    'relate_type' => News::class,
                    'relate_id'   => $news->id,
                    'text'        => $msg,
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $user = User::query()->where('id', getUser('id'));
                $user->update([
                    'allcomments' => DB::raw('allcomments + 1'),
                    'point'       => DB::raw('point + 1'),
                    'money'       => DB::raw('money + 5'),
                ]);

                $news->update([
                    'comments' => DB::raw('comments + 1'),
                ]);

                setFlash('success', 'Комментарий успешно добавлен!');

                if (Request::has('read')) {
                    redirect('/news/' . $news->id);
                }

                redirect('/news/' . $news->id . '/end');

            } else {
                setInput(Request::all());
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
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at')
            ->with('user')
            ->get();

        return view('news/comments', compact('news', 'comments', 'page'));
    }

    /**
     * Редактирование комментария
     */
    public function editComment($nid, $id)
    {
        $page = abs(intval(Request::input('page', 1)));

        if (!getUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::query()
            ->where('relate_type', News::class)
            ->where('comments.id', $id)
            ->where('comments.user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment['created_at'] + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий комментарий!']);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
                redirect('/news/' . $nid . '/comments?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }
        return view('news/editcomment', compact('comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {
        $news = News::query()->find($id);

        if (empty($news)) {
            abort(404, 'Ошибка! Данной новости не существует!');
        }

        $end = ceil($news['comments'] / setting('postnews'));
        redirect('/news/' . $id . '/comments?page=' . $end);
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
            ->select('comments.*', 'title', 'comments')
            ->where('relate_type', News::class)
            ->leftJoin('news', 'comments.relate_id', '=', 'news.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        return view('news/allcomments', compact('comments', 'page'));
    }

    /**
     * Переход к сообщению
     */
    public function viewComment($id, $cid)
    {
        $news = News::query()->find($id);

        if (empty($news)) {
            abort(404, 'Ошибка! Данной новости не существует!');
        }

        $total = Comment::query()
            ->where('relate_type', News::class)
            ->where('relate_id', $id)
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('postnews'));
        redirect('/news/' . $id . '/comments?page=' . $end . '#comment_' . $cid);
    }
}
