<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\File;
use App\Models\Flood;
use App\Models\Photo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhotoController extends Controller
{
    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
    {
        $photos = Photo::query()
            ->select('photos.*', 'pollings.vote')
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('photos.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Photo::$morphName)
                    ->where('pollings.user_id', getUser('id'));
            })
            ->orderByDesc('created_at')
            ->with('user', 'files')
            ->paginate(setting('fotolist'));

        return view('photos/index', compact('photos'));
    }

    /**
     * Просмотр полной фотографии
     *
     * @param int $id
     *
     * @return View
     */
    public function view(int $id): View
    {
        $photo = Photo::query()
            ->select('photos.*', 'pollings.vote')
            ->where('photos.id', $id)
            ->leftJoin('pollings', static function (JoinClause $join) {
                $join->on('photos.id', 'pollings.relate_id')
                    ->where('pollings.relate_type', Photo::$morphName)
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
     *
     * @return View|RedirectResponse
     */
    public function create(Request $request, Validator $validator, Flood $flood)
    {
        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if ($request->isMethod('post')) {
            $title  = $request->input('title');
            $text   = $request->input('text');
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 0, 1000, ['text' => __('validator.text_long')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

            $existFiles = File::query()
                ->where('relate_type', Photo::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $user->id)
                ->exists();
            $validator->true($existFiles, ['files' => __('validator.image_upload_failed')]);

            if ($validator->isValid()) {
                /** @var Photo $photo */
                $photo = Photo::query()->create([
                    'user_id'    => $user->id,
                    'title'      => $title,
                    'text'       => antimat($text),
                    'created_at' => SITETIME,
                    'closed'     => $closed,
                ]);

                File::query()
                    ->where('relate_type', Photo::$morphName)
                    ->where('relate_id', 0)
                    ->where('user_id', $user->id)
                    ->update(['relate_id' => $photo->id]);

                clearCache(['statPhotos', 'recentPhotos', 'PhotoFeed']);
                $flood->saveState();

                setFlash('success', __('photos.photo_success_uploaded'));

                return redirect('photos/' . $photo->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $files = File::query()
            ->where('relate_type', Photo::$morphName)
            ->where('relate_id', 0)
            ->where('user_id', $user->id)
            ->get();

        return view('photos/create', compact('files'));
    }

    /**
     * Редактирование фото
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function edit(int $id, Request $request, Validator $validator)
    {
        $page = int($request->input('page', 1));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Photo $photo */
        $photo = Photo::query()->where('user_id', $user->id)->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_author'));
        }

        if ($request->isMethod('post')) {
            $title  = $request->input('title');
            $text   = $request->input('text');
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, 3, 50, ['title' => __('validator.text')])
                ->length($text, 0, 1000, ['text' => __('validator.text_long')]);

            if ($validator->isValid()) {
                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                ]);

                setFlash('success', __('photos.photo_success_edited'));
                return redirect('photos/albums/' . $user->login . '?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
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
     *
     * @return View|RedirectResponse
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood)
    {
        /** @var Photo $photo */
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_exist'));
        }

        if ($request->isMethod('post')) {
            $user = getUser();
            $msg  = $request->input('msg');

            $validator
                ->true($user, __('main.not_authorized'))
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->empty($photo->closed, ['msg' => __('main.closed_comments')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                /** @var Comment $comment */
                $comment = $photo->comments()->create([
                    'text'        => $msg,
                    'user_id'     => $user->id,
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $user->increment('allcomments');
                $user->increment('point');
                $user->increment('money', 5);

                $photo->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, '/photos/comment/' . $photo->id . '/' . $comment->id, $photo->title);

                setFlash('success', __('main.comment_added_success'));

                return redirect('photos/end/' . $photo->id);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $comments = $photo->comments()
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
     *
     * @return View|RedirectResponse
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator)
    {
        $page = int($request->input('page', 1));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Photo $photo */
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_exist'));
        }

        $comment = $photo->comments()
            ->where('id', $cid)
            ->where('user_id', $user->id)
            ->first();

        if (! $comment) {
            abort(200, __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort(200, __('main.editing_impossible'));
        }

        if ($request->isMethod('post')) {
            $msg = $request->input('msg');

            $validator
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->empty($photo->closed, __('main.closed_comments'));

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));

                return redirect('photos/comments/' . $photo->id . '?page=' . $page);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }
        return view('photos/editcomment', compact('photo', 'comment', 'page'));
    }

    /**
     * Удаление фотографий
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Photo $photo */
        $photo = Photo::query()->where('user_id', $user->id)->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_author'));
        }

        $validator
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->empty($photo->count_comments, __('photos.photo_has_comments'));

        if ($validator->isValid()) {
            $photo->comments()->delete();
            $photo->delete();

            setFlash('success', __('photos.photo_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('photos/albums/' . $user->login . '?page=' . $page);
    }

    /**
     * Переадресация на последнюю страницу
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function end(int $id): RedirectResponse
    {
        /** @var Photo $photo */
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_exist'));
        }

        $total = $photo->comments()->count();

        $end = ceil($total / setting('comments_per_page'));
        return redirect('photos/comments/' . $photo->id . '?page=' . $end);
    }

    /**
     * Альбомы пользователей
     *
     * @return View
     */
    public function albums(): View
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
     *
     * @return View
     */
    public function album(string $login): View
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

        $moder = getUser() && getUser('id') === $user->id ? 1 : 0;

        return view('photos/user_albums', compact('photos', 'moder', 'user'));
    }

    /**
     * Альбом пользователя
     *
     * @param Request $request
     *
     * @return View
     */
    public function top(Request $request): View
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
     * @return View
     */
    public function allComments(): View
    {
        $comments = Comment::query()
            ->select('comments.*', 'title')
            ->where('relate_type', Photo::$morphName)
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
     *
     * @return View
     */
    public function UserComments(string $login): View
    {
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $comments = Comment::query()
            ->select('comments.*', 'title')
            ->where('relate_type', Photo::$morphName)
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
     *
     * @return RedirectResponse
     */
    public function viewComment(int $id, int $cid): RedirectResponse
    {
        /** @var Photo $photo */
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_exist'));
        }

        $total = $photo->comments()
            ->where('id', '<=', $cid)
            ->orderBy('created_at')
            ->count();

        $end = ceil($total / setting('comments_per_page'));

        return redirect('photos/comments/' . $photo->id . '?page=' . $end . '#comment_' . $cid);
    }
}
