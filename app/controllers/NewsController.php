<?php

class NewsController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = News::count();
        $page = App::paginate(Setting::get('postnews'), $total);

        $news = News::orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('user')
            ->get();

        App::view('news/index', compact('news', 'page'));
    }

    /**
     * Вывод новости
     */
    public function view($id)
    {
        $news = News::find($id);

        if (! $news) {
            App::abort(404, 'Новость не существует, возможно она была удалена!');
        }

        $news['text'] = str_replace('[cut]', '', $news['text']);

        $comments = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->limit(5)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        $comments = $comments->reverse();

        App::view('news/view', compact('news', 'comments'));
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $news = News::find($id);

        if (! $news) {
            App::abort(404, 'Новость не существует, возможно она была удалена!');
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validation = new Validation();

            $validation->addRule('bool', is_user(), 'Чтобы добавить комментарий необходимо авторизоваться')
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('equal', [Flood::isFlood(App::getUserId()), true], 'Антифлуд! Разрешается комментировать раз в ' . Flood::getPeriod() . ' сек!')
                ->addRule('string', $msg, 'Слишком длинный или короткий комментарий!', true, 5, 1000)
                ->addRule('empty', $news['closed'], 'Комментирование данной новости запрещено!');

            if ($validation->run()) {
                $msg = antimat($msg);

                Comment::create([
                    'relate_type' => News::class,
                    'relate_id'   => $news->id,
                    'text'        => $msg,
                    'user_id'     => App::getUserId(),
                    'created_at'  => SITETIME,
                    'ip'          => App::getClientIp(),
                    'brow'        => App::getUserAgent(),
                ]);

                $user = User::where('id', App::getUserId());
                $user->update([
                    'allcomments' => Capsule::raw('allcomments + 1'),
                    'point'       => Capsule::raw('point + 1'),
                    'money'       => Capsule::raw('money + 5'),
                ]);

                $news->update([
                    'comments' => Capsule::raw('comments + 1'),
                ]);

                App::setFlash('success', 'Комментарий успешно добавлен!');

                if (isset($_GET['read'])) {
                    App::redirect('/news/' . $news->id);
                }

                App::redirect('/news/' . $news->id . '/end');

            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        $total = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->count();

        $page = App::paginate(Setting::get('postnews'), $total);

        $comments = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at')
            ->with('user')
            ->get();

        App::view('news/comments', compact('news', 'comments', 'page'));
    }

    /**
     * Редактирование комментария
     */
    public function editcomment($nid, $id)
    {
        $page = abs(intval(Request::input('page', 1)));

        if (!is_user()) {
            App::abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::where('relate_type', News::class)
            ->where('comments.id', $id)
            ->where('comments.user_id', App::getUserId())
            ->first();

        if (! $comment) {
            App::abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment['created_at'] + 600 < SITETIME) {
            App::abort('default', 'Редактирование невозможно, прошло более 10 минут!');
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

                App::setFlash('success', 'Комментарий успешно отредактирован!');
                App::redirect('/news/' . $nid . '/comments?page=' . $page);
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }
        App::view('news/editcomment', compact('comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {
        $news = News::find($id);

        if (empty($news)) {
            App::abort(404, 'Ошибка! Данной новости не существует!');
        }

        $end = ceil($news['comments'] / Setting::get('postnews'));
        App::redirect('/news/' . $id . '/comments?page=' . $end);
    }

    /**
     * Rss новостей
     */
    public function rss()
    {
        $newses = News::orderBy('created_at', 'desc')->limit(15)->get();

        if ($newses->isEmpty()) {
            App::abort('default', 'Новости не найдены!');
        }

        App::view('news/rss', compact('newses'));
    }
}
