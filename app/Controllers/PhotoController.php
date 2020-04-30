<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\File;
use App\Models\Flood;
use App\Models\Photo;
use Exception;
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
        $photos = Photo::query()
            ->orderByDesc('created_at')
            ->with('user', 'files')
            ->paginate(setting('fotolist'));

        return view('photos/index', compact('photos'));
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
            abort(404, __('photos.photo_not_exist'));
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
                ->length($text, 0, 1000, ['text' => __('validator.text_long')])
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
                    ->where('relate_type', Photo::$morphName)
                    ->where('relate_id', 0)
                    ->where('user_id', getUser('id'))
                    ->update(['relate_id' => $photo->id]);

                clearCache(['statPhotos', 'recentPhotos']);
                $flood->saveState();

                setFlash('success', __('photos.photo_success_uploaded'));
                redirect('/photos/' . $photo->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $files = File::query()
            ->where('relate_type', Photo::$morphName)
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
            abort(403, __('main.not_authorized'));
        }

        /** @var Photo $photo */
        $photo = Photo::query()->where('user_id', getUser('id'))->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_author'));
        }

        if ($request->isMethod('post')) {
            $token  = check($request->input('token'));
            $title  = check($request->input('title'));
            $text   = check($request->input('text'));
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 0, 1000, ['text' => __('validator.text_long')]);

            if ($validator->isValid()) {
                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                ]);

                setFlash('success', __('photos.photo_success_edited'));
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
            abort(404, __('photos.photo_not_exist'));
        }

        if ($request->isMethod('post')) {
            $msg   = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator
                ->true(getUser(), __('main.not_authorized'))
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->empty($photo->closed, ['msg' => __('main.closed_comments')]);

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

        $comments = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $id)
            ->orderBy('created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return view('photos/comments', compact('photo', 'comments'));
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
            abort(404, __('photos.photo_not_exist'));
        }

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $comment = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('id', $cid)
            ->where('user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort('default', __('main.comment_deleted'));
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
                ->empty($photo->closed, __('main.closed_comments'));

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
     * @throws Exception
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
            abort(404, __('photos.photo_not_author'));
        }

        $validator
            ->equal($token, $_SESSION['token'], __('validator.token'))
            ->empty($photo->count_comments, __('photos.photo_has_comments'));

        if ($validator->isValid()) {
            $photo->comments()->delete();
            $photo->delete();

            setFlash('success', __('photos.photo_success_deleted'));
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
            abort(404, __('photos.photo_not_exist'));
        }

        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $photo->id)
            ->count();

        $end = ceil($total / setting('comments_per_page'));
        redirect('/photos/comments/' . $photo->id . '?page=' . $end);
    }

    /**
     * Альбомы пользователей
     *
     * @return string
     */
    public function albums(): string
    {
        $albums = Photo::query()
            ->select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(count_comments) as count_comments')
            ->join('users', 'photos.user_id', 'users.id')
            ->groupBy('user_id')
            ->orderByDesc('cnt')
            ->paginate(setting('photogroup'));

        return view('photos/albums', compact('albums'));
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

        $photos = Photo::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('user')
            ->paginate(setting('fotolist'));

        $moder = (getUser('id') === $user->id) ? 1 : 0;

        return view('photos/user_albums', compact('photos', 'moder', 'user'));
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

        $photos = Photo::query()
            ->orderByDesc($order)
            ->with('user')
            ->paginate(setting('fotolist'))
            ->appends(['sort' => $sort]);

        return view('photos/top', compact('photos', 'order'));
    }

    /**
     * Выводит все комментарии
     *
     * @return string
     */
    public function allComments(): string
    {
        $comments = Comment::query()
            ->select('comments.*', 'title')
            ->where('relate_type', Photo::class)
            ->leftJoin('photos', 'comments.relate_id', 'photos.id')
            ->orderByDesc('comments.created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return view('photos/all_comments', compact('comments'));
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

        $comments = Comment::query()
            ->select('comments.*', 'title')
            ->where('relate_type', Photo::class)
            ->where('comments.user_id', $user->id)
            ->leftJoin('photos', 'comments.relate_id', 'photos.id')
            ->orderByDesc('comments.created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return view('photos/user_comments', compact('comments', 'user'));
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
            abort(404, __('photos.photo_not_exist'));
        }

        $total = Comment::query()
            ->where('relate_type', Photo::class)
            ->where('relate_id', $id)
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('comments_per_page'));
        redirect('/photos/comments/' . $photo->id . '?page=' . $end . '#comment_' . $cid);
    }
}
