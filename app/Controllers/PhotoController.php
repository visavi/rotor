<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;

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
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user')
            ->get();

        return view('gallery/index', compact('photos', 'page'));
    }

    /**
     * Просмотр полной фотографии
     */
    public function view($id)
    {
        $photo = Photo::query()
            ->select('photo.*', 'pollings.vote')
            ->where('photo.id', $id)
            ->leftJoin('pollings', function (JoinClause $join) {
                $join->on('photo.id', '=', 'pollings.relate_id')
                    ->where('pollings.relate_type', Photo::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('user')
            ->first();

        if (! $photo) {
            abort(404, 'Фотография не найдена');
        }

        return view('gallery/view', compact('photo'));
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
            $photo  = Request::file('photo');
            $closed = empty(Request::input('closed')) ? 0 : 1;

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->length($title, 5, 50, ['title' => 'Слишком длинное или короткое название!'])
                ->length($text, 0, 1000, ['text' => 'Слишком длинное описание!'])
                ->true(Flood::isFlood(), ['text' => 'Антифлуд! Разрешается отправлять сообщения раз в ' . Flood::getPeriod() . ' секунд!']);

            $rules = [
                'maxsize'   => setting('filesize'),
                'maxweight' => setting('fileupfoto'),
                'minweight' => 100,
            ];
            $validator->image($photo, $rules, ['photo' => 'Не удалось загрузить фотографию!']);

            if ($validator->isValid()) {

                $link = uploadImage($photo, UPLOADS.'/pictures/');

                $photo = Photo::query()->create([
                    'user_id'    => getUser('id'),
                    'title'      => $title,
                    'text'       => antimat($text),
                    'link'       => $link,
                    'created_at' => SITETIME,
                    'closed'     => $closed,
                ]);

                setFlash('success', 'Фотография успешно загружена!');
                redirect('/gallery/' . $photo->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        return view('gallery/create');
    }

    /**
     * Редактирование фото
     */
    public function edit($id)
    {
        $page = int(Request::input('page', 1));

        if (! getUser()) {
            abort(403, 'Авторизуйтесь для редактирования фотографии!');
        }

        $photo = Photo::query()->where('user_id', getUser('id'))->find($id);

        if (! $photo) {
            abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
        }

        if (Request::isMethod('post')) {
            $token  = check(Request::input('token'));
            $title  = check(Request::input('title'));
            $text   = check(Request::input('text'));
            $closed = empty(Request::input('closed')) ? 0 : 1;

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
    public function comments($id)
    {
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort('default', 'Фотография не найдена!');
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

                $photo->increment('count_comments');

                setFlash('success', 'Комментарий успешно добавлен!');
                redirect('/gallery/end/' . $photo->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $id)
            ->count();
        $page = paginate(setting('postgallery'), $total);

        $comments = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $id)
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('created_at')
            ->with('user')
            ->get();

        return view('gallery/comments', compact('photo', 'comments', 'page'));
    }

    /**
     * Редактирование комментария
     */
    public function editComment($id, $cid)
    {
        $page = int(Request::input('page', 1));
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort('default', 'Фотография не найдена!');
        }

        if (! getUser()) {
            abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::query()
            ->select('comments.*', 'photo.closed')
            ->where('relate_type', Photo::class)
            ->where('comments.id', $cid)
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
                redirect('/gallery/comments/' . $photo->id . '?page=' . $page);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }
        return view('gallery/editcomment', compact('photo', 'comment', 'page'));
    }

    /**
     * Удаление фотографий
     */
    public function delete($id)
    {
        $page = int(Request::input('page', 1));

        $token = check(Request::input('token'));

        if (! getUser()) {
            abort(403, 'Для удаления фотографий небходимо авторизоваться!');
        }

        $photo = Photo::query()->where('user_id', getUser('id'))->find($id);

        if (! $photo) {
            abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
        }

        $validator = new Validator();
        $validator
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->true(is_writable(HOME . '/uploads/pictures'), ['Не установлены атрибуты доступа на дирекоторию с фотографиями!'])
            ->empty($photo->count_comments, 'Запрещено удалять фотографии к которым имеются комментарии!');

        if ($validator->isValid()) {
            deleteImage('uploads/pictures/', $photo->link);

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
    public function end($id)
    {
        $photo = Photo::query()->find($id);

        if (empty($photo)) {
            abort(404, 'Выбранное вами фото не найдено, возможно оно было удалено!');
        }

        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $id)
            ->count();

        $end = ceil($total / setting('postgallery'));
        redirect('/gallery/comments/' . $id . '?page=' . $end);
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
            ->selectRaw('count(*) as cnt, sum(count_comments) as count_comments')
            ->join('users', 'photo.user_id', '=', 'users.id')
            ->offset($page->offset)
            ->limit($page->limit)
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

        if (! $user) {
            abort('default', 'Пользователь не найден!');
        }

        $total = Photo::query()->where('user_id', $user->id)->count();

        $page = paginate(setting('fotolist'), $total);

        $photos = Photo::query()
            ->where('user_id', $user->id)
            ->offset($page->offset)
            ->limit($page->limit)
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
            case 'comments':
                $order = 'count_comments';
                break;
            default:
                $order = 'rating';
        }

        $total = Photo::query()->count();
        $page = paginate(setting('fotolist'), $total);

        $photos = Photo::query()
            ->orderBy($order, 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
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
            ->offset($page->offset)
            ->limit($page->limit)
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

        if (! $user) {
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
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        return view('gallery/user_comments', compact('comments', 'page', 'user'));
    }

    /**
     * Переход к сообщению
     */
    public function viewComment($id, $cid)
    {
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, 'Фотография не найдена!');
        }

        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $id)
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('postgallery'));
        redirect('/gallery/comments/' . $id . '?page=' . $end . '#comment_' . $cid);
    }
}
