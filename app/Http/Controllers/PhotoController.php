<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Comment;
use App\Models\File;
use App\Models\Flood;
use App\Models\Photo;
use App\Traits\CommentableTrait;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhotoController extends Controller
{
    use CommentableTrait;

    protected function commentableModel(): string
    {
        return Photo::class;
    }

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
    public function view(int $id, Request $request): View|RedirectResponse
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

        if ($redirect = $this->cidRedirect($photo, $request)) {
            return $redirect;
        }

        ['comments' => $comments, 'files' => $files] = $this->getCommentsData($photo);

        return view('photos/view', compact('photo', 'comments', 'files'));
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

            $validator
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

                clearCache(['statPhotos', 'recentPhotos']);
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
            ->orderBy('created_at')
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

            $validator
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

        $validator->empty($photo->count_comments, __('photos.photo_has_comments'));

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
            ->paginate(setting('comments_per_page'))
            ->appends(['user' => $user->login]);
        ;

        return view('photos/user_comments', compact('comments', 'user'));
    }
}
