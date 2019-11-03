<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\File;
use App\Models\Flood;
use App\Models\Photo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class PhotoController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        $total = Photo::query()->count();
        $page = paginate(setting('fotolist'), $total);

        $photos = Photo::query()
            ->orderBy('created_at', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->with('user', 'files')
            ->get();

        return view('photos/index', compact('photos', 'page'));
    }

    /**
     * Просмотр полной фотографии
     *
     * @param int $id
     * @return string
     */
    public function view(int $id): string
    {
        $photo = Photo::query()
            ->select('photos.*', 'pollings.vote')
            ->where('photos.id', $id)
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('photos.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Photo::class)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->with('user')
            ->first();

        if (! $photo) {
            abort(404, 'Фотография не найдена');
        }

        return view('photos/view', compact('photo'));
    }

    /**
     * Форма загрузки фото
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     * @return string
     */
    public function create(Request $request, Validator $validator, Flood $flood): string
    {
        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if ($request->isMethod('post')) {

            $token  = check($request->input('token'));
            $title  = check($request->input('title'));
            $text   = check($request->input('text'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 0, 1000, ['text' => 'Слишком длинное описание!'])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

            if ($validator->isValid()) {
                /** @var Photo $photo */
                $photo = Photo::query()->create([
                    'user_id'    => getUser('id'),
                    'title'      => $title,
                    'text'       => antimat($text),
                    'created_at' => SITETIME,
                    'closed'     => $closed,
                ]);

                File::query()
                    ->where('relate_type', Photo::class)
                    ->where('relate_id', 0)
                    ->where('user_id', getUser('id'))
                    ->update(['relate_id' => $photo->id]);

                clearCache(['statPhotos', 'recentPhotos']);
                $flood->saveState();

                setFlash('success', 'Фотография успешно загружена!');
                redirect('/photos/' . $photo->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $files = File::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', 0)
            ->where('user_id', getUser('id'))
            ->get();

        return view('photos/create', compact('files'));
    }

    /**
     * Редактирование фото
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        $page = int($request->input('page', 1));

        if (! getUser()) {
            abort(403, 'Авторизуйтесь для редактирования фотографии!');
        }

        /** @var Photo $photo */
        $photo = Photo::query()->where('user_id', getUser('id'))->find($id);

        if (! $photo) {
            abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
        }

        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $title  = check($request->input('title'));
            $text   = check($request->input('text'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 0, 1000, ['text' => 'Слишком длинное описание!']);

            if ($validator->isValid()) {
                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                ]);

                setFlash('success', 'Фотография успешно отредактирована!');
                redirect('/photos/albums/' . getUser('login') . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $checked = $photo->closed ? ' checked' : '';

        return view('photos/edit', compact('photo', 'checked', 'page'));
    }

    /**
     * Список комментариев
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     * @return string
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): string
    {
        /** @var Photo $photo */
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, 'Фотография не найдена!');
        }

        if ($request->isMethod('post')) {
            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator
                ->true(getUser(), __('main.not_authorized'))
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->empty($photo->closed, ['msg' => 'Комментирование данной фотографии запрещено!']);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                /** @var Comment $comment */
                $comment = Comment::query()->create([
                    'relate_type' => Photo::class,
                    'relate_id'   => $photo->id,
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

                $photo->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, '/photos/comment/' . $photo->id . '/' . $comment->id, $photo->title);

                setFlash('success', __('main.comment_added_success'));
                redirect('/photos/end/' . $photo->id);
            } else {
                setInput($request->all());
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

        return view('photos/comments', compact('photo', 'comments', 'page'));
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

        /** @var Photo $photo */
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, 'Фотография не найдена!');
        }

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $comment = Comment::query()
            ->select('comments.*', 'photos.closed')
            ->where('relate_type', Photo::class)
            ->where('comments.id', $cid)
            ->where('comments.user_id', getUser('id'))
            ->leftJoin('photos', 'comments.relate_id', 'photos.id')
            ->first();

        if (! $comment) {
            abort('default', __('main.comment_deleted'));
        }

        if ($comment->closed) {
            abort('default', 'Редактирование невозможно, комментирование запрещено!');
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->empty($comment->closed, 'Комментирование данной фотографии запрещено!');

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));
                redirect('/photos/comments/' . $photo->id . '?page=' . $page);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }
        return view('photos/editcomment', compact('photo', 'comment', 'page'));
    }

    /**
     * Удаление фотографий
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @throws \Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        $page = int($request->input('page', 1));

        $token = check($request->input('token'));

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Photo $photo */
        $photo = Photo::query()->where('user_id', getUser('id'))->find($id);

        if (! $photo) {
            abort(404, 'Выбранное вами фото не найдено или вы не автор этой фотографии!');
        }

        $validator
            ->equal($token, $_SESSION['token'], __('validator.token'))
            ->empty($photo->count_comments, 'Запрещено удалять фотографии к которым имеются комментарии!');

        if ($validator->isValid()) {
            $photo->comments()->delete();
            $photo->delete();

            setFlash('success', 'Фотография успешно удалена!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/photos/albums/' . getUser('login') . '?page=' . $page);
    }

    /**
     * Переадресация на последнюю страницу
     *
     * @param int $id
     */
    public function end(int $id): void
    {
        /** @var Photo $photo */
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, 'Выбранное вами фото не найдено, возможно оно было удалено!');
        }

        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $photo->id)
            ->count();

        $end = ceil($total / setting('postgallery'));
        redirect('/photos/comments/' . $photo->id . '?page=' . $end);
    }

    /**
     * Альбомы пользователей
     *
     * @return string
     */
    public function albums(): string
    {
        $total = Photo::query()
            ->distinct()
            ->join('users', 'photos.user_id', 'users.id')
            ->count('user_id');

        $page = paginate(setting('photogroup'), $total);

        $albums = Photo::query()
            ->select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(count_comments) as count_comments')
            ->join('users', 'photos.user_id', 'users.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->groupBy('user_id')
            ->orderBy('cnt', 'desc')
            ->get();

        return view('photos/albums', compact('albums', 'page'));
    }

    /**
     * Альбом пользователя
     *
     * @param string $login
     * @return string
     */
    public function album(string $login): string
    {
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
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

        $moder = (getUser('id') === $user->id) ? 1 : 0;

        return view('photos/user_albums', compact('photos', 'moder', 'page', 'user'));
    }

    /**
     * Альбом пользователя
     *
     * @param Request $request
     * @return string
     */
    public function top(Request $request): string
    {
        $sort = check($request->input('sort', 'rating'));

        if ($sort === 'comments') {
            $order = 'count_comments';
        } else {
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

        return view('photos/top', compact('photos', 'page', 'order'));
    }

    /**
     * Выводит все комментарии
     *
     * @return string
     */
    public function allComments(): string
    {
        $total = Comment::query()->where('relate_type', Photo::class)->count();
        $page = paginate(setting('postgallery'), $total);

        $comments = Comment::query()
            ->select('comments.*', 'title')
            ->where('relate_type', Photo::class)
            ->leftJoin('photos', 'comments.relate_id', 'photos.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        return view('photos/all_comments', compact('comments', 'page'));
    }

    /**
     * Выводит комментарии пользователя
     *
     * @param string $login
     * @return string
     */
    public function UserComments($login): string
    {
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
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
            ->leftJoin('photos', 'comments.relate_id', 'photos.id')
            ->offset($page->offset)
            ->limit($page->limit)
            ->orderBy('comments.created_at', 'desc')
            ->with('user')
            ->get();

        return view('photos/user_comments', compact('comments', 'page', 'user'));
    }

    /**
     * Переход к сообщению
     *
     * @param int $id
     * @param int $cid
     */
    public function viewComment(int $id, int $cid): void
    {
        /** @var Photo $photo */
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
        redirect('/photos/comments/' . $photo->id . '?page=' . $end . '#comment_' . $cid);
    }
}
