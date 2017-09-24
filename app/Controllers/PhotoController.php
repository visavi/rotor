<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
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
        $total = Photo::count();
        $page = paginate(setting('fotolist'), $total);

        $photos = Photo::orderBy('created_at', 'desc')
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
        $photo = Photo::select('photo.*', 'pollings.vote')
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

            $validation = new Validation();
            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $title, ['title' => 'Слишком длинное или короткое название!'], true, 5, 50)
                ->addRule('string', $text, ['text' => 'Слишком длинное описание!'], true, 0, 1000)
                ->addRule('bool', Flood::isFlood(), ['text' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!']);

            $handle = uploadImage(
                $_FILES['photo'],
                setting('filesize'),
                setting('fileupfoto'),
                uniqid()
            );

            if (!$handle) {
                $validation->addError(['photo' => 'Не удалось загрузить фотографию!']);
            }

            if ($validation->run()) {

                $handle->process(HOME . '/uploads/pictures/');
                if ($handle->processed) {
                    $photo = new Photo();
                    $photo->user_id = getUser('id');
                    $photo->title = $title;
                    $photo->text = antimat($text);
                    $photo->link = $handle->file_dst_name;
                    $photo->created_at = SITETIME;
                    $photo->closed = $closed;
                    $photo->save();

                    setFlash('success', 'Фотография успешно загружена!');
                    redirect('/gallery/' . $photo->id);
                } else {
                    $validation->addError(['photo' => $handle->error]);
                }
            }

            setInput(Request::all());
            setFlash('danger', $validation->getErrors());
        }

        return view('gallery/create');
    }

    /**
     * Редактирование фото
     */
    public function edit($gid)
    {
        $page = abs(intval(Request::input('page', 1)));

        if (!getUser()) {
            abort(403, 'Авторизуйтесь для редактирования фотографии!');
        }

        $photo = Photo::where('user_id', getUser('id'))->find($gid);

        if (!$photo) {
            abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
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

                setFlash('success', 'Фотография успешно отредактирована!');
                redirect('/gallery/album/' . getUser('login') . '?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }

        $checked = ($photo['closed'] == 1) ? ' checked="checked"' : '';

        return view('gallery/edit', compact('photo', 'checked', 'page'));
    }

    /**
     * Список комментариев
     */
    public function comments($gid)
    {
        $photo = Photo::find($gid);

        if (! $photo) {
            abort('default', 'Фотография не найдена');
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validation = new Validation();
            $validation
                ->addRule('bool', getUser(), 'Чтобы добавить комментарий необходимо авторизоваться')
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $msg, ['msg' => 'Слишком длинное или короткое название!'], true, 5, 1000)
                ->addRule('bool', Flood::isFlood(), ['msg' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!'])
                ->addRule('empty', $photo['closed'], 'Комментирование данной фотографии запрещено!');

            if ($validation->run()) {
                $msg = antimat($msg);

                Comment::create([
                    'relate_type' => Photo::class,
                    'relate_id'   => $photo->id,
                    'text'        => $msg,
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getClientIp(),
                    'brow'        => getUserAgent(),
                ]);

                $user = User::where('id', getUser('id'));
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
                setFlash('danger', $validation->getErrors());
            }
        }

        $total = Comment::where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->count();
        $page = paginate(setting('postgallery'), $total);

        $comments = Comment::where('relate_type', Photo::class)
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
        $page = abs(intval(Request::input('page', 1)));

        if (!getUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::select('comments.*', 'photo.closed')
            ->where('relate_type', Photo::class)
            ->where('comments.id', $id)
            ->where('comments.user_id', getUser('id'))
            ->leftJoin('photo', 'comments.relate_id', '=', 'photo.id')
            ->first();

        if (! $comment) {
            abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment['closed']) {
            abort('default', 'Редактирование невозможно, комментирование запрещено!');
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
                ->addRule('string', $msg, ['msg' => 'Слишком длинный или короткий комментарий!'], true, 5, 1000)
                ->addRule('empty', $comment['closed'], 'Комментирование данной фотографии запрещено!');

            if ($validation->run()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', 'Комментарий успешно отредактирован!');
                redirect('/gallery/' . $gid . '/comments?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validation->getErrors());
            }
        }
        return view('gallery/editcomment', compact('comment', 'page'));
    }

    /**
     * Удаление фотографий
     */
    public function delete($gid)
    {
        $page = abs(intval(Request::input('page', 1)));

        $token = check(Request::input('token'));

        if (!getUser()) {
            abort(403, 'Для удаления фотографий небходимо авторизоваться!');
        }

        $photo = Photo::where('user_id', getUser('id'))->find($gid);

        if (!$photo) {
            abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
        }

        $validation = new Validation();
        $validation
            ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('bool', is_writeable(HOME . '/uploads/pictures'), ['Не установлены атрибуты доступа на дирекоторию с фотографиями!'])
            ->addRule('empty', $photo['comments'], 'Запрещено удалять фотографии к которым имеются комментарии!');

        if ($validation->run()) {
            deleteImage('uploads/pictures/', $photo['link']);

            Comment::where('relate_type', Photo::class)
                ->where('relate_id', $photo->id)
                ->delete();

            $photo->delete();

            setFlash('success', 'Фотография успешно удалена!');

        } else {
            setFlash('danger', $validation->getErrors());
        }

        redirect('/gallery/album/' . getUser('login') . '?page=' . $page);
    }


    /**
     * Переадресация на последнюю страницу
     */
    public function end($gid)
    {
        $photo = Photo::find($gid);

        if (empty($photo)) {
            abort(404, 'Выбранное вами фото не найдено, возможно оно было удалено!');
        }

        $total = Comment::where('relate_type', Photo::class)
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
        $total = Photo::distinct('user_id')
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->count('user_id');

        $page = paginate(setting('photogroup'), $total);

        $albums = Photo::select('user_id', 'login')
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
        $user = User::where('login', $login)->first();

        if (!$user) {
            abort('default', 'Пользователь не найден!');
        }

        $total = Photo::where('user_id', $user->id)->count();

        $page = paginate(setting('fotolist'), $total);

        $photos = Photo::where('user_id', $user->id)
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

        $total = Photo::count();
        $page = paginate(setting('fotolist'), $total);

        $photos = Photo::orderBy($order, 'desc')
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
        $total = Comment::where('relate_type', Photo::class)->count();
        $page = paginate(setting('postgallery'), $total);

        $comments = Comment::select('comments.*', 'title')
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
        $user = User::where('login', $login)->first();

        if (!$user) {
            abort('default', 'Пользователь не найден!');
        }

        $total = Comment::where('relate_type', Photo::class)
            ->where('user_id', $user->id)
            ->count();

        $page = paginate(setting('postgallery'), $total);

        $comments = Comment::select('comments.*', 'title')
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
        $total = Comment::where('relate_type', Photo::class)
            ->where('relate_id', $gid)
            ->where('id', '<=', $id)
            ->orderBy('created_at')
            ->count();

        if ($total) {
            $end = ceil($total / setting('postgallery'));

            redirect('/gallery/' . $gid . '/comments?page=' . $end);
        } else {
            setFlash('success', 'Комментариев к данному изображению не существует!');
            redirect("/gallery/comments");
        }
    }
}
