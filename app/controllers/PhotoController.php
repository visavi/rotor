<?php

class PhotoController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Photo::count();
        $page = App::paginate(Setting::get('fotolist'), $total);

        $photos = Photo::orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit(Setting::get('fotolist'))
            ->with('user')
            ->get();

        App::view('gallery/index', compact('photos', 'page'));
    }

    /**
     * Просмотр полной фотографии
     */
    public function view($gid)
    {
        $photo = Photo::select('photo.*', 'pollings.vote')
            ->where('photo.id', $gid)
            ->leftJoin('pollings', function ($join) {
                $join->on('photo.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Photo::class)
                    ->where('pollings.user_id', App::getUserId());
            })
            ->with('user')
            ->first();

        if (! $photo) {
            App::abort(404, 'Фотография не найдена');
        }

        App::view('gallery/view', compact('photo', 'page'));
    }

    /**
     * Форма загрузки фото
     */
    public function create()
    {
        if (!is_user()) {
            App::abort(403, 'Для добавления фотографий небходимо авторизоваться!');
        }

        if (Request::isMethod('post')) {

            $token  = check(Request::input('token'));
            $title  = check(Request::input('title'));
            $text   = check(Request::input('text'));
            $closed = Request::has('closed') ? 1 : 0;

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $title, ['title' => 'Слишком длинное или короткое название!'], true, 5, 50)
                ->addRule('string', $text, ['text' => 'Слишком длинное описание!'], true, 0, 1000)
                ->addRule('bool', Flood::isFlood(App::getUserId()), ['text' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!']);

            $handle = upload_image(
                $_FILES['photo'],
                Setting::get('filesize'),
                Setting::get('fileupfoto'),
                uniqid()
            );

            if (!$handle) {
                $validation->addError(['photo' => 'Не удалось загрузить фотографию!']);
            }

            if ($validation->run()) {

                $handle->process(HOME . '/uploads/pictures/');
                if ($handle->processed) {
                    $photo = new Photo();
                    $photo->user_id = App::getUserId();
                    $photo->title = $title;
                    $photo->text = antimat($text);
                    $photo->link = $handle->file_dst_name;
                    $photo->created_at = SITETIME;
                    $photo->closed = $closed;
                    $photo->save();

                    App::setFlash('success', 'Фотография успешно загружена!');
                    App::redirect('/gallery/' . $photo->id);
                } else {
                    $validation->addError(['photo' => $handle->error]);
                }
            }

            App::setInput(Request::all());
            App::setFlash('danger', $validation->getErrors());
        }

        App::view('gallery/create');
    }

    /**
     * Редактирование фото
     */
    public function edit($gid)
    {
        $page = abs(intval(Request::input('page', 1)));

        if (!is_user()) {
            App::abort(403, 'Авторизуйтесь для редактирования фотографии!');
        }

        $photo = Photo::where('user_id', App::getUserId())->find($gid);

        if (!$photo) {
            App::abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
        }

        if (Request::isMethod('post')) {
            $token = check(Request::input('token'));
            $title = check(Request::input('title'));
            $text = check(Request::input('text'));
            $closed = Request::has('closed') ? 1 : 0;

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $title, ['title' => 'Слишком длинное или короткое название!'], true, 5, 50)
                ->addRule('string', $text, ['text' => 'Слишком длинное описание!'], true, 0, 1000);

            if ($validation->run()) {
                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                ]);

                App::setFlash('success', 'Фотография успешно отредактирована!');
                App::redirect('/gallery/album/' . App::getUsername() . '?page=' . $page);
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        $checked = ($photo['closed'] == 1) ? ' checked="checked"' : '';

        App::view('gallery/edit', compact('photo', 'checked', 'page'));
    }

    /**
     * Список комментариев
     */
    public function comments($gid)
    {
        $photo = Photo::find($gid);

        if (!$photo) {
            App::abort('default', 'Фотография не найдена');
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validation = new Validation();
            $validation
                ->addRule('bool', is_user(), 'Чтобы добавить комментарий необходимо авторизоваться')
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $msg, ['msg' => 'Слишком длинное или короткое название!'], true, 5, 1000)
                ->addRule('bool', Flood::isFlood(App::getUserId()), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!'])
                ->addRule('empty', $photo['closed'], 'Комментирование данной фотографии запрещено!');

            if ($validation->run()) {
                $msg = antimat($msg);

                Comment::create([
                    'relate_type' => Photo::class,
                    'relate_id'   => $photo->id,
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

                $photo->update([
                    'comments' => Capsule::raw('comments + 1'),
                ]);

                App::setFlash('success', 'Комментарий успешно добавлен!');
                App::redirect('/gallery/' . $photo->id . '/end');
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }

        $total = Comment::where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->count();
        $page = App::paginate(Setting::get('postgallery'), $total);

        $comments = Comment::where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at')
            ->with('user')
            ->get();

        App::view('gallery/comments', compact('photo', 'comments', 'page'));
    }

    /**
     * Редактирование комментария
     */
    public function editcomment($gid, $id)
    {
        $page = abs(intval(Request::input('page', 1)));

        if (!is_user()) {
            App::abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::select('comments.*', 'photo.closed')
            ->where('relate_type', Photo::class)
            ->where('comments.id', $id)
            ->where('comments.user_id', App::getUserId())
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->first();

        if (! $comment) {
            App::abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment['closed']) {
            App::abort('default', 'Редактирование невозможно, комментирование запрещено!');
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
                ->addRule('string', $msg, ['msg' => 'Слишком длинный или короткий комментарий!'], true, 5, 1000)
                ->addRule('empty', $comment['closed'], 'Комментирование данной фотографии запрещено!');

            if ($validation->run()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                App::setFlash('success', 'Комментарий успешно отредактирован!');
                App::redirect('/gallery/' . $gid . '/comments?page=' . $page);
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }
        App::view('gallery/editcomment', compact('comment', 'page'));
    }

    /**
     * Удаление фотографий
     */
    public function delete($gid)
    {
        $page = abs(intval(Request::input('page', 1)));

        $token = check(Request::input('token'));

        if (!is_user()) {
            App::abort(403, 'Для удаления фотографий небходимо авторизоваться!');
        }

        $photo = Photo::where('user_id', App::getUserId())->find($gid);

        if (!$photo) {
            App::abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
        }

        $validation = new Validation();
        $validation
            ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', is_writeable(HOME . '/uploads/pictures'), ['Не установлены атрибуты доступа на дирекоторию с фотографиями!'])
            ->addRule('empty', $photo['comments'], 'Запрещено удалять фотографии к которым имеются комментарии!');

        if ($validation->run()) {
            unlink_image('uploads/pictures/', $photo['link']);

            Comment::where('relate_type', Photo::class)
                ->where('relate_id', $photo->id)
                ->delete();

            $photo->delete();

            App::setFlash('success', 'Фотография успешно удалена!');

        } else {
            App::setFlash('danger', $validation->getErrors());
        }

        App::redirect('/gallery/album/' . App::getUsername() . '?page=' . $page);
    }


    /**
     * Переадресация на последнюю страницу
     */
    public function end($gid)
    {
        $photo = Photo::find($gid);

        if (empty($photo)) {
            App::abort(404, 'Выбранное вами фото не найдено, возможно оно было удалено!');
        }

        $total = Comment::where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->count();

        $end = ceil($total / Setting::get('postgallery'));
        App::redirect('/gallery/' . $gid . '/comments?page=' . $end);
    }

    /**
     * Альбомы пользователей
     */
    public function albums()
    {
        $total = Photo::distinct('user_id')
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->count('user_id');

        $page = App::paginate(Setting::get('photogroup'), $total);

        $albums = Photo::select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(comments) as comments')
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->groupBy('user_id')
            ->orderBy('cnt', 'desc')
            ->get();

        App::view('gallery/albums', compact('albums', 'page'));
    }

    /**
     * Альбом пользователя
     */
    public function album($login)
    {
        $user = User::where('login', $login)->first();

        if (!$user) {
            App::abort('default', 'Пользователь не найден!');
        }

        $total = Photo::where('user_id', $user->id)->count();

        $page = App::paginate(Setting::get('fotolist'), $total);

        $photos = Photo::where('user_id', $user->id)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        $moder = (App::getUserId() == $user->id) ? 1 : 0;

        App::view('gallery/user_albums', compact('photos', 'moder', 'page', 'user'));
    }

    /**
     * Альбом пользователя
     */
    public function top()
    {
        $sort = check(Request::input('sort', 'rating'));

        switch ($sort) {
            case 'rating':
                $order = 'rating';
                break;
            case 'comments':
                $order = 'comments';
                break;
            default:
                $order = 'rating';
        }

        $total = Photo::count();
        $page = App::paginate(Setting::get('fotolist'), $total);

        $photos = Photo::orderBy($order, 'desc')
            ->offset($page['offset'])
            ->limit(Setting::get('fotolist'))
            ->with('user')
            ->get();

        App::view('gallery/top', compact('photos', 'page', 'order'));
    }

    /**
     * Выводит все комментарии
     */
    public function allComments()
    {
        $total = Comment::where('relate_type', Photo::class)->count();
        $page = App::paginate(Setting::get('postgallery'), $total);

        $comments = Comment::select('comments.*', 'title')
            ->where('relate_type', Photo::class)
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        App::view('gallery/all_comments', compact('comments', 'page'));
    }

    /**
     * Выводит комментарии пользователя
     */
    public function UserComments($login)
    {
        $user = User::where('login', $login)->first();

        if (!$user) {
            App::abort('default', 'Пользователь не найден!');
        }

        $total = Comment::where('relate_type', Photo::class)
            ->where('user_id', $user->id)
            ->count();

        $page = App::paginate(Setting::get('postgallery'), $total);

        $comments = Comment::select('comments.*', 'title')
            ->where('relate_type', Photo::class)
            ->where('comments.user_id', $user->id)
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        App::view('gallery/user_comments', compact('comments', 'page', 'user'));
    }

    /**
     * Переход к сообщению
     */
    public function viewComment($gid, $id)
    {
        $total = Comment::where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->where('id', '<=', $id)
            ->orderBy('created_at')
            ->count();

        if ($total) {
            $end = ceil($total / Setting::get('postgallery'));

            App::redirect('/gallery/' . $gid . '/comments?page=' . $end);
        } else {
            App::setFlash('success', 'Комментариев к данному изображению не существует!');
            App::redirect("/gallery/comments");
        }
    }
}
