<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class PhotoController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = Photo::query()->count();
        $page = paginate(setting('fotolist'), $total);

        $photos = Photo::query()
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('fotolist'))
            ->with('user')
            ->get();

        return view('gallery/index', compact('photos', 'page'));
    }

    /**
     * Просмотр полной фотографии
     */
    public function view($gid)
    {
        $photo = Photo::query()
            ->select('photo.*', 'pollings.vote')
            ->where('photo.id', $gid)
            ->leftJoin('pollings', function ($join) {
                $join->on('photo.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Photo::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('user')
            ->first();

        if (! $photo) {
            abort(404, 'Фотография не найдена');
        }

        return view('gallery/view', compact('photo', 'page'));
    }

    /**
     * Форма загрузки фото
     */
    public function create()
    {
        if (!getUser()) {
            abort(403, 'Для добавления фотографий небходимо авторизоваться!');
        }

        if (Request::isMethod('post')) {

            $token  = check(Request::input('token'));
            $title  = check(Request::input('title'));
            $text   = check(Request::input('text'));
            $closed = Request::has('closed') ? 1 : 0;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 0, 1000, ['text' => 'Слишком длинное описание!'])
                ->true(Flood::isFlood(), ['text' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!']);

            $handle = uploadImage(
                $_FILES['photo'],
                setting('filesize'),
                setting('fileupfoto'),
                uniqid()
            );

            if (! $handle) {
                $validator->addError(['photo' => 'Не удалось загрузить фотографию!']);
            }

            if ($validator->isValid()) {

                $handle->process(HOME . '/uploads/pictures/');
                if ($handle->processed) {

                    $photo = Photo::query()->create([
                        'user_id'    => getUser('id'),
                        'title'      => $title,
                        'text'       => antimat($text),
                        'link'       => $handle->file_dst_name,
                        'created_at' => SITETIME,
                        'closed'     => $closed,
                    ]);

                    setFlash('success', 'Фотография успешно загружена!');
                    redirect('/gallery/' . $photo->id);
                } else {
                    $validator->addError(['photo' => $handle->error]);
                }
            }

            setInput(Request::all());
            setFlash('danger', $validator->getErrors());
        }

        return view('gallery/create');
    }

    /**
     * Редактирование фото
     */
    public function edit($gid)
    {
        $page = int(Request::input('page', 1));

        if (! getUser()) {
            abort(403, 'Авторизуйтесь для редактирования фотографии!');
        }

        $photo = Photo::query()->where('user_id', getUser('id'))->find($gid);

        if (! $photo) {
            abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
        }

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $title  = check(Request::input('title'));
            $text   = check(Request::input('text'));
            $closed = Request::has('closed') ? 1 : 0;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 0, 1000, ['text' => 'Слишком длинное описание!']);

            if ($validator->isValid()) {
                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                ]);

                setFlash('success', 'Фотография успешно отредактирована!');
                redirect('/gallery/album/' . getUser('login') . '?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $checked = ($photo['closed'] == 1) ? ' checked' : '';

        return view('gallery/edit', compact('photo', 'checked', 'page'));
    }

    /**
     * Список комментариев
     */
    public function comments($gid)
    {
        $photo = Photo::query()->find($gid);

        if (! $photo) {
            abort('default', 'Фотография не найдена');
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validator = new Validator();
            $validator
                ->true(getUser(), 'Чтобы добавить комментарий необходимо авторизоваться')
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинное или короткое название!'])
                ->true(Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!'])
                ->empty($photo->closed, ['msg' => 'Комментирование данной фотографии запрещено!']);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                Comment::query()->create([
                    'relate_type' => Photo::class,
                    'relate_id'   => $photo->id,
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

                $photo->update([
                    'comments' => DB::raw('comments + 1'),
                ]);

                setFlash('success', 'Комментарий успешно добавлен!');
                redirect('/gallery/' . $photo->id . '/end');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->count();
        $page = paginate(setting('postgallery'), $total);

        $comments = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at')
            ->with('user')
            ->get();

        return view('gallery/comments', compact('photo', 'comments', 'page'));
    }

    /**
     * Редактирование комментария
     */
    public function editComment($gid, $id)
    {
        $page = int(Request::input('page', 1));

        if (!getUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::query()
            ->select('comments.*', 'photo.closed')
            ->where('relate_type', Photo::class)
            ->where('comments.id', $id)
            ->where('comments.user_id', getUser('id'))
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->first();

        if (! $comment) {
            abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment->closed) {
            abort('default', 'Редактирование невозможно, комментирование запрещено!');
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validator = new Validator();
            $validator
                ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($msg, 5, 1000, ['msg' => 'Слишком длинный или короткий комментарий!'])
                ->empty($comment->closed, 'Комментирование данной фотографии запрещено!');

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
                redirect('/gallery/' . $gid . '/comments?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }
        return view('gallery/editcomment', compact('comment', 'page'));
    }

    /**
     * Удаление фотографий
     */
    public function delete($gid)
    {
        $page = int(Request::input('page', 1));

        $token = check(Request::input('token'));

        if (!getUser()) {
            abort(403, 'Для удаления фотографий небходимо авторизоваться!');
        }

        $photo = Photo::query()->where('user_id', getUser('id'))->find($gid);

        if (!$photo) {
            abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
        }

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(is_writable(HOME . '/uploads/pictures'), ['Не установлены атрибуты доступа на дирекоторию с фотографиями!'])
            ->empty($photo['comments'], 'Запрещено удалять фотографии к которым имеются комментарии!');

        if ($validator->isValid()) {
            deleteImage('uploads/pictures/', $photo['link']);

            Comment::query()
                ->where('relate_type', Photo::class)
                ->where('relate_id', $photo->id)
                ->delete();

            $photo->delete();

            setFlash('success', 'Фотография успешно удалена!');

        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/gallery/album/' . getUser('login') . '?page=' . $page);
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($gid)
    {
        $photo = Photo::query()->find($gid);

        if (empty($photo)) {
            abort(404, 'Выбранное вами фото не найдено, возможно оно было удалено!');
        }

        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->count();

        $end = ceil($total / setting('postgallery'));
        redirect('/gallery/' . $gid . '/comments?page=' . $end);
    }

    /**
     * Альбомы пользователей
     */
    public function albums()
    {
        $total = Photo::query()
            ->distinct()
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->count('user_id');

        $page = paginate(setting('photogroup'), $total);

        $albums = Photo::query()
            ->select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(comments) as comments')
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->groupBy('user_id')
            ->orderBy('cnt', 'desc')
            ->get();

        return view('gallery/albums', compact('albums', 'page'));
    }

    /**
     * Альбом пользователя
     */
    public function album($login)
    {
        $user = User::query()->where('login', $login)->first();

        if (!$user) {
            abort('default', 'Пользователь не найден!');
        }

        $total = Photo::query()->where('user_id', $user->id)->count();

        $page = paginate(setting('fotolist'), $total);

        $photos = Photo::query()
            ->where('user_id', $user->id)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        $moder = (getUser('id') == $user->id) ? 1 : 0;

        return view('gallery/user_albums', compact('photos', 'moder', 'page', 'user'));
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

        $total = Photo::query()->count();
        $page = paginate(setting('fotolist'), $total);

        $photos = Photo::query()
            ->orderBy($order, 'desc')
            ->offset($page['offset'])
            ->limit(setting('fotolist'))
            ->with('user')
            ->get();

        return view('gallery/top', compact('photos', 'page', 'order'));
    }

    /**
     * Выводит все комментарии
     */
    public function allComments()
    {
        $total = Comment::query()->where('relate_type', Photo::class)->count();
        $page = paginate(setting('postgallery'), $total);

        $comments = Comment::query()
            ->select('comments.*', 'title')
            ->where('relate_type', Photo::class)
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        return view('gallery/all_comments', compact('comments', 'page'));
    }

    /**
     * Выводит комментарии пользователя
     */
    public function UserComments($login)
    {
        $user = User::query()->where('login', $login)->first();

        if (!$user) {
            abort('default', 'Пользователь не найден!');
        }

        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('user_id', $user->id)
            ->count();

        $page = paginate(setting('postgallery'), $total);

        $comments = Comment::query()
            ->select('comments.*', 'title')
            ->where('relate_type', Photo::class)
            ->where('comments.user_id', $user->id)
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        return view('gallery/user_comments', compact('comments', 'page', 'user'));
    }

    /**
     * Переход к сообщению
     */
    public function viewComment($gid, $id)
    {
        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->where('id', '<=', $id)
            ->orderBy('created_at')
            ->count();

        if ($total) {
            $end = ceil($total / setting('postgallery'));

            redirect('/gallery/' . $gid . '/comments?page=' . $end);
        } else {
            setFlash('success', 'Комментариев к данному изображению не существует!');
            redirect('/gallery/comments');
        }
    }
}
