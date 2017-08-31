<?php

namespace App\Controllers;

class NewsController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = News::count();
        $page = paginate(setting('postnews'), $total);

        $news = News::orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('user')
            ->get();

        view('news/index', compact('news', 'page'));
    }

    /**
     * Вывод новости
     */
    public function view($id)
    {
        $news = News::find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        $news['text'] = str_replace('[cut]', '', $news['text']);

        $comments = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->limit(5)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        $comments = $comments->reverse();

        view('news/view', compact('news', 'comments'));
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $news = News::find($id);

        if (! $news) {
            abort(404, 'Новость не существует, возможно она была удалена!');
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validation = new Validation();

            $validation->addRule('bool', isUser(), 'Чтобы добавить комментарий необходимо авторизоваться')
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('equal', [Flood::isFlood(), true], 'Антифлуд! Разрешается комментировать раз в ' . Flood::getPeriod() . ' сек!')
                ->addRule('string', $msg, 'Слишком длинный или короткий комментарий!', true, 5, 1000)
                ->addRule('empty', $news['closed'], 'Комментирование данной новости запрещено!');

            if ($validation->run()) {
                $msg = antimat($msg);

                Comment::create([
                    'relate_type' => News::class,
                    'relate_id'   => $news->id,
                    'text'        => $msg,
                    'user_id'     => getUserId(),
                    'created_at'  => SITETIME,
                    'ip'          => getClientIp(),
                    'brow'        => getUserAgent(),
                ]);

                $user = User::where('id', getUserId());
                $user->update([
                    'allcomments' => DB::raw('allcomments + 1'),
                    'point'       => DB::raw('point + 1'),
                    'money'       => DB::raw('money + 5'),
                ]);

                $news->update([
                    'comments' => DB::raw('comments + 1'),
                ]);

                setFlash('success', 'Комментарий успешно добавлен!');

                if (isset($_GET['read'])) {
                    redirect('/news/' . $news->id);
                }

                redirect('/news/' . $news->id . '/end');

            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        $total = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->count();

        $page = paginate(setting('postnews'), $total);

        $comments = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at')
            ->with('user')
            ->get();

        view('news/comments', compact('news', 'comments', 'page'));
    }

    /**
     * Редактирование комментария
     */
    public function editComment($nid, $id)
    {
        $page = abs(intval(Request::input('page', 1)));

        if (!isUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::where('relate_type', News::class)
            ->where('comments.id', $id)
            ->where('comments.user_id', getUserId())
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

            $validation = new Validation();
            $validation
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $msg, ['msg' => 'Слишком длинный или короткий комментарий!'], true, 5, 1000);

            if ($validation->run()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
                redirect('/news/' . $nid . '/comments?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }
        view('news/editcomment', compact('comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {
        $news = News::find($id);

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
        $newses = News::orderBy('created_at', 'desc')->limit(15)->get();

        if ($newses->isEmpty()) {
            abort('default', 'Новости не найдены!');
        }

        view('news/rss', compact('newses'));
    }

    /**
     * Все комментарии
     */
    public function allComments()
    {
        $total = Comment::where('relate_type', News::class)->count();

        if ($total > 500) {
            $total = 500;
        }

        $page = paginate(setting('postnews'), $total);

        $comments = Comment::select('comments.*', 'title', 'comments')
            ->where('relate_type', News::class)
            ->leftJoin('news', 'comments.relate_id', '=', 'news.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        view('news/allcomments', compact('comments', 'page'));
    }

    /**
     * Переход к сообщению
     */
    public function viewComment($id, $cid)
    {
        $news = News::find($id);

        if (empty($news)) {
            abort(404, 'Ошибка! Данной новости не существует!');
        }

        $total = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('postnews'));
        redirect('/news/' . $id . '/comments?page=' . $end . '#comment_' . $cid);
    }
}
