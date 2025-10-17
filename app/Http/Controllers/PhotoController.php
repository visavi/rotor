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
     */
    public function index(Request $request): View
    {
        $sort = $request->input('sort', 'date');
        $order = $request->input('order', 'desc');

        [$sorting, $orderBy] = Photo::getSorting($sort, $order);

        $photos = Photo::query()
            ->select('photos.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('photos.id', 'polls.relate_id')
                    ->where('polls.relate_type', Photo::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->orderBy(...$orderBy)
            ->with('user', 'files')
            ->paginate(setting('fotolist'))
            ->appends(compact('sort', 'order'));

        return view('photos/index', compact('photos', 'sorting'));
    }

    /**
     * Просмотр полной фотографии
     */
    public function view(int $id): View
    {
        $photo = Photo::query()
            ->select('photos.*', 'polls.vote')
            ->where('photos.id', $id)
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('photos.id', 'polls.relate_id')
                    ->where('polls.relate_type', Photo::$morphName)
                    ->where('polls.user_id', getUser('id'));
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
     */
    public function create(Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        if (! isAdmin() && ! setting('photos_create')) {
            abort(200, __('photos.photos_closed'));
        }

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, setting('photo_title_min'), setting('photo_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('photo_text_min'), setting('photo_text_max'), ['text' => __('validator.text_long')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])]);

            $existFiles = File::query()
                ->where('relate_type', Photo::$morphName)
                ->where('relate_id', 0)
                ->where('user_id', $user->id)
                ->exists();
            $validator->true($existFiles, ['files' => __('validator.image_upload_failed')]);

            if ($validator->isValid()) {
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

                return redirect()->route('photos.view', ['id' => $photo->id]);
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
     */
    public function edit(int $id, Request $request, Validator $validator): View|RedirectResponse
    {
        $page = int($request->input('page', 1));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $photo = Photo::query()->where('user_id', $user->id)->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_author'));
        }

        if ($request->isMethod('post')) {
            $title = $request->input('title');
            $text = $request->input('text');
            $closed = empty($request->input('closed')) ? 0 : 1;

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($title, setting('photo_title_min'), setting('photo_title_max'), ['title' => __('validator.text')])
                ->length($text, setting('photo_text_min'), setting('photo_text_max'), ['text' => __('validator.text_long')]);

            if ($validator->isValid()) {
                $text = antimat($text);

                $photo->update([
                    'title'  => $title,
                    'text'   => $text,
                    'closed' => $closed,
                ]);

                setFlash('success', __('photos.photo_success_edited'));

                return redirect()->route('photos.user-albums', ['user' => $user->login, 'page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $checked = $photo->closed ? ' checked' : '';

        return view('photos/edit', compact('photo', 'checked', 'page'));
    }

    /**
     * Список комментариев
     */
    public function comments(int $id, Request $request, Validator $validator, Flood $flood): View|RedirectResponse
    {
        $photo = Photo::query()->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_exist'));
        }

        $cid = int($request->input('cid'));
        if ($cid) {
            $total = $photo->comments->where('id', '<=', $cid)->count();

            $page = ceil($total / setting('comments_per_page'));
            $page = $page > 1 ? $page : null;

            return redirect()->route('photos.comments', ['id' => $photo->id, 'page' => $page])
                ->withFragment('comment_' . $cid);
        }

        if ($request->isMethod('post')) {
            $user = getUser();
            $msg = $request->input('msg');

            $validator
                ->true($user, __('main.not_authorized'))
                ->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->length($msg, setting('comment_text_min'), setting('comment_text_max'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->empty($photo->closed, ['msg' => __('main.closed_comments')]);

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment = $photo->comments()->create([
                    'text'       => $msg,
                    'user_id'    => $user->id,
                    'created_at' => SITETIME,
                    'ip'         => getIp(),
                    'brow'       => getBrowser(),
                ]);

                $user->increment('allcomments');
                $user->increment('point', setting('comment_point'));
                $user->increment('money', setting('comment_money'));

                $photo->increment('count_comments');

                $flood->saveState();
                sendNotify($msg, route('photos.comments', ['id' => $photo->id, 'cid' => $comment->id], false), $photo->title);

                setFlash('success', __('main.comment_added_success'));

                return redirect()->route('photos.comments', [
                    'id'   => $photo->id,
                    'page' => ceil($photo->comments->count() / setting('comments_per_page')),
                ]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $comments = $photo->comments()
            ->select('comments.*', 'polls.vote')
            ->leftJoin('polls', static function (JoinClause $join) {
                $join->on('comments.id', 'polls.relate_id')
                    ->where('polls.relate_type', Comment::$morphName)
                    ->where('polls.user_id', getUser('id'));
            })
            ->orderBy('created_at')
            ->with('user')
            ->paginate(setting('comments_per_page'));

        return view('photos/comments', compact('photo', 'comments'));
    }

    /**
     * Редактирование комментария
     */
    public function editComment(int $id, int $cid, Request $request, Validator $validator): View|RedirectResponse
    {
        $page = int($request->input('page', 1));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

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
                ->length($msg, setting('comment_text_min'), setting('comment_text_max'), ['msg' => __('validator.text')])
                ->empty($photo->closed, __('main.closed_comments'));

            if ($validator->isValid()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                setFlash('success', __('main.comment_edited_success'));

                return redirect()->route('photos.comments', ['id' => $photo->id, 'page' => $page]);
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return view('photos/editcomment', compact('photo', 'comment', 'page'));
    }

    /**
     * Удаление фотографий
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $page = int($request->input('page', 1));

        if (! $user = getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $photo = Photo::query()->where('user_id', $user->id)->find($id);

        if (! $photo) {
            abort(404, __('photos.photo_not_author'));
        }

        $validator
            ->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->empty($photo->count_comments, __('photos.photo_has_comments'));

        if ($validator->isValid()) {
            $photo->delete();

            setFlash('success', __('photos.photo_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect()->route('photos.user-albums', ['user' => $user->login, 'page' => $page]);
    }

    /**
     * Альбомы пользователей
     */
    public function albums(): View
    {
        $albums = Photo::query()
            ->select('user_id', 'login')
            ->selectRaw('count(*) as cnt, sum(count_comments) as count_comments')
            ->join('users', 'photos.user_id', 'users.id')
            ->groupBy('user_id')
            ->orderByDesc('cnt')
            ->with('user')
            ->paginate(setting('photogroup'));

        return view('photos/albums', compact('albums'));
    }

    /**
     * Альбом пользователя
     */
    public function album(Request $request): View
    {
        $login = $request->input('user', getUser('login'));
        $user = getUserByLogin($login);

        if (! $user) {
            abort(404, __('validator.user'));
        }

        $photos = Photo::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->with('user', 'files')
            ->paginate(setting('fotolist'))
            ->appends(['user' => $user->login]);

        $moder = getUser() && getUser('id') === $user->id ? 1 : 0;

        return view('photos/user_albums', compact('photos', 'moder', 'user'));
    }

    /**
     * Выводит все комментарии
     */
    public function allComments(): View
    {
        $comments = Comment::query()
            ->select('comments.*', 'title')
            ->where('relate_type', Photo::$morphName)
            ->leftJoin('photos', 'comments.relate_id', 'photos.id')
            ->orderByDesc('comments.created_at')
            ->with('user', 'relate')
            ->paginate(setting('comments_per_page'));

        return view('photos/all_comments', compact('comments'));
    }

    /**
     * Выводит комментарии пользователя
     */
    public function userComments(Request $request): View
    {
        $login = $request->input('user', getUser('login'));
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
            ->with('user', 'relate')
            ->paginate(setting('comments_per_page'));

        return view('photos/user_comments', compact('comments', 'user'));
    }
}
